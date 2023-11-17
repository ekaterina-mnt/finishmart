<?php

use functions\TechInfo;
use functions\Parser;
use functions\Logs;
use functions\Categories;

$goods = $document->find('.sub_item');



foreach ($goods as $num => $good) {
    echo "<br><b>Товар " . ++$num . "</b><br><br>";
    $all_product_data = [];

    //цена    
    $price_res = $good->first('.price')->text();
    preg_match("#([0-9 ]+)([^0-9]+)#", $price_res, $carm);
    $all_product_data['price'] = ($carm and !isset($all_product_data['price'])) ? [(int) str_replace(' ', '', trim($carm[1])), 'i'] : [null, 'i'];

    //наличие
    $all_product_data['stock'] = [$good->find('.stock')[0]->text(), 's'];

    $good_a = $good->find('.name a');
    //название
    $all_product_data['title'] = [$good_a[0]->text(), 's'];

    //код
    $all_product_data['good_id_from_provider'] = [($good->find('.kod')[0]->text()), 's'];

    //ссылка на товар
    $all_product_data['link'] = ["https://www.centerkrasok.ru" . $good_a[0]->attr('href'), 's'];


    //категории
    $path_res = $document->find('.c_breadcrumbs li');
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

    //характеристики
    $characteristics_res = [
        $good->first('.name-props li'),
    ];
    if ($characteristics_res) {
        $characteristics = array();

        foreach ($characteristics_res as $num => $value) {
            if ($characteristics_res[$num]) {
                $characteristics_res[$num] = $characteristics_res[$num]->text();

                while (str_contains($characteristics_res[$num], '  ') or str_contains($characteristics_res[$num], "\t")) {
                    $characteristics_res[$num] = trim(str_replace(["  ", "\t", "\n", "\r"], ' ', $characteristics_res[$num]));
                }
                $char_key = "n" . $num + 1;
                $characteristics[$char_key] = $characteristics_res[$num];
            }
        }

        $characteristics = json_encode($characteristics, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $all_product_data['characteristics'] = [$characteristics, 's'];
    }

    //картинки
    $images_res = $document->find(".slider-wrap .slide a");
    if ($images_res) {
        $images = Parser::getImages($images_res, $provider) ?? null;
        $all_product_data['images'] = [$images, 's'];
    }


    // $all_product_data['collection'] = [$collection, 's'];
    // $all_product_data['producer'] = [$fabric, 's'];
    $all_product_data['provider'] = ['centerkrasok', 's'];

    foreach ($all_product_data as $data_key => $data_value) {
        $all_product_data[$data_key][0] = trim($all_product_data[$data_key][0]);

        if (isset($data_value[0])) {
            while (str_contains($all_product_data[$data_key][0], '  ') or str_contains($all_product_data[$data_key][0], "\t") or str_contains($all_product_data[$data_key][0], "\n")) {
                $all_product_data[$data_key][0] = str_replace(["  ", "\t", "\n", "\r"], ' ', $all_product_data[$data_key][0]);
            }
        }
    }

    if (isset($all_product_data['good_id_from_provider'][0])) {
        $all_product_data['good_id_from_provider'][0] = str_replace('Код: ', '', $all_product_data['good_id_from_provider'][0]);
    }
    

    include "insert_ending.php";
}
