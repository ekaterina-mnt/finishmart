<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use functions\MySQL;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;
use functions\GoogleSheets\FormInsertData;
use functions\GoogleSheets\ParseCharacteristics\Napolnye;

try {
  echo "Скрипт начал - " . date('Y-m-d H:i:s', time()) . "<br><br>";

  var_dump($_POST);
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

  $current_cell = 3;

  $char_table_name = [
    "Напольные покрытия" => "napolnye_characteristics",
  ][$needed_category];

  $query = "SELECT name FROM $char_table_name";
  $specific_attributes = mysqli_fetch_all(MySQL::sql($query));
  $res_4 = array_column(mysqli_fetch_all(MySQL::sql($query), MYSQLI_ASSOC), "name");
  var_dump($specific_attributes);
  echo "<br>";
  var_dump($res_4);
  exit;

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
    $s = new Napolnye($characteristics, $good);
    $specific_attributes = $s->parse($good['provider'], $needed_subcategory);

    $values = array_merge($values, $specific_attributes);
    $values = array_map(fn ($value) => $value ?? "-", $values);
    $insert_data[] = FormInsertData::get_i($list_name, $values, "B", $current_cell++);
  }
  TechInfo::preArray($insert_data);
  Sheet::update_few_data($insert_data);

  echo "<br>Скрипт закончил - " . date('Y-m-d H:i:s', time());
} catch (Throwable $e) {
  var_dump($e);
}
