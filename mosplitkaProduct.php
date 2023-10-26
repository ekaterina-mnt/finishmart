<?php
require __DIR__ . "/vendor/autoload.php";

use functions\MySQL;
use functions\Logs;
use functions\TechInfo;
use functions\Parser;
use functions\ParserMasterdom;

TechInfo::start();

try {
    $provider = 'mosplitka';
    // $provider = Parser::getProvider($url_parser); 

    //Получаем ссылку, с которой будем парсить
    $query = MySQL::sql("SELECT link, product_views FROM " . $provider . "_links WHERE type='product' ORDER BY product_views, id LIMIT 1");

    if (!$query->num_rows) {
        Logs::writeCustomLog("не получено ссылки для парсинга");
        TechInfo::errorExit("не получено ссылки для парсинга");
    }

    $res = mysqli_fetch_assoc($query);

    //Получаем ссылку
    $url_parser = $res['link'];
    $url_parser = "https://mosplitka.ru/product/umyvalnik-santek-animo-60/";

    TechInfo::whichLinkPass($url_parser);

    //Увеличиваем просмотры ссылки
    $views = $res['product_views'] + 1;
    $date_edit = MySQL::get_mysql_datetime();
    MySQL::sql("UPDATE " . $provider . "_links SET product_views=$views, date_edit='$date_edit' WHERE link='$url_parser'"); //поменять имя таблицы

    //Получаем html страницы
    try {
        $document = Parser::guzzleConnect($url_parser);
        $all_product_data = [];

        //название товара
        $title = $document->find('.single-product___page-header__h1, .tile__title');
        $title = ($title) ? trim($title[0]->text()) : null;

        $all_product_data['title'] = [$title, 's'];

        //цена
        $price_res = $document->find('.single-product___main-info--price span, .tile-shop__price');
        preg_match("#([0-9 ]+)([^0-9]+)#", $price_res[0]->text(), $carm);
        $price = ($carm) ? (int) str_replace(' ', '', trim($carm[1])) : null;

        $all_product_data['price'] = [$price, 'i'];

        //единица измерения
        $edizm = ($carm) ? str_replace(["₽", "/", "."], '', trim($carm[2])) : "шт";

        $all_product_data['edizm'] = [$edizm, 's'];

        //остатки товара
        $stock = $document->find('.single-product___main-info--tag-item.is-green.e__flex.e__aic.e__jcc, .tile-shop-plashki-item.tile-shop-plashki-item__green'); //остатки на складе
        $stock = ($stock) ? str_replace('М', 'м', str_replace(" • ", ", ", trim($stock[0]->text()))) : 'Нет данных';

        $all_product_data['stock'] = [$stock, 's'];

        //все характеристики
        $characteristics_res = $document->find('.single-product___atts-att.e__flex.e__aic, .tile-prop-tabs__item');
        if ($characteristics_res) {
            $characteristics = array();

            foreach ($characteristics_res as $charact) {
                $name = trim($charact->find('.q_prop__name, .tile-prop-tabs__name')[0]->text());
                $value = trim($charact->find('.q_prop__value, .tile-prop-tabs__value')[0]->text());
                $characteristics[$name] = $value;
            }

            if ($characteristics) {
                //артикул
                $articul = $characteristics['Артикул'] ?? null;
                $all_product_data['articul'] = [$articul, 's'];

                //производитель
                $producer = $characteristics['Производитель'] ?? null;
                $all_product_data['producer'] = [$producer, 's'];

                //коллекция
                $collection = $characteristics['Коллекция'] ?? null;
                $all_product_data['collection'] = [$collection, 's'];

                //длина          
                foreach ($characteristics as $key => $value) {
                    if (str_contains($key, 'Длина')) {
                        $length = (float) str_replace(",", ".", $characteristics[$key]);
                        break;
                    }
                }
                $length = $length ?? null;
                $all_product_data['length'] = [$length, 's'];
                //ширина
                foreach ($characteristics as $key => $value) {
                    if (str_contains($key, 'Ширина')) {
                        $width = (float) str_replace(",", ".", $characteristics[$key]);
                        break;
                    }
                }
                $width = $width ?? null;
                $all_product_data['width'] = [$width, 's'];
                //высота
                foreach ($characteristics as $key => $value) {
                    if (str_contains($key, 'Высота')) {
                        $height = (float) str_replace(",", ".", $characteristics[$key]);
                        break;
                    }
                }
                $height = $height ?? null;
                $all_product_data['height'] = [$height, 's'];
                //глубина
                foreach ($characteristics as $key => $value) {
                    if (str_contains($key, 'Глубина')) {
                        $depth = (float) str_replace(",", ".", $characteristics[$key]);
                        break;
                    }
                }
                $depth = $depth ?? null;
                $all_product_data['depth'] = [$depth, 's'];
                //толщина
                foreach ($characteristics as $key => $value) {
                    if (str_contains($key, 'Толщина')) {
                        $thickness = (float) str_replace(",", ".", $characteristics[$key]);
                        break;
                    }
                }
                $thickness = $thickness ?? null;
                $all_product_data['thickness'] = [$thickness, 's'];
                //формат
                foreach ($characteristics as $key => $value) {
                    if (str_contains($key, 'Формат')) {
                        $format = $characteristics[$key];
                        $format = str_replace(["X", "Х", "x", "х"], "x", $format);
                        break;
                    }
                }
                $format = $format ?? null;
                $all_product_data['format'] = [$format, 's'];
                //материал
                foreach ($characteristics as $key => $value) {
                    if (str_contains($key, 'Материал') or str_contains($key, 'Тип материала')) {
                        $material = $characteristics[$key];
                        break;
                    }
                }
                $material = $material ?? null;
                $all_product_data['material'] = [$material, 's'];
            }

            $characteristics = json_encode($characteristics, JSON_UNESCAPED_UNICODE);
        }
        $characteristics = $characteristics ?? null;
        $all_product_data['characteristics'] = [$characteristics, 's'];

        //картинки
        $images_res = $document->find('.single-product___main-info--thumbnail__img img, .tile-picture-prev__item img, .single-product___main-info--thumbnail.e__flex.e__aic.e__jcc.e__pointer.is-active img, .single-product___main-info--main-image.e__w100.e__pointer img');
        if ($images_res) {
            $images = array();
            $i = 1;

            foreach ($images_res as $img) {
                $src = 'https://mosplitka.ru' . $img->attr('src');
                $src = str_replace("60_999", "700_370", $src); //больше размер
                $src = str_replace("50_999", "500_999", $src); //больше размер
                $images["img$i"] = $src;
                $i += 1;
            }
            $images = json_encode($images, JSON_UNESCAPED_SLASHES);
        }
        $images = $images ?? null;
        $all_product_data['images'] = [$images, 's'];

        //варианты исполнения
        $var_res = $document->find('.product-sku__section a');

        if ($var_res) {
            $variants = array();
            $i = 1;

            foreach ($var_res as $var) {
                $src = 'https://mosplitka.ru' . $var->attr('href');
                $variants["var$i"] = $src;
                $i += 1;
            }
            $variants = json_encode($variants, JSON_UNESCAPED_SLASHES);
        }
        $variants = $variants ?? null;
        $all_product_data['variants'] = [$variants, 's'];

        //путь
        $path_res = $document->find('.product-breadcrumb a, .breadcrumb_cont a');
        $path = "";
        if ($path_res) {
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
            $category = $categories[0] ?? null;
            $subcategory = $categories[1] ?? null;

            echo "path: $path<br<br>";

            $all_product_data['category'] = [$category, 's'];
            $all_product_data['subcategory'] = [$subcategory, 's'];

            $all_product_data['variants'] = [$variants, 's'];

            echo "<b>итоговые данные, которые мы спарсили:</b><br><br>";
            $print_result = [];
            foreach ($all_product_data as $key => $val) {
                $print_result[$key] = $val[0];
            }
            TechInfo::preArray($print_result);
        }
    } catch (Throwable $e) {
        MySQL::decreaseViews($views, $url_parser);
        Logs::writeLog($e, $url_parser);
        TechInfo::errorExit($e);
    }
} catch (\Throwable $e) {
    Logs::writeLog($e);
    TechInfo::errorExit($e);
    var_dump($e);
}

TechInfo::end();
