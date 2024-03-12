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
use functions\GoogleSheets\ParseCharacteristics\ConnectedSubcategories;



try {
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
        $query .= " ADD COLUMN `$column` TEXT(1500) DEFAULT NULL, ";
        echo "Добавится колонка $column<br>";
    }
    $query = substr($query, 0, -2);
    // MySQL::sql($query);
    echo "Все добавлено<br>";
} catch (Throwable $e) {
    var_dump($e);
}
