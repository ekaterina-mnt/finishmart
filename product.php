<?php
require "functions.php";
require __DIR__ . "/vendor/autoload.php";

use DiDom\Document;

echo "<b>скрипт начал работу " . date("d-m-Y H:i:s", time()) . "</b><br><br>";

try {
    //Получаем ссылку, с которой будем парсить
    try {
        $query = sql("SELECT link, product_views FROM links WHERE type='product' ORDER BY product_views, id LIMIT 1");
    } catch (Throwable $e) {
        //Если too_many_connections
        echo "<b>ошибка: </b>";
        var_dump($e);
        echo "<br><br><b>скрипт закончил работу " . date("d-m-Y H:i:s", time()) . "</b><br><br>";
        exit();
    }

    if (!$query->num_rows) {
        echo "<b>ошибка: не получено ссылки для парсинга</b><br><br>";
        echo "<b>скрипт закончил работу " . date("d-m-Y H:i:s", time()) . "</b><br><br>";
        exit();
    }

    $res = mysqli_fetch_assoc($query);
    $link = $res['link'];
    $views = $res['product_views'] + 1;
    sql("UPDATE links SET product_views=$views WHERE link='$link'");
    $product = sql("SELECT id FROM products WHERE link='$link'");

    echo '<b>скрипт проходил ссылку <a href="' . $link . '">' . $link . '</a></b><br><br>';

    //проверка валидности ссылки
    $ch = curl_init($link);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);

    if (!curl_errno($ch)) {
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code == 404) {
            //если не валидна
            sql("UPDATE products SET link_status='недействительная ссылка' WHERE link='$link'");
            writeCustomLog("Код curl 404. Ссылка, которую парсим - $link");
            echo "<b>ошибка: код ссылки 404</b><br><br>";
            echo "<b>скрипт закончил работу " . date("d-m-Y H:i:s", time()) . "</b><br><br>";
            exit();
        }
        //если валидна
        $document = new Document($link, true);

        //название товара
        $title = $document->find('.single-product___page-header__h1, .tile__title');
        $title = ($title) ? trim($title[0]->text()) : "null";

        //цена
        $price_res = $document->find('.single-product___main-info--price span, .tile-shop__price');
        preg_match("#([0-9 ]+)([^0-9]+)#", $price_res[0]->text(), $carm);
        $price = ($carm) ? (int) str_replace(' ', '', trim($carm[1])) : "null";

        //единица измерения
        $edizm = ($carm) ? str_replace(["₽", "/", "."], '', trim($carm[2])) : "шт";

        //остатки товара
        $stock = $document->find('.single-product___main-info--tag-item.is-green.e__flex.e__aic.e__jcc, .tile-shop-plashki-item.tile-shop-plashki-item__green'); //остатки на складе
        $stock = ($stock) ? str_replace('М', 'м', str_replace(" • ", ", ", trim($stock[0]->text()))) : 'Нет данных';

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
                $articul = $characteristics['Артикул'] ?? "null";
                //производитель
                $producer = $characteristics['Производитель'] ?? null;
                //коллекция
                $collection = $characteristics['Коллекция'] ?? null;
                //длина          
                foreach ($characteristics as $key => $value) {
                    if (str_contains($key, 'Длина')) {
                        $length = (float) str_replace(",", ".", $characteristics[$key]);
                        break;
                    }
                }
                $length = $length ?? "null";
                //ширина
                foreach ($characteristics as $key => $value) {
                    if (str_contains($key, 'Ширина')) {
                        $width = (float) str_replace(",", ".", $characteristics[$key]);
                        break;
                    }
                }
                $width = $width ?? "null";
                //высота
                foreach ($characteristics as $key => $value) {
                    if (str_contains($key, 'Высота')) {
                        $height = (float) str_replace(",", ".", $characteristics[$key]);
                        break;
                    }
                }
                $height = $height ?? "null";
                //глубина
                foreach ($characteristics as $key => $value) {
                    if (str_contains($key, 'Глубина')) {
                        $depth = (float) str_replace(",", ".", $characteristics[$key]);
                        break;
                    }
                }
                $depth = $depth ?? "null";
                //толщина
                foreach ($characteristics as $key => $value) {
                    if (str_contains($key, 'Толщина')) {
                        $thickness = (float) str_replace(",", ".", $characteristics[$key]);
                        break;
                    }
                }
                $thickness = $thickness ?? "null";
                //формат
                foreach ($characteristics as $key => $value) {
                    if (str_contains($key, 'Формат')) {
                        $format = $characteristics[$key];
                        break;
                    }
                }
                $format = $format ?? "null";
                //материал
                foreach ($characteristics as $key => $value) {
                    if (str_contains($key, 'Материал') or str_contains($key, 'Тип материала')) {
                        $material = $characteristics[$key];
                        break;
                    }
                }
                $material = $material ?? "null";
            }

            $characteristics = json_encode($characteristics, JSON_UNESCAPED_UNICODE);
        } else {
            $characteristics = 0;
        }

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
        } else {
            $images = 0;
        }

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
        } else {
            $variants = 0;
        }

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
            $category1 = $categories[0] ?? 'Неизвестна';
            $category2 = $categories[1] ?? null;
            $category3 = $categories[2] ?? null;
        }


        // Итоговый массив для проверки
        $arr = [
            "ссылка" => $link, "остатки" => $stock, "цена" => $price, "ед.изм" => $edizm, "артикул" => $articul,
            "название" => $title, "картинки" => $images, "варианты" => $variants, "характеристики" => $characteristics,
            "путь" => $path, "категория1" => $category1, "категория2" => $category2, "категория3" => $category3,
            "длина" => $length, "ширина" => $width, "высота" => $height, "глубина" => $depth, "толщина" => $thickness,
            "формат" => $format, "материал" => $material, "производитель" => $producer, "коллекция" => $collection,
        ];

        echo "<b>итоговые данные, которые мы спарсили:</b><br><br>";
        foreach ($arr as $key => $i) {
            echo "$key: ";
            var_dump($i);
            echo "<br><br>";
        }


        //добавление/обновление записи в БД

        $types = 'ssissssssssssdddddssss';
        $values = [
            $link, $stock, $price, $edizm, $articul, $title, $images, $variants, $characteristics, $path, $category1, $category2, $category3,
            $length, $width, $height, $depth, $thickness, $format, $material, $producer, $collection
        ];

        if ($product->num_rows) {
            $id = mysqli_fetch_assoc($product)['id'];
            $query = "UPDATE products 
        SET `link`=?, `stock`=?, `price`=?,
        `edizm`=?, `articul`=?, `title`=?, `images`=?, `variants`=?,
        `characteristics`=?, `path`=?, `category1`=?, `category2`=?,
        `category3`=?, `length`=?, `width`=?, `height`=?, `depth`=?, 
        `thickness`=?, `format`=?, `material`=?, `producer`=?, 
        `collection`=?
        WHERE id=$id";
        } else {
            $query = "INSERT INTO products
        (`link`, `stock`, `price`, `edizm`, `articul`, `title`, `images`, `variants`, `characteristics`, `path`, `category1`, `category2`, `category3`, 
        `length`, `width`, `height`, `depth`, `thickness`, `format`, `material`, `producer`, `collection`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        }

        try {
            $db = getDB();
            $stmt = mysqli_prepare($db, $query);
            $stmt->bind_param($types, ...$values);
            $stmt->execute();
            echo "<b>не возникло ошибок с добавлением продукта в БД</b><br><br>";
        } catch (Exception $e) {
            writeLog($e);
            echo "<b>возникла ошибка с добавлением продукта в БД:</b><br>" . $e->getMessage() . '<br><br>';
        }
    }
    curl_close($ch);
} catch (Throwable $e) {
    writeLog($e);
    echo "<b>ошибка в выполнении скрипта:</b><br>";
    var_dump($e);
}
echo "<b>скрипт закончил работу " . date("d-m-Y H:i:s", time()) . "</b><br><br>";
