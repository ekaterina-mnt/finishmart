<?php

require __DIR__ . "/vendor/autoload.php";

use functions\MySQL;
use functions\Parser;

$url = "https://santehnika.masterdom.ru/rakoviny/catalog/";

$document = Parser::guzzleConnect($url);

$api_data_arr = $document->find('script');

foreach ($api_data_arr as $n) {
    
var_dump($n);
echo "<br><br><br>";

}
exit;
$api_data_arr = rtrim(str_replace("window.__initialData=", "", $api_data_arr), ";");
$api_data_arr = json_decode($api_data_arr, 1);
$api_data_country = $api_data_arr['store']['references']['data']['countries'];

foreach ($api_data_country as $country) {
    if (!isset($country['nested'])) continue;
    $producer = $country['nested']['items'];

    foreach ($producer as $fabric_id => $fabric_name) {
        $all_tile_producers[$fabric_id] = [
            'fabric_id' => $fabric_id,
            'fabric_name' => $fabric_name['name'],
            'country' => $country['name'],
            'collections' => [],
        ];
        $collections = $fabric_name['nested']['items'];
        foreach ($collections as $collection) {
            $all_tile_producers[$fabric_id]['collections'][] = $collection['id'];
            $all_tile_collections[$collection['id']] = [
                'collection_id' => $collection['id'],
                'collection_name' => $collection['name'],
                'fabric_id' => $collection['fabric_id'],
                'country' => $country['name'],
            ];
        }
    }
}

if ($needed == 'producers') {
    return $all_tile_producers;
} elseif ($needed == 'collections') {
    return $all_tile_collections;
}


echo "<pre>";
print_r($all_santech_producers);
echo "</pre>";
