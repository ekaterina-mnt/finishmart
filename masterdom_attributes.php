<?php

use functions\MySQL;
use functions\Logs;
use functions\TechInfo;
use functions\Parser;
use functions\ParserMasterdom;

//Проверяем есть ли следующая ссылка для выгрузки товаров + добавляем если есть
$limit = str_contains($url_parser, "oboi.masterdom") ? 30 : 100;
$next_link = Parser::nextLink($url_parser, $limit);
if ($next_link) {
    
    $query = "INSERT INTO all_links (link, type, provider) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE type='product'";
    $types = "sss";
    $values = [$next_link, 'product', 'masterdom'];
    MySQL::bind_sql($query, $types, $values);
    echo "<b>Следующая ссылка: </b> $next_link (добавлена в БД)<br><br>";
} else {
    echo "<b>Следующая ссылка: </b> нет<br><br>";
}

if (str_contains($url_parser, 'polotencesushitely/catalog')) {
    $country_coll_producer_res = ParserMasterdom::getDataPolotencesushitely();
    $api_data = $country_coll_producer_res['api_data'];

    $category = 'Сантехника';
} else {
    //категория
    $category = ParserMasterdom::getCategory($url_parser);

    //Нужные массивы до цикла (для плитки и сантехники)
    switch ($category) {
        case "Плитка и керамогранит":
            $country_coll_producer_res = ParserMasterdom::getDataPlitka();
            break;
        case "Сантехника":
            $country_coll_producer_res = ParserMasterdom::getDataSantechnika();
            break;
        case "Обои и настенные покрытия":
            $country_coll_producer_res = ParserMasterdom::getDataOboi();
            break;
    }

    //Начинаем вытаскивать нужные данные
    $api_data = Parser::getApiData($document);
}

$category = $category ?? null;

$api_data = $api_data ?? null;

$fabrics = isset($country_coll_producer_res['fabrics']) ? $country_coll_producer_res['fabrics'] : null;
$collections = isset($country_coll_producer_res['collections']) ? $country_coll_producer_res['collections'] : null;
$countries = isset($country_coll_producer_res['countries']) ? $country_coll_producer_res['countries'] : null;
$usage_array = isset($country_coll_producer_res['usage']) ? $country_coll_producer_res['usage'] : null;


//НЕПОСРЕДСТВЕННАЯ ОБРАБОТКА ПОЛУЧЕННЫХ ДАННЫХ


echo "<b>Всего товаров в ссылке:</b> " . count($api_data) . " шт.<br><br>";
$product_ord_num = 1;
foreach ($api_data as $datum) {
    echo "<br><b>Товар " . $product_ord_num++ . "</b><br><br>";
    $all_product_data = [];
    $all_product_data['provider'] = ['masterdom', 's'];
    $all_product_data['api_parser_link'] = [$url_parser, 's'];
    //ОБЩИЕ ДЛЯ ВСЕХ

    //название товара (сантехника - full_name)
    $title = (isset($datum['fullname']) ? $datum['fullname'] : $datum['full_name']) ?? null;
    $all_product_data['title'] = [$title, 's'];

    //артикул
    $articul = $datum['article'] ?? null;
    $all_product_data['articul'] = [$articul, 's'];

    //категория
    $all_product_data['category'] = [$category, 's'];

    //подкатегория
    $subcategory = ParserMasterdom::getSubcategory($category, $datum) ?? null;
    $all_product_data['subcategory'] = [$subcategory, 's'];

    //ссылка на товар
    $product_id = $datum['id'] ?? null;
    $name_url = $datum['name_url'] ?? null;
    $product_link = ParserMasterdom::getProductLink($subcategory, $articul, $product_id, $name_url);
    $all_product_data['link'] = [$product_link, 's'];

    //цена
    $price = (isset($datum['price_site']) ? $datum['price_site'] : $datum['price']) ?? null;
    $all_product_data['price'] = [$price, 'i'];


    //единица измерения
    $edizm = Parser::getEdizm($category) ?? null;
    $all_product_data['edizm'] = [$edizm, 's'];

    //остатки товара
    $stock = isset($datum['balance']) ? $datum['balance'] : null;
    $all_product_data['stock'] = [$stock, 'i'];

    //страна
    $country_key = (isset($datum['country']) ? $datum['country'] : $datum['data']['country']) ?? null;
    $country = $countries[$country_key]['name'] ?? null;
    $all_product_data['country'] = [$country, 's'];

    //производитель
    switch ($category) {
        case "Сантехника":
        case "Плитка и керамогранит":
            $producer_key = $datum['fabric'] ?? null;
            $producer = $fabrics[$producer_key]['name'] ?? null;
            break;
        default:
            $producer = (isset($datum['fabric_name']) ? $datum['fabric_name'] : $datum['fabric']) ?? null;
            break;
    }
    $all_product_data['producer'] = [$producer, 's'];

    //коллекция
    switch ($category) {
        case "Сантехника":
        case "Плитка и керамогранит":
            $collection_key = $datum['collection'] ?? null;
            $collection = $collections[$collection_key]['name'] ?? null;
            break;
        default:
            $collection = $datum['collection_name'];
            break;
    }
    $all_product_data['collection'] = [$collection, 's'];

    //длина
    $length = $datum['length'] ?? null;
    $all_product_data['length'] = [$length, 'd'];

    //ширина
    $width = $datum['width'] ?? null;
    $all_product_data['width'] = [$width, 'd'];

    //высота
    $height = $datum['height'] ?? null;
    $all_product_data['width'] = [$height, 'd'];

    //глубина
    $depth = null;
    $all_product_data['depth'] = [$depth, 'd'];

    //толщина
    $thickness = null;
    $all_product_data['thickness'] = [$thickness, 'd'];

    //формат
    $format = null;
    $all_product_data['format'] = [$format, 's'];

    //материал
    $material = $datum['тип'] ?? null;
    $all_product_data['material'] = [$material, 's'];

    //картинки
    $images = ParserMasterdom::getImages($datum, $url_parser);
    $all_product_data['images'] = [$images, 's'];

    //варианты исполнения
    $variants = null;
    $all_product_data['variants'] = [$variants, 's'];

    //СПЕЦИФИЧЕСКИЕ АТРИБУТЫ

    //назначение
    $usage_keys = $datum['product_usages'] ?? null;
    if ($usage_keys) {
        $usage = [];
        foreach ($usage_keys as $usage_i) {
            $usage[$usage_i] = $usage_array[$usage_i]['name'];
        }
        $usage = json_encode($usage, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
    $usage = $usage ?? null;
    $all_product_data['product_usages'] = [$usage, 's'];


    //тип
    if ($subcategory == 'Унитазы, писсуары и биде') {
        $type = $datum['product_kind'];
    }
    $type = $type ?? null;
    $all_product_data['type'] = [$type, 's'];

    //все характеристики
    $characteristics = json_encode($datum, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $all_product_data['characteristics'] = [$characteristics, 's'];

    include "insert_ending.php";
}
