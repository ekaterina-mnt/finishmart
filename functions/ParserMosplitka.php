<?php

namespace functions;

require __DIR__ . "/../vendor/autoload.php";

use DiDom\Document;
use DOMDocument;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;

class ParserMosplitka
{
    static function check_if_complect(Document $document): bool
    {
        $goods = $document->find('.single-product___main-info--equip-body.e__flex.e__fdc .single-product___main-info--equip-item.e__flex');
        if (empty($goods)) return false;
        return boolval(count($goods) > 1);
    }

    static function check_if_archive(Document $document): bool
    {
        $data = $document->find('.product-arhive-label');
        if (empty($goods)) return false;
        return boolval(count($data));
    }

    static function check_if_ampir_articul_exists(string $articul, string $provider, string $url_parser): bool
    {
        $good = MySQL::sql("SELECT id FROM " . $provider . "_products WHERE articul = '{$articul}' and link NOT LIKE '{$url_parser}'");
        return boolval($good->num_rows);
    }

    static function getArticulIfExists(string $url_parser, string $provider): string {
        $articul = MySQL::sql("SELECT articul FROM " . $provider . "_products WHERE link NOT LIKE '{$url_parser}'");
        return $articul->fetch_assoc($articul)['articul'];
    }

    static function getComplectData(Document $document, string $url_parser): array
    {
        $all_product_data = [];

        $all_product_data['link'] = [$url_parser, 's'];

        //название товара
        $title = $document->find('.single-product___page-header__h1, .tile__title');
        $title = ($title) ? trim($title[0]->text()) : null;
        $all_product_data['title'] = [$title, 's'];

        //цена
        $price_res = $document->find('.single-product___main-info--price span, .tile-shop__price');
        preg_match("#([0-9 ]+)([^0-9]+)#", $price_res[0]->text(), $carm);
        $price = ($carm) ? (int) str_replace(' ', '', trim($carm[1])) : null;
        $all_product_data['price'] = [$price, 'i'];

        //категория
        $categories = ParserMosplitka::getCategorySubcategory($document, $title);
        $category = $categories['category'] ?? null;
        $all_product_data['category'] = [$category, 's'];

        //подкатегория
        $subcategory = $categories['subcategory'] ?? null;
        $all_product_data['subcategory'] = [$subcategory, 's'];

        //единица измерения
        $edizm = Parser::getEdizm($category);
        $all_product_data['edizm'] = [$edizm, 's'];

        //остатки товара
        $stock = $document->find('.single-product___main-info--tag-item.is-green.e__flex.e__aic.e__jcc, .tile-shop-plashki-item.tile-shop-plashki-item__green'); //остатки на складе
        $stock = ($stock) ? str_replace('М', 'м', str_replace(" • ", ", ", trim($stock[0]->text()))) : 'Нет данных';

        $all_product_data['stock'] = [$stock, 's'];


        //картинки
        $images = ParserMosplitka::getImages($document) ?? null;
        $all_product_data['images'] = [$images, 's'];

        $characteristics_res = $document->find('.single-product___atts-att.e__flex.e__aic, .tile-prop-tabs__item');

        if ($characteristics_res) {
            $characteristics = array();

            foreach ($characteristics_res as $charact) {
                $name = trim($charact->find('.q_prop__name, .tile-prop-tabs__name')[0]->text());
                $value = trim($charact->find('.q_prop__value, .tile-prop-tabs__value')[0]->text());

                $characteristics[$name] = $characteristics[$name] ?? $value;

                //артикул
                if ($name == 'Артикул') {
                    $articul = $articul ?? $value;
                    $all_product_data['articul'] = [$articul, 's'];
                }

                //производитель
                if ($name == 'Производитель') {
                    $producer = $producer ?? $value;
                    $all_product_data['producer'] = [$producer, 's'];
                }

                //коллекция
                if ($name == 'Коллекция') {
                    $collection = $collection ?? $value;
                    $all_product_data['collection'] = [$collection, 's'];
                }

                //материал
                if ($name == 'Материал') {
                    $material = $material ?? $value;
                    $all_product_data['material'] = [$material, 's'];
                }

                //страна
                if ($name == 'Страна') {
                    $country = $country ?? $value;
                    $all_product_data['country'] = [$country, 's'];
                }

                //цвет
                if ($name == 'Цвет') {
                    $color = $color ?? $value;
                    $all_product_data['color'] = [$color, 's'];
                }

                //дизайн
                if ($name == 'Стиль') {
                    $design = $design ?? $value;
                    $all_product_data['design'] = [$design, 's'];
                }

                //монтаж
                if ($name == 'Тип установки' or $name == 'Монтаж') {
                    $montage = $montage ?? $value;
                    $all_product_data['montage'] = [$montage, 's'];
                }

                //тип
                if ($name == 'Тип' and str_contains($value, 'унитаз')) {
                    $type = 'Унитаз';
                    $all_product_data['type'] = [$type, 's'];
                }
            }
            $characteristics = json_encode($characteristics, JSON_UNESCAPED_UNICODE);
            $characteristics = $characteristics ?? null;
            $all_product_data['characteristics'] = [$characteristics, 's'];
        }

        return $all_product_data;
    }

