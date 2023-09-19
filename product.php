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
    mysqli_query($db, "UPDATE links SET product_views=$views WHERE link='$link'");
}

$document = new Document($link, true);

//название товара
$title = $document->find('.single-product___page-header__h1, .tile__title');
$title = ($title) ? trim($title[0]->text()) : null;

//цена
$price = $document->find('.single-product___main-info--price span, .tile-shop__price');
$price = ($price) ? trim($price[0]->text()) : null;

//остатки товара
$stock = $document->find('.single-product___main-info--tag-item.is-green.e__flex.e__aic.e__jcc, .tile-shop-plashki-item.tile-shop-plashki-item__green'); //остатки на складе
$stock = ($stock) ? trim($stock[0]->text()) : 'Нет данных';

//все характеристики
$characteristics_res = $document->find('.single-product___atts-att.e__flex.e__aic, .tile-prop-tabs__item');
$characteristics = array();
if ($characteristics_res) {

    foreach ($characteristics_res as $charact) {
        $name = trim($charact->find('.q_prop__name, .tile-prop-tabs__name')[0]->text());
        $value = trim($charact->find('.q_prop__value, .tile-prop-tabs__value')[0]->text());
        $characteristics[$name] = $value;
    }

    //артикул
    if ($characteristics) {
        $articul = $characteristics['Артикул'];
    }

    $characteristics = json_encode($characteristics, JSON_UNESCAPED_UNICODE);
}

//картинки
$imgs_res = $document->find('.single-product___main-info--thumbnail__img, .tile-picture-prev__item');
$imgs = array();
if ($imgs_res) {
    $i = 1;

    foreach ($imgs_res as $img) {
        $src = 'https://mosplitka.ru' . $img->attr('src');
        $src = str_replace("60_999", "700_370", $src); //больше размер
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
}

// Итоговый массив для проверки
$arr = [
    "ссылка" => $link, "остатки" => $stock, "цена" => $price, "артикул" => $articul,
    "название" => $title, "картинки" => $imgs, "варианты" => $variants, "характеристики" => $characteristics,
    "путь" => $path,
];
foreach ($arr as $key => $i) {
    echo "$key: ";
    var_dump($i);
    echo "<br><br>";
}

try {
    mysqli_query($db, "INSERT INTO products
    (`link`, `stock`, `price`, `articul`, `title`, `images`, `variants`, `characteristics`, `path`) 
    VALUES ('$link', '$stock', '$price', '$articul', '$title', '$imgs', '$variants', '$characteristics', '$path')");
} catch (Exception $e) {
}
