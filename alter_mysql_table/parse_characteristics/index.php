<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use functions\MySQL;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;
use functions\GoogleSheets\FormInsertData;
use functions\GoogleSheets\ParseCharacteristics\Napolnye;

try {
    echo "Скрипт начал - " . date('Y-m-d H:i:s', time()) . "<br><br>";

    $napolnye = Napolnye::getSubcategoriesNapolnye();

    $needed_category = "Напольные покрытия";

    foreach ($napolnye as $i => $sub) {
        $napolnye[$i] = "'{$sub}'";
    }
    $subcategoriesList = implode(", ", $napolnye);

    $query = "SELECT count(id) FROM all_products WHERE subcategory in ($subcategoriesList) AND category like '{$needed_category}'";
    var_dump(mysqli_fetch_assoc(MySQL::sql($query)));

    $query = "SELECT id, characteristics, char_views FROM all_products WHERE subcategory in ($subcategoriesList) AND category like '{$needed_category}' ORDER BY char_views LIMIT 100";
    $goods = MySQL::sql($query);

    foreach ($goods as $good) {
        $query = "SELECT COLUMN_NAME as 'columns' 
                  FROM INFORMATION_SCHEMA.COLUMNS 
                  WHERE TABLE_SCHEMA = 'penzevrv_2109' AND TABLE_NAME = 'all_products'";

        $res = MySQL::sql($query);
        $columns = array_column(mysqli_fetch_all($res, MYSQLI_ASSOC), "columns");

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

        // ДОБАВЛЕНИЕ КОЛОНОК
        if (count($add_columns)) {
            $query = "ALTER TABLE all_products";
            foreach ($add_columns as $column) {
                $query .= " ADD COLUMN `$column` TEXT(1500) DEFAULT NULL,";
            }
            $query = substr($query, 0, -1);
            var_dump($query);
            MySQL::sql($query);

            $values = $add_columns;
            $types = str_repeat("s", count($values));
            MySQL::multiple_insert("name", $types, $values, "napolnye_characteristics");
        }

        // ДОБАВЛЕНИЕ САМИХ ХАРАКТЕРИСТИК
        $types = str_repeat("s", count($chars));
        // обновляем char_views
        $types .= "i";
        $chars['char_views'] = $good['char_views'] + 1;

        $query = MySQL::update($types, $chars, "all_products", $good['id'], false);
    }


    echo "<br>Скрипт закончил - " . date('Y-m-d H:i:s', time());
} catch (Throwable $e) {
    var_dump($e);
}
