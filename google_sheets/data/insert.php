<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use functions\MySQL;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;
use functions\GoogleSheets\FormInsertData;
use functions\GoogleSheets\ParseCharacteristics\Napolnye;

try {
  echo "Скрипт начал - " . date('Y-m-d H:i:s', time()) . "<br><br>";
  echo "Категория: {$_POST['category']}, подкатегория: {$_POST['subcategory']}<br><br>";

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

  $cells = Sheet::get_data("$list_name!C3:C10000");
  $filled_ids = array_column($cells['values'], 0);
  $last_cell = array_key_last($filled_ids) + 3; // +3, т.к. ячейка C3, а отсчет с нуля
  $filled_ids_str = implode(', ', $filled_ids);

  $current_cell = $last_cell + 1;
  $specific_attributes_cell = "$list_name!S2";

  $char_table_name = [
    "Напольные покрытия" => "napolnye_characteristics",
  ][$needed_category];

  $query = "SELECT name FROM $char_table_name";
  $specific_attributes = array_column(mysqli_fetch_all(MySQL::sql($query), MYSQLI_ASSOC), "name");

  Sheet::update_data($specific_attributes_cell, $specific_attributes);

  $query = "SELECT * FROM all_products WHERE subcategory like '{$needed_subcategory}' AND id NOT IN ($filled_ids_str)";
  $goods = MySQL::sql($query);

  $insert_data = array();
  foreach ($goods as $i => $good) {

    echo "<br>$i<br>";
    $common_values = array();
    foreach ($common_attributes as $attr) {
      $common_values[] = $good[$attr];
    }
    $common_values = array_merge([MySQL::get_mysql_datetime()], array_slice($common_values, 0, 1), ["-"], array_slice($common_values, 1));

    $characteristics = json_decode($good['characteristics'], 1);


    $specific_values = array();
    foreach ($specific_attributes as $attr) {
      $specific_values[] = $good[$attr];
    }

    $values = array_merge($common_values, $specific_values);
    TechInfo::preArray($values);
    $values = array_map(fn ($value) => $value ?? "-", $values);
    $insert_data[] = FormInsertData::get_i($list_name, $values, "B", $current_cell++);
  }
  echo "<br>Всего строк добавлено:" . count($insert_data);
  Sheet::update_few_data($insert_data);

  echo "<br>Скрипт закончил - " . date('Y-m-d H:i:s', time());
} catch (Throwable $e) {
  var_dump($e);
}
