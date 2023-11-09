<?php

use functions\TechInfo;
use functions\Parser;
use functions\Logs;
use functions\Categories;

$dataSection = $document->find('#wrap-news')[0]->attr('data-section');
$collection = $document->find('.catalog_info h1')[0]->text();
$fabric = $document->find('.catalog_info h2')[0]->text();

$apiLink = "https://surgaz.ru/ajax.php?ajax=Y&PAGEN_1=1&SECTION_ID=$dataSection&PAGE_ELEMENT_COUNT=1000&LANGUAGE_ID=ru&act=collection";
$apiDocument = Parser::guzzleConnect($apiLink, "windows-1251");
TechInfo::whichLinkPass($apiLink, 1);

// $articuls_res = array_values(array_unique($document->find('.number')));
$articuls_res = $document->find('.info .number');
$images_res = $apiDocument->find('.gallery a');

$goods = $apiDocument->find('.item');

foreach ($goods as $num => $good) {
    echo "<br><b>Товар " . ++$num . "</b><br><br>";
    $info = $good->first('.info');

    $all_product_data = [];

    $all_product_data['collection'] = [$collection, 's'];
    $all_product_data['producer'] = [$fabric, 's'];
    $all_product_data['provider'] = [$provider, 's'];

    //материал
    $all_product_data['material'] = [str_replace("МАТЕРИАЛ ", "", $info->find('.material')[0]->text()), 's'];

    //размер
    $all_product_data['format'] = [str_replace(' ', '', str_replace("РАЗМЕР ", "", $info->find('.size')[0]->text())), 's'];
    
    //артикул
    $articul = $info->find('.number')[0]->text();
    $all_product_data['articul'] = [$articul, 's'];

    //ссылка на товар
    $all_product_data['link'] = [$url_parser . "?art=" . str_replace(" ", "", $articul), 's'];

    //картинки
    $images_res = array($good->first('.gallery a'));
    if ($images_res) {
        $images = Parser::getImages($images_res, $provider) ?? null;
        $all_product_data['images'] = [$images, 's'];
    }

    //категории
    $all_product_data['category'] = [Parser::getCategoriesList()[0], 's'];
    $all_product_data['subcategory'] = [Parser::getSubcategoriesList()[9], 's'];



    $print_result = [];
    foreach ($all_product_data as $key => $val) {
        $print_result[$key] = $val[0];
    }
    TechInfo::preArray($print_result);

    //Для передачи в MySQL

    $types = '';
    $values = array();
    foreach ($all_product_data as $key => $n) {
        $types .= $n[1];
        $values[$key] = $n[0];
    }

    Parser::insertProductData1($types, $values, $all_product_data['link'][0]);
}