    static function getLinkType(string $link): string|null
    {
        if (preg_match("#https://mosplitka.ru/catalog.+#", $link) and !preg_match("#.php$#", $link)) {
            $type = 'catalog';
        } elseif (preg_match("#https://mosplitka.ru/product.+#", $link)) {
            $type = 'product';
        } elseif (preg_match("#https://www.ampir.ru/catalog/.+/page(\d+).*#", $link)) {
            $type = 'catalog';
        } elseif (preg_match("#https://www.ampir.ru/catalog/.+/\d+/#", $link)) {
            $type = 'product';
        }

        $type = $type ?? null;

        return $type;
    }

    static function getCategoryAmpir($url_parser): string|null
    {
        $all_categories = Parser::getCategoriesList();

        if (preg_match("#https://www.ampir.ru/catalog/oboi/.*#", $url_parser)) {
            $category = $all_categories[0];
        } elseif (preg_match("#https://www.ampir.ru/catalog/lepnina/.*#", $url_parser)) {
            $category = $all_categories[5];
        } elseif (preg_match("#https://www.ampir.ru/catalog/kraski/.*#", $url_parser)) {
            $category = $all_categories[4];
        } elseif (preg_match("#https://www.ampir.ru/catalog/shtukaturka/.*#", $url_parser)) {
            $category = $all_categories[4];
        } elseif (preg_match("#https://www.ampir.ru/catalog/rozetki/.*#", $url_parser)) {
            $category = $all_categories[5];
        }

        return $category ?? null;
    }

    static function getSubcategoryAmpir(string $url_parser, string $title = null, string $product_usages = null): string|null
    {
        $all_subcategories = Parser::getSubcategoriesList();

        if (preg_match("#https://www.ampir.ru/catalog/shtukaturka/.*#", $url_parser)) {
            $subcategory = $all_subcategories[19];
        } elseif (preg_match("#https://www.ampir.ru/catalog/rozetki/.*#", $url_parser)) {
            $subcategory = $all_subcategories[20];
        } elseif (preg_match("#https://www.ampir.ru/catalog/kraski/.*#", $url_parser)) {
            $subcategory = null;
        }elseif (preg_match("#https://www.ampir.ru/catalog/oboi/.*#", $url_parser) and str_contains(mb_strtolower($title), 'обои под покраску')) {
            $subcategory = $all_subcategories[18];
        } elseif (preg_match("#https://www.ampir.ru/catalog/oboi/.*#", $url_parser) and str_contains(mb_strtolower($title), 'фотообои')) {
            $subcategory = $all_subcategories[17];
        } elseif (preg_match("#https://www.ampir.ru/catalog/oboi/.*#", $url_parser)) {
            $subcategory = $all_subcategories[9];
        } elseif (preg_match("#https://www.ampir.ru/catalog/lepnina/.*#", $url_parser) and str_contains(mb_strtolower($product_usages), 'карниз')) {
            $subcategory = $all_subcategories[21];
        } elseif (preg_match("#https://www.ampir.ru/catalog/lepnina/.*#", $url_parser) and str_contains(mb_strtolower($product_usages), 'дверное обрамление')) {
            $subcategory = $all_subcategories[24];
        } elseif (preg_match("#https://www.ampir.ru/catalog/lepnina/.*#", $url_parser) and str_contains(mb_strtolower($product_usages), 'молдинг')) {
            $subcategory = $all_subcategories[22];
        } elseif (preg_match("#https://www.ampir.ru/catalog/lepnina/.*#", $url_parser) and str_contains(mb_strtolower($product_usages), 'плинтус')) {
            $subcategory = $all_subcategories[23];
        } elseif (preg_match("#https://www.ampir.ru/catalog/lepnina/.*#", $url_parser) and str_contains(mb_strtolower($product_usages), 'потолочный декор')) {
            $subcategory = $all_subcategories[25];
        } elseif (preg_match("#https://www.ampir.ru/catalog/lepnina/.*#", $url_parser)) {
            $subcategory = $all_subcategories[26];
        }

        return $subcategory ?? null;
    }

