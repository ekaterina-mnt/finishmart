<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use functions\MySQL;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;
use functions\GoogleSheets\FormInsertData;
use functions\GoogleSheets\ParseCharacteristics;

try {
  echo "Скрипт начал - " . date('Y-m-d H:i:s', time()) . "<br><br>";
  $list_name = 'Инженерная доска';

  $common_attributes = [
    'id',
    'articul',
    'title',
    'link',
    'category',
    'subcategory',
    'good_id_from_provider',
    'price',
    'stock',
    'images',
    'status',
    'provider',
    'characteristics',
  ];

  $needed_category = "";
  $needed_subcategory = "Инженерная доска";

  $current_cell = 2;

//   $specific_attributes = [];

  $query = "SELECT " . implode(", ", $common_attributes) . " FROM all_products WHERE subcategory like 'Инженерная доска'";
  $goods = MySQL::sql($query);
  
  $insert_data = array();
  foreach ($goods as $i => $good) {
      
      echo "<br>$i<br>";
    $values = array_values($good);
    $values = array_merge(array_slice($values, 0, 1), ["-"], array_slice($values, 1));

    $characteristics = json_decode($good['characteristics'], 1);
    $s = new ParseCharacteristics($characteristics);
    $specific_attributes = $s->parse($good['provider']);
    
    $values = array_merge($values, $specific_attributes);
    $values = array_map(fn ($value) => $value ?? "-", $values);
    $insert_data[] = FormInsertData::get_i($list_name, $values, "B", $current_cell++);
    
  }
  TechInfo::preArray($insert_data);
    Sheet::update_few_data($insert_data);
    // var_dump($get_data);
    //Sheet::update_few_data($data);
    //echo "Данные успешно обновлены";
  
  echo "<br>Скрипт закончил - " . date('Y-m-d H:i:s', time());
} catch (Throwable $e) {
  var_dump($e);
}