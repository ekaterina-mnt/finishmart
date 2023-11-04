<?php

use functions\TechInfo;
use functions\Parser;
use functions\Logs;
use functions\Categories;

$attributes_classes = [
    "title" => [
        "h1.head", //olimpparket
        "h1.productCard__title", //laparet
        "h1.good__name", //ntceramic
        "h1.product-title", //domix
    ],

    "price" => [
        ".product", //domix
        ".price span", //olimpparket
        ".productCard__priceValue", //laparet
        ".good-price__value", //ntceramic
    ],

    "stock" => [
        //olimpparket (в скрипте links)
        ".productCard__availability", //laparet
        ".good-available__text", //ntceramic
        "#product-stocks-container tr", //domix
    ],

    "articul" => [
        //larapet (есть только id)
        //olimpparket (в характеристиках)
        ".good-code-and-series .good-block__value", //ntceramic
        ".single-articul .product-article", //domix

    ],

    "characteristics" => [
        ".good-chars-list .good-char", //ntceramic
        ".properties__content .properties__item", //laparet
        ".product-params tr", //olimpparket
        ".sinle-characters-wr.hide-cont .sinle-character", //domix
    ],

    "char_name" => [
        ".good-char__title", //ntceramic
        ".properties__itemName", //laparet
        "th", //olimpparket
        ".sinle-character div", //domix
    ],

    "char_value" => [
        ".good-char__value", //ntceramic
        ".properties__itemDesc", //laparet
        "td", //olimpparket
        ".sinle-character div", //domix
    ],

    "path" => [
        ".breadcrumbs-list .breadcrumbs-item", //ntceramic
        ".breadcrumbs__list .breadcrumbs__item", //laparet
        "ul.breadcrumbs-list li a", //domix
    ],

    "images" => [ //маленькие 
        ".swiper-wrapper a.good-slide__link", //ntceramic
        ".single-vertical meta", //domix
        ".gallery__previews a.gallery__previewsItem", //laparet
        ".more-photo-container a.fancybox-gallery", //olimpparket
        ".main-img a.fancybox-gallery", //olimpparket главное фото
    ]
];

//название товара
$title_res = $document->find(implode(', ', $attributes_classes['title']));
if ($title_res) {
    $all_product_data['title'] = [$title_res[0]->text(), 's'];
    while (str_contains($all_product_data['title'][0], '  ')) {
        $all_product_data['title'][0] = str_replace(["  ", "\t", "\n"], ' ', $all_product_data['title'][0]);
    }
}

//цена
$price_res = $document->find(implode(', ', $attributes_classes['price']));