    static function getCategorySubcategory(Document $document, string $title, string $producer = null, string $collection = null): array|null
    {
        $path_res = $document->find('.product-breadcrumb a, .breadcrumb_cont a');
        $path = "";
        if (!$path_res) return null;

        foreach ($path_res as $a) {
            $path .= $a->text() . "/";
        }
        $path = substr($path, 0, strlen($path) - 1);

        //категории
        $categories = array();
        foreach ($path_res as $a) {
            $a = $a->text();
            if (!isset($producer) && isset($collection)) {
                if ($a != 'На главную' && $a != 'Каталог' && !str_contains($a, $collection)) {
                    $categories[] = $a;
                }
            } elseif (isset($producer) && !isset($collection)) {
                if ($a != 'На главную' && $a != 'Каталог' && !str_contains($a, $producer)) {
                    $categories[] = $a;
                }
            } elseif (!isset($producer) && !isset($collection)) {
                if ($a != 'На главную' && $a != 'Каталог') {
                    $categories[] = $a;
                }
            } else {
                if ($a != 'На главную' && $a != 'Каталог' && !str_contains($a, $producer) && !str_contains($a, $collection)) {
                    $categories[] = $a;
                }
            }
        }


        $category = self::validateCategory($categories[0]);
        $subcategory = isset($categories[1]) ? self::validateSubcategory($category, $categories[1]) : self::validateSubcategory($category, null, $title);

        return ['category' => $category, 'subcategory' => $subcategory];
    }

    static function validateCategory(string $category): string
    {
        $categories = Parser::getCategoriesList();

        $categories_keys = [
            0 => null,
            1 => null,
            2 => boolval($category == 'Керамическая плитка'),
            3 => boolval($category == 'Сантехника'),
            4 => null,
            5 => null,
        ];

        foreach ($categories_keys as $key => $value) {
            if ($value) {
                $category = $categories[$key];
                break;
            }
        }

        return $category;
    }

    static function validateSubcategory(string $category, string|null $subcategory, string $title = null): string|null
    {
        $subcategories = Parser::getSubcategoriesList();

        $subcategories_keys = [
            0 => 'Раковины',
            1 => ['Унитазы', 'Инсталляции', 'Писсуары', 'Биде', 'Кнопки смыва'],
            2 => 'Ванны',
            3 => ['Душевые', 'Поддоны, трапы, лотки'],
            4 => 'Смесители',
            5 => 'Мебель для ванной',
            6 => ['Аксессуары для ванной комнаты', 'Аксессуары для ванной'],
            7 => 'Комплектующие',
            8 => 'Полотенцесушители',
            9 => 'Декоративные обои',
            10 => ['Керамогранит', 'керамогранит'],
            11 => ['Керамическая плитка', 'керамическая плитка'],
            12 => 'Натуральный камень',
            13 => ['Мозаика', 'мозаика'],
            14 => 'Кухонные мойки',
            15 => 'клинкер',
            16 => 'SPC-плитка',
        ];

        if (isset($title)) {
            if (str_contains($title, "Биде") or str_contains($title, "биде")) {
                return $subcategories[1];
            } elseif (str_contains($title, "Душевой бокс")) {
                return $subcategories[3];
            } elseif (str_contains($title, 'Раковина')) {
                return $subcategories[0];
            }
        } elseif (!isset($subcategory) and isset($category)) {
            if (str_contains($category, "Керамическая плитка")) {
                return $subcategories[11];
            }
        } elseif (isset($subcategory)) {
            foreach ($subcategories_keys as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $value_i) {
                        if (str_contains($subcategory, $value_i)) {
                            return $subcategories[$key];
                        };
                    }
                } elseif (str_contains($subcategory, $value)) {
                    return $subcategories[$key];
                };
            }
        }
        return null;
    }

    static function getImages(Document $document, string $type = null): string
    {
        $images_res = $document->find('.single-product___main-info--main-image img, .single-product___main-info--thumbnails img, .tile-picture-main img, .tile-picture-prev__item img');
        $images = array();

        foreach ($images_res as $i => $img) {
            if (!$img->attr('src') or $i == 1) continue;
            $i += 1;
            $src = 'https://mosplitka.ru' . $img->attr('src');
            $src = str_replace("60_999_1", "700_370_1c25f2b498b88af7d613b511c3b4f7424", $src); //больше размер
            $src = str_replace("50_999_1", "500_999_1c25f2b498b88af7d613b511c3b4f7424", $src); //больше размер

            if (array_search($src, $images)) continue;
            $images["img$i"] = $src;
        }
        $images = json_encode($images, JSON_UNESCAPED_SLASHES);

        return $images;
    }

    static function getVariants(Document $document): string|null
    {
        $var_res = $document->find('.product-sku__section a, .product-variants-table a');
        if (!$var_res) return null;

        $variants = array();
        $i = 1;
        foreach ($var_res as $var) {
            $src = 'https://mosplitka.ru' . $var->attr('href');
            $variants["var$i"] = $src;
            $i += 1;
        }
        $variants = json_encode($variants, JSON_UNESCAPED_SLASHES);

        return $variants;
    }
}
