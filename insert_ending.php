<?php

use functions\TechInfo;
use functions\Parser;
use functions\Categories;

$all_product_data['provider_subcategory'] = [$all_product_data['subcategory'][0] ?? null, 's'];
$all_product_data['provider_category'] = [$all_product_data['category'][0] ?? null, 's'];

$all_product_data['subcategory'] = [Categories::finalSubcategory($provider, $all_product_data['provider_category'][0] ?? null, $all_product_data['provider_subcategory'][0] ?? null, $all_product_data['title'][0] ?? null, $all_product_data['link'][0] ?? null), 's', $all_product_data['characteristics'][0] ?? null];
$all_product_data['category'] = [Categories::finalCategory($provider, $all_product_data['provider_category'][0] ?? null, $all_product_data['provider_subcategory'][0] ?? null, $all_product_data['title'][0] ?? null, $all_product_data['link'][0] ?? null), 's', $all_product_data['characteristics'][0] ?? null];

if (!preg_match("#(Подъем и выгрузка)#", $all_product_data['title'][0])) { //иначе пропускаем итерацию, не добавляем товар
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

    if (
        $all_product_data['category'] == 'Свет'
        or $all_product_data['subcategory'] == 'Ковровые покрытия'
        or $all_product_data['subcategory'] == 'Ковры'
        or $all_product_data['provider_subcategory'] == "Фанера пиленная"
    ) {
        Parser::insertProductData1($types, $values, $all_product_data['link'][0], "needless_products");
    } else {
        Parser::insertProductData1($types, $values, $all_product_data['link'][0], "all_products");
    }
} else {
    echo "Название товара: " . $all_product_data['title'][0];
}
