<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use functions\MySQL;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;
use functions\GoogleSheets\FormInsertData;
use functions\GoogleSheets\ParseCharacteristics\Napolnye;
use functions\GoogleSheets\ParseCharacteristics\SpecificChars;
use functions\GoogleSheets\ParseCharacteristics\CommonChars;
use functions\GoogleSheets\Goods\GetGoods;
use Google\Service\AuthorizedBuyersMarketplace\Contact;
use functions\GoogleSheets\ParseCharacteristics\DefineNeededColumns;
use functions\GoogleSheets\ParseCharacteristics\GetFilledIds;



try {
    echo "Скрипт начал - " . date('Y-m-d H:i:s', time()) . "<br><br>";

    $GoogleSheets_tablename = "napolnye_raw";
    $mysql_tablename = "final_products";
    $columns_excel_range = "Товары!C3:AU3";

    $columns_excel = Sheet::get_data($columns_excel_range, $GoogleSheets_tablename);
    $columns_excel = $columns_excel['values'][0];

    $query = "SELECT COLUMN_NAME as 'columns' 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'penzevrv_2109' AND TABLE_NAME = '$mysql_tablename'";

    $res = MySQL::sql($query);
    $columns_mysql = array_column(mysqli_fetch_all($res, MYSQLI_ASSOC), "columns");


    $insert_columns = array();

    foreach ($columns_excel as $column) {
        if (!in_array($column, $columns_mysql)) $insert_columns[] = $column;
    }

    foreach (array_intersect($columns_excel, $columns_mysql) as $column) {
        echo "Уже есть колонка $column<br>";
    }

    $query = "ALTER TABLE $mysql_tablename";
    foreach ($insert_columns as $column) {
        $query .= " ADD COLUMN `$column` TEXT(1500) DEFAULT NULL,";
        MySQL::sql($query);
        echo "Добавлена колонка $column<br>";
    }


    echo "<br>Скрипт закончил - " . date('Y-m-d H:i:s', time());
} catch (Throwable $e) {
    var_dump($e);
}