if ($price_res) {
    //форматирование цены
    if ($price_res[0]->attr('data-calc')) {
        $price_arr = json_decode($price_res[0]->attr('data-calc'), 1, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $price = $price_arr['price_m2'] ?? ($price_arr['offers'][0]['price_m2'] ?? ($price_arr['price_pack'] ?? ($price_arr['price_sht'] ?? ($price_arr['price']) ?? null)));
        $all_product_data['price'] = [(int) str_replace(' ', '', $price), 'i'];
    } elseif (is_array($price_res[0])) {
        foreach ($price_res[0] as $price) {
            if (is_numeric($price)) {
                $all_product_data['price'] = [ceil(str_replace(' ', '', $price)), 'i'];
            }
        }
    } else {
        $all_product_data['price'] = [(int) str_replace(' ', '', $price_res[0]->text()), 'i'];
    }
}

//наличие
$stock_res = $document->find(implode(', ', $attributes_classes['stock']));
if ($stock_res) {
    if (isset($stock_res[1])) {
        $all_product_data['stock'] = ['', 's'];
        foreach ($stock_res as $stock_i) {
            $all_product_data['stock'][0] .= ' ' . trim($stock_i->text()) . ';';
        }
        $all_product_data['stock'][0] = substr($all_product_data['stock'][0], 0, -1);
    } else {
        $all_product_data['stock'] = [$stock_res[0]->text(), 's'];
    }

    $all_product_data['stock'][0] = (trim($all_product_data['stock'][0]) == "Поставка от 2 дней") ? "В наличии" : $all_product_data['stock'][0];
}

//артикул
$articul_res = $document->find(implode(', ', $attributes_classes['articul']));
if ($articul_res) {
    $all_product_data['articul'] = [$articul_res[0]->text(), 's'];

    //форматирование артикула
    // while (str_contains($all_product_data['stock'][0], '  ')) {
    //     $all_product_data['stock'][0] = str_replace(["  ", "\t", "\n"], ' ', $all_product_data['stock'][0]);
    // }
    //
}


//категории из пути
$path_res = $document->find(implode(', ', $attributes_classes['path']));
if ($path_res) {
    $path = Categories::getPath($path_res, $provider);

    $categories = Categories::getCategoriesByPath($path, $provider);
    $all_product_data['category'] = [$categories['category'], 's'];
    if ($categories['subcategory']) {
        $all_product_data['subcategory'] = [$categories['subcategory'], 's'];
    } elseif (isset($categories['category_key']) and !in_array($provider, ['laparet'])) {
        $all_product_data['subcategory'] = [Categories::getSubcategoryByPath($path, $provider, $categories['category_key']), 's'];
        echo $all_product_data['subcategory'][0];
    }
}

//категории из названия товара
if ($provider == 'olimpparket' and isset($all_product_data['title'][0])) {
    $categories = Categories::getCategoriesByTitle($all_product_data['title'][0], $provider);
    $all_product_data['category'] = [$categories['category'], 's'];
    $all_product_data['subcategory'] = [$categories['subcategory'], 's'];
}

//категория


//подкатегория


//все характеристики
$characteristics_res = $document->find(implode(', ', $attributes_classes['characteristics']));
if ($characteristics_res) {
    $characteristics = array();

    foreach ($characteristics_res as $chatact_key => $charact) {
        if ($provider == 'domix') {
            $name = $charact->find(implode(', ', $attributes_classes['char_name']))[0]->text();
            $value = $charact->find(implode(', ', $attributes_classes['char_name']))[1]->text();
        } else {
            $name = $charact->find(implode(', ', $attributes_classes['char_name']))[0]->text();
            $value = $charact->find(implode(', ', $attributes_classes['char_value']))[0]->text();
        }

        $name = trim($name);
        $value = trim($value);

        while (str_contains($value, '  ') or str_contains($value, "\t") or str_contains($name, "\n") or str_contains($name, '  ') or str_contains($name, "\t") or str_contains($name, "\n")) {
            $value = str_replace(["  ", "\t", "\n"], ' ', $value);
            $name = str_replace(["  ", "\t", "\n"], ' ', $name);
        }

        $characteristics[$name] = $value;

        if (str_contains($name, "Артикул")) {
            $all_product_data['articul'] = [$value, 'i'];
        }

        if (str_contains($name, "Категория") and !isset($all_product_data['subcategory'][0])) {
            $subcategory = Categories::getSubcategoryByCharacteristics($value);
            $all_product_data['subcategory'] = [$value, 'i'];
        }
    }


    $characteristics = json_encode($characteristics, JSON_UNESCAPED_UNICODE);
    $all_product_data['characteristics'] = [$characteristics, 's'];
}
//


//форматирование подкатегории
foreach ($all_product_data as $data_key => $data_value) {
    $all_product_data[$data_key][0] = trim($all_product_data[$data_key][0]);

    if (isset($data_value[0])) {
        while (str_contains($all_product_data[$data_key][0], '  ') or str_contains($all_product_data[$data_key][0], "\t") or str_contains($all_product_data[$data_key][0], "\n")) {
            $all_product_data[$data_key][0] = str_replace(["  ", "\t", "\n"], ' ', $all_product_data[$data_key][0]);
        }
    }
}

if (isset($all_product_data['title']) and isset($all_product_data['subcategory'])) {
    $all_product_data['subcategory'][0] = (mb_strtolower($all_product_data['title'][0]) == mb_strtolower($all_product_data['subcategory'][0])) ? null : $all_product_data['subcategory'][0];
}

//картинки
$images_res = $document->find(implode(', ', $attributes_classes['images']));
if ($images_res) {
    $images = Parser::getImages($images_res, $provider) ?? null;
    $all_product_data['images'] = [$images, 's'];
}
