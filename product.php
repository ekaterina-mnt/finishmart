<?php

require __DIR__ . "/vendor/autoload.php";

use DiDom\Document;

$db = mysqli_connect('localhost', 'root', '', 'parser');
mysqli_query($db, 'SET character_set_results = "utf8"');

$query = mysqli_query($db, "SELECT link, product_views FROM links WHERE type='product' ORDER BY product_views, id LIMIT 1");

if ($query->num_rows) {
    $res = mysqli_fetch_assoc($query);
    $link = $res['link'];
    $views = $res['product_views'] + 1;
    // mysqli_query($db, "UPDATE links SET product_views=$views WHERE link='$link'");
}

$link = "https://mosplitka.ru/product/korzina-dlya-belya-weltwasser-lety-mt-50l-10000003989-matovaya-stal/";

$document = new Document($link, true);

//название товара
$title = $document->find('.single-product___page-header__h1, .tile__title');
$title = ($title) ? trim($title[0]->text()) : null;

//цена
$price_res = $document->find('.single-product___main-info--price span, .tile-shop__price');
preg_match("#([0-9 ]+)([^0-9]+)#", $price_res[0]->text(), $carm);
$price = ($carm) ? (int) str_replace(' ', '', trim($carm[1])) : null;

//единица измерения
$edizm = ($carm) ? trim($carm[2]) : null;

//остатки товара
$stock = $document->find('.single-product___main-info--tag-item.is-green.e__flex.e__aic.e__jcc, .tile-shop-plashki-item.tile-shop-plashki-item__green'); //остатки на складе
$stock = ($stock) ? str_replace('М', 'м', str_replace(" • ", ", ", trim($stock[0]->text()))) : 'Нет данных';

//все характеристики
$characteristics_res = $document->find('.single-product___atts-att.e__flex.e__aic, .tile-prop-tabs__item');
$characteristics = array();
if ($characteristics_res) {

    foreach ($characteristics_res as $charact) {
        $name = trim($charact->find('.q_prop__name, .tile-prop-tabs__name')[0]->text());
        $value = trim($charact->find('.q_prop__value, .tile-prop-tabs__value')[0]->text());
        $characteristics[$name] = $value;
    }

    if ($characteristics) {
        //артикул
        $articul = $characteristics['Артикул'] ?? null;
        //производитель
        $producer = $characteristics['Производитель'] ?? null;
        //коллекция
        $collection = $characteristics['Коллекция'] ?? null;
        //длина          
        foreach ($characteristics as $key => $value) {
            if (str_contains($key, 'Длина') OR str_contains($key, 'длина')) {
                $length = $characteristics[$key];
                break;
            }
        }
        $length = $length ?? null;
        //ширина
        foreach ($characteristics as $key => $value) {
            if (str_contains($key, 'Ширина') OR str_contains($key, 'ширина')) {
                $width = $characteristics[$key];
                break;
            }
        }
        $width = $width ?? null;
        //высота
        foreach ($characteristics as $key => $value) {
            if (str_contains($key, 'Высота') OR str_contains($key, 'высота')) {
                $height = $characteristics[$key];
                break;
            }
        }
        $height = $height ?? null;
        //глубина
        foreach ($characteristics as $key => $value) {
            if (str_contains($key, 'Глубина') OR str_contains($key, 'глубина')) {
                $depth = $characteristics[$key];
                break;
            }
        }
        $depth = $depth ?? null;
        //толщина
        foreach ($characteristics as $key => $value) {
            if (str_contains($key, 'Толщина') OR str_contains($key, 'толщина')) {
                $thickness = $characteristics[$key];
                break;
            }
        }
        $thickness = $thickness ?? null;
        //формат
        foreach ($characteristics as $key => $value) {
            if (str_contains($key, 'Формат') OR str_contains($key, 'формат')) {
                $format = $characteristics[$key];
                break;
            }
        }
        $format = $format ?? null;
    }

    $characteristics = json_encode($characteristics, JSON_UNESCAPED_UNICODE);
}

//картинки
$imgs_res = $document->find('.single-product___main-info--thumbnail__img img, .tile-picture-prev__item img, .single-product___main-info--thumbnail.e__flex.e__aic.e__jcc.e__pointer.is-active img, .single-product___main-info--main-image.e__w100.e__pointer img');
$imgs = array();
var_dump($imgs_res);
if ($imgs_res) {
    $i = 1;

    foreach ($imgs_res as $img) {
        $src = 'https://mosplitka.ru' . $img->attr('src');
        $src = str_replace("60_999", "700_370", $src); //больше размер
        $src = str_replace("50_999", "500_999", $src); //больше размер
        $imgs["img$i"] = $src;
        $i += 1;
    }
    $imgs = json_encode($imgs, JSON_UNESCAPED_SLASHES);
}

//варианты исполнения
$var_res = $document->find('.product-sku__section a');
$variants = array();
if ($var_res) {
    $i = 1;

    foreach ($var_res as $var) {
        $src = 'https://mosplitka.ru' . $var->attr('href');
        $variants["var$i"] = $src;
        $i += 1;
    }
    $variants = json_encode($variants, JSON_UNESCAPED_SLASHES);
}
$variants = (!empty($variants)) ? $variants : "";

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
                echo "$a<br><br>";
                $categories[] = $a;
            }
        } elseif (isset($producer) && !isset($collection)) {
            if ($a != 'На главную' && $a != 'Каталог' && !str_contains($a, $producer)) {
                echo "$a<br><br>";
                $categories[] = $a;
            }
        } elseif (!isset($producer) && !isset($collection)) {
            if ($a != 'На главную' && $a != 'Каталог') {
                echo "$a<br><br>";
                $categories[] = $a;
            }
        } else {
            if ($a != 'На главную' && $a != 'Каталог' && !str_contains($a, $producer) && !str_contains($a, $collection)) {
                echo "$a<br><br>";
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
    "название" => $title, "картинки" => $imgs, "варианты" => $variants, "характеристики" => $characteristics,
    "путь" => $path, "категория1" => $category1, "категория2" => $category2, "категория3" => $category3,
    "длина" => $length, "ширина" => $width, "высота" => $height, "глубина" => $depth, "толщина" => $thickness,
    "формат" => $format,
];
foreach ($arr as $key => $i) {
    echo "$key: ";
    var_dump($i);
    echo "<br><br>";
}

// try {
//     mysqli_query($db, "INSERT INTO products
//     (`link`, `stock`, `price`, `articul`, `title`, `images`, `variants`, `characteristics`, `path`, `category1`, `category2`, `category3`) 
//     VALUES ('$link', '$stock', '$price', '$articul', '$title', '$imgs', '$variants', '$characteristics', '$path', '$category1', '$category2', '$category3')");
// } catch (Exception $e) {
// }
