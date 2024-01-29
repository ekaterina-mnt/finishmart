<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use functions\MySQL;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;
use functions\GoogleSheets\FormInsertData;
use functions\GoogleSheets\ParseCharacteristics\Napolnye;

try {
    echo "Скрипт начал - " . date('Y-m-d H:i:s', time()) . "<br><br>";

    //   if (!isset($_POST['category']) or !isset($_POST['subcategory'])) exit("Нужны параметры 'категория' и 'подкатегория'");
    $napolnye = Napolnye::getSubcategoriesNapolnye();
    //   if (!in_array($_POST['subcategory'], $napolnye)) exit("Неподходящий параметр");

    //   $needed_category = $_POST['category'];
    $needed_category = "Напольные покрытия";
    //   $needed_subcategory = $_POST['subcategory'];
    //   $list_name = $needed_subcategory;

    foreach ($napolnye as $i => $sub) {
        $napolnye[$i] = "'{$sub}'";
    }

    $subcategoriesList = implode(", ", $napolnye);
    $query = "SELECT characteristics FROM all_products WHERE subcategory in ($subcategoriesList) AND category like '{$needed_category}' ORDER BY char_views LIMIT 1";
    $goods = MySQL::sql($query);

    foreach ($goods as $good) {
        $query = "SELECT group_concat(COLUMN_NAME) as 'columns'
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = 'penzevrv_2109' AND TABLE_NAME = 'all_products'";

        $res = MySQL::sql($query);
        $columns = mysqli_fetch_assoc($res)['columns'];
        $columns = explode(",", $columns);

        $chars = json_decode($good['characteristics'], true);

        $add_columns = array();
        foreach ($chars as $char => $value) {
            if (in_array($char, $columns)) {
                echo "есть в mysql<br>";
            } else {
                echo "нет в mysql<br>";
                $add_columns[] = $char;
            }
        }

        $query = "ALTER TABLE all_products";
        foreach ($add_columns as $column) {
            $query .= "ADD COLUMN `$column` TEXT(1500) DEFAULT NULL,";
        }
        $query = substr($query, 0, -1);
        var_dump($query);
        MySQL::sql($query);

        $types = str_repeat("s", count($chars));
        $values = array_values($chars);

        $query = MySQL::update($types, $values, "all_products", $good['id'], true);


    }


    echo "<br>Скрипт закончил - " . date('Y-m-d H:i:s', time());
} catch (Throwable $e) {
    var_dump($e);
}
