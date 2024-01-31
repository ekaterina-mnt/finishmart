<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use functions\MySQL;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;
use functions\GoogleSheets\FormInsertData;
use functions\GoogleSheets\ParseCharacteristics\Napolnye;

try {
  echo "Скрипт начал - " . date('Y-m-d H:i:s', time()) . "<br><br>";

  if (!isset($_POST['category']) or !isset($_POST['subcategory'])) exit("Нужны параметры 'категория' и 'подкатегория'");
  $napolnye = Napolnye::getSubcategoriesNapolnye();
  if (!in_array($_POST['subcategory'], $napolnye)) exit("Неподходящий параметр");

  $needed_category = $_POST['category'];
  $needed_subcategory = $_POST['subcategory'];
  $list_name = $needed_subcategory;

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
    'description',
    'in_pack'
  ];

  $cells = Sheet::get_data("C2:C10000");
  var_dump($cells['values']);
  exit;

  $current_cell = 3;
  $specific_attributes_cell = "$list_name!S2";

  $char_table_name = [
    "Напольные покрытия" => "napolnye_characteristics",
  ][$needed_category];

  $query = "SELECT name FROM $char_table_name";
  $specific_attributes = array_column(mysqli_fetch_all(MySQL::sql($query), MYSQLI_ASSOC), "name");

  Sheet::update_data($specific_attributes_cell, $specific_attributes);

  $query = "SELECT * FROM all_products WHERE subcategory like '{$needed_subcategory}'";
  $goods = MySQL::sql($query);

  $insert_data = array();
  foreach ($goods as $i => $good) {

    echo "<br>$i<br>";
    $values = array();
    foreach ($common_attributes as $attr) {
      $values[] = $good[$attr];
    }
    var_dump($values);
    $values = array_merge([MySQL::get_mysql_datetime()], array_slice($values, 0, 1), ["-"], array_slice($values, 1));

    $characteristics = json_decode($good['characteristics'], 1);


    $specific_values = array();
    foreach ($specific_attributes as $attr) {
      $specific_values = $good[$attr];
    }

    $values = array_merge($values, $specific_values);
    $values = array_map(fn ($value) => $value ?? "-", $values);
    $insert_data[] = FormInsertData::get_i($list_name, $values, "B", $current_cell++);
  }
  TechInfo::preArray($insert_data);
  Sheet::update_few_data($insert_data);

  echo "<br>Скрипт закончил - " . date('Y-m-d H:i:s', time());
} catch (Throwable $e) {
  var_dump($e);
}
