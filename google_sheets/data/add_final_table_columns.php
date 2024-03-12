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
    echo "Скрипт начал - " . date('Y-m-d H:i:s', time()) . "<br><br>";

    $needed_category = ($_POST['category'] ?? $_GET['category']) ?? $argv[1];
    $needed_subcategory = ($_POST['subcategory'] ?? $_GET['subcategory']) ?? $argv[2];

    /////// ОПРЕДЕЛЯЕМ НУЖНЫЕ ПЕРЕМЕННЫЕ ///////

    echo "Категория: {$_POST['category']}, подкатегория: {$_POST['subcategory']}<br><br>";
    $subcategories = ConnectedSubcategories::getList();
    if (!isset($needed_category) or !isset($needed_subcategory)) exit("Нужны параметры 'категория' и 'подкатегория'");
    if (!in_array($needed_category, array_keys($subcategories))) exit("Неподходящий параметр");
    if (!in_array($needed_subcategory, $subcategories[$needed_category])) exit("Неподходящий параметр");

    // $list_name = "Товары";
    $list_name = $needed_subcategory;


    // Первая ячейка, с которой начинается инзерт в Гугл Таблицу
    $current_cell = 4;
    $start_column = "A";
    $additional_columns = ['id в новой таблице', 'Дата изменения'];

    // В какую таблицу будет инзерт   
    $GoogleSheets_tablename = ConnectedSubcategories::getGoogleSheetsTableName($needed_category, $needed_subcategory);
    $tablename = "final_products";

    $integer_type = ["all_links_id", "Цена", "Цена для клиента"];

    require "update_final_table.php";

    exit;


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
        $query .= " ADD COLUMN `$column` TEXT(1500) DEFAULT NULL, ";
        echo "Добавится колонка $column<br>";
    }
    $query = substr($query, 0, -2);
    MySQL::sql($query);
    echo "Все добавлено<br>";


    echo "<br>Скрипт закончил - " . date('Y-m-d H:i:s', time());
} catch (Throwable $e) {
    var_dump($e);
}
