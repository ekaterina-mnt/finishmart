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
        ".container.main__container h1", //finefloor
        "h1.sproduct-title", //tdgalion
        ".product-detailed h1", //dplintus
        ".item h1", //centerkrasok
        "h1.item-detail__header", //alpinefloor
        ".product_title", //lkrn
    ],

    "price" => [
        ".single-price", //domix
        ".product-page.product_details .price span", //olimpparket
        // ".productCard__priceValue", //laparet
        // ".good-price__value", //ntceramic
        // ".catalog__product__info__price .price", //finefloor
        // ".sproduct-price__value", //tdgalion
        // ".product-item-detail-price-current", //dplintus
        // ".sku-info .price", //centerkrasok
        // ".item-detail-price__value", //alpinefloor
        ".price .woocommerce-Price-amount bdi"
    ],

    "stock" => [
        // olimpparket (в скрипте links)
        ".productCard__availability", //laparet
        ".good-available__text", //ntceramic
        "#product-stocks-container tr", //domix
        ".product-item-rest-list .amount", //dplintus
    ],

    "articul" => [
        //larapet (есть только id)
        //olimpparket (в характеристиках)
        ".good-code-and-series .good-block__value", //ntceramic
        ".single-articul .product-article", //domix
        ".product-detailed .art", //dplintus

    ],

    // "characteristics" => [
    //     ".good-chars-list .good-char", //ntceramic
    //     ".properties__content .properties__item", //laparet
    //     ".product-params tr", //olimpparket
    //     ".sinle-characters-wr.hide-cont .sinle-character", //domix
    //     ".specifications__table .specifications__table__row", //finefloor
    //     ".sproduct-charact__list", //tdgalion
    //     ".sproduct-info__item", //tdgalion
    //     ".product-item-detail-properties dt", //dplintus
    // ],

    "char_double_count" => [
        "#chars-table tr", //alpinefloor
    ],

    "char_double" => [ //где нет четкого различия в классах между значением и ключом
        "#chars-table.table td", //alpinefloor
        ".woocommerce-product-details__short-description p", //lkrn
    ],

    "characteristics_count" => [
        ".good-char__title", //ntceramic
        ".properties__itemName", //laparet
        "th", //olimpparket
        ".sinle-character", //domix
        ".specifications__table__name", //finefloor
        ".sproduct-charact__name", //tdgalion
        ".sproduct-info__name", //tdgalion
        ".product-item-detail-properties dt", //dplintus
        ".item-detail-classes .item-detail-class", //alpinefloor
    ],

    "char_name" => [
        ".good-char__title", //ntceramic
        ".properties__itemName", //laparet
        "th", //olimpparket
        ".sinle-character div", //domix
        ".specifications__table__name", //finefloor
        ".sproduct-charact__name", //tdgalion
        ".sproduct-info__name", //tdgalion
        ".product-item-detail-properties dt", //dplintus
        ".item-detail-class__title", //alpinefloor
    ],

    "char_value" => [
        ".good-char__value", //ntceramic
        ".properties__itemDesc", //laparet
        ".product-params td", //olimpparket
        ".sinle-character div", //domix
        ".specifications__table__value", //finefloor
        ".sproduct-charact__value", //tdgalion
        ".sproduct-info__value", //tdgalion
        ".product-item-detail-properties dd", //dplintus
        ".item-detail-class__name", //alpinefloor
    ],

    "path" => [
        ".breadcrumbs-list .breadcrumbs-item", //ntceramic
        ".breadcrumbs__list .breadcrumbs__item", //laparet
        "ul.breadcrumbs-list li a", //domix
        ".bx-breadcrumb .bx-breadcrumb-item", //dplintus
    ],

    "images" => [ //маленькие 
        ".swiper-wrapper a.good-slide__link", //ntceramic
        ".single-vertical meta", //domix
        ".gallery__previews a.gallery__previewsItem", //laparet
        ".more-photo-container a.fancybox-gallery", //olimpparket
        ".main-img a.fancybox-gallery", //olimpparket главное фото
        "a.catalog__image__big__slide", //finefloor
        "img.product-item-detail-slider-img", //dplintus
        ".imageItemBig .innerImGItem", //tdgalion
        ".item-detail-slide a", //alpinefloor
        ".woocommerce-product-gallery__image a", //lkrn
    ],

    "good_id_from_provider" => [],

    "category" => [
        ".sproduct-info__value",
        ".posted_in a", //lkrn
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
    } elseif ($provider == 'lkrn') {
        // var_dump($price_res[0]);
        $all_product_data['price'] = [(int) str_replace([",", "₽"], '', $price_res[0]->text()), 'i'];
        // var_dump($price_res);
        // echo htmlspecialchars($price_res);
    } elseif (is_array($price_res[0])) {
        foreach ($price_res[0] as $price) {
            if (is_numeric($price)) {
                $all_product_data['price'] = [(int) str_replace(' ', '', $price), 'i'];
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
    $all_product_data['articul'] = [str_replace('Артикул: ', '', $articul_res[0]->text()), 's'];

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


//категории из названия/ссылки товара/провайдера
if ($provider == 'olimpparket' and isset($all_product_data['title'][0])) {
    $categories = Categories::getCategoriesByTitle($all_product_data['title'][0], $provider);
    $all_product_data['category'] = isset($categories['category']) ? [$categories['category'], 's'] : [Parser::getCategoriesList()[1], 's'];
    $all_product_data['subcategory'] = isset($categories['subcategory']) ? [$categories['subcategory'], 's'] : [Parser::getSubcategoriesList()[26], 's'];
} elseif ($provider == 'finefloor') {
    $all_product_data['category'] = [Parser::getCategoriesList()[1], 's'];
    $all_product_data['subcategory'] = [Parser::getSubcategoriesList()[37], 's'];
} elseif ($provider == 'alpinefloor') {
    $categories = Categories::getCategoriesByLink($url_parser, $provider);
    $all_product_data['category'] = isset($categories['category']) ? [$categories['category'], 's'] : [null, 's'];
    $all_product_data['subcategory'] = isset($categories['subcategory']) ? [$categories['subcategory'], 's'] : [null, 's'];
}

//категория
if ($provider == 'lkrn') {
    $categories_res = $document->find(implode(', ', $attributes_classes['category']));
    $all_product_data['category'] = !empty($categories_res) ? [$categories_res[0]->text(), 's'] : [null, 's'];
}

//подкатегория


//все характеристики
$characteristics_count = count($document->find(implode(', ', $attributes_classes['characteristics_count']))) - 1;
$char_names = $document->find(implode(', ', $attributes_classes['char_name']));
$char_values = $document->find(implode(', ', $attributes_classes['char_value']));
$char_double_count = count($document->find(implode(', ', $attributes_classes['char_double_count']))) - 1;
$char_double = $document->find(implode(', ', $attributes_classes['char_double']));

if ($provider == 'lkrn') {
    $char_res = "";
    $html_char_res = "";

    foreach ($char_double as $m) {
        $html_char_res .= htmlspecialchars($m);
        $char_res .= ' ' . $m->text() . ' ';
    }

    while (str_contains($char_res, '  ') or str_contains($char_res, "\t") or str_contains($char_res, "\n") or str_contains($char_res, " \/ ") or str_contains($html_char_res, '  ') or str_contains($html_char_res, "\t") or str_contains($html_char_res, "\n") or str_contains($html_char_res, " \/ ")) {
        $html_char_res = str_replace(["  ", "\t", "\n", " \/ "], ' ', $html_char_res);
        $char_res = str_replace(["  ", "\t", "\n", " \/ "], ' ', $char_res);
    }

    $characteristics = json_encode(['text' => $char_res, 'html' => $html_char_res], JSON_UNESCAPED_UNICODE);
    $all_product_data['characteristics'] = [$characteristics, 's'];
} elseif ($characteristics_count > 0) {
    if ($char_double_count > 0) {
        foreach (range(0, count($char_double) - 1) as $charact) {

            if ($charact % 2) continue;
            // echo $charact . "\r". $char_double[$charact] . "<br><br>";
            // echo $charact . "\r". $char_double[$charact +1] . "<br><br>";
            $char_names[] = $char_double[$charact];
            $char_values[] = $char_double[$charact + 1];
        }
    }
    // foreach (range(0, count($char_names) - 1) as $charact) {
    //     echo $charact . "\r". $char_names[$charact] . "<br><br>";
    //     echo $charact . "\r". $char_values[$charact] . "<br><br>";
    // }

    $characteristics = array();

    foreach (range(0, count($char_names) - 1) as $charact) {
        if ($provider == 'domix') {
            if ($charact % 2) continue;
            $name = $char_names[$charact]->text();
            $value = $char_names[$charact + 1]->text();
        } else {
            $name = $char_names[$charact]->text();
            $value = $char_values[$charact]->text();
        }

        $name = str_replace(":", '', trim($name));
        $value = trim($value);

        while (str_contains($value, '  ') or str_contains($value, "\t") or str_contains($name, "\n") or str_contains($name, '  ') or str_contains($name, "\t") or str_contains($name, "\n")) {
            $value = str_replace(["  ", "\t", "\n"], ' ', $value);
            $name = str_replace(["  ", "\t", "\n"], ' ', $name);
        }

        $characteristics[$name] = $value;

        if ((str_contains(mb_strtolower($name), "артикул") and $provider != 'tdgalion') or
            (str_contains(mb_strtolower($name), 'код товара') and $provider == 'alpinefloor')
        ) {
            $all_product_data['articul'] = [$value, 'i'];
        }

        if (str_contains(mb_strtolower($name), "код 1с")) {
            $all_product_data['good_id_from_provider'] = [$value, 'i'];
        }

        if ((str_contains($name, "категория") and !isset($all_product_data['subcategory'][0])) or
            ((str_contains(mb_strtolower($name), "тип товара") and !isset($all_product_data['subcategory'][0]) and $provider == 'tdgalion'))
        ) {
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

    if (isset($data_value[0])) {
        $all_product_data[$data_key][0] = trim($all_product_data[$data_key][0]);
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
