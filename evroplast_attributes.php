<?php
use functions\TechInfo;
use functions\Parser;

$apiData = json_decode($document->find('body')[0]->text(),1);

echo "Найдено " . count($apiData) . " товаров:<br><br>";

foreach ($apiData as $num => $item) {
    echo "<br><b>Товар " . ++$num . "</b><br><br>";

    $all_product_data = [];

    $all_product_data['provider'] = [$provider, 's'];
    
    $all_product_data['title'] = [$item['name'], 's'];
    $all_product_data['articul'] = [$item['article'], 's'];
    $all_product_data['good_id_from_provider'] = [$item['id'], 's'];
    $all_product_data['price'] = [(int) $item['price'], 's'];
    $all_product_data['link'] = ["https://evroplast.ru" . $item['link'], 's'];
    $all_product_data['images'] = [json_encode("https://evroplast.ru" . $item['img'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 's'];
    $all_product_data['category'] = [$item['catName'], 's'];
    $all_product_data['characteristics'] = [json_encode($item['params'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 's'];
    $all_product_data['stock'] = $item['availableToSell'] ? ['В наличии', 's'] : [null, 's']; 

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