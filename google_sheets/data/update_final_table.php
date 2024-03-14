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
use functions\GoogleSheets\MySQLColumns\MySQLColumns;
use Google\Service\AuthorizedBuyersMarketplace\Contact;
use functions\GoogleSheets\ParseCharacteristics\DefineNeededColumns;
use functions\GoogleSheets\ParseCharacteristics\GetFilledIds;
use functions\GoogleSheets\Sql\SqlQuery;
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
    $mysql_tablename = "final_products";

    $list_name = $needed_subcategory;
    $integer_type = ["all_links_id", "Цена", "Цена для клиента"];


    $columns_excel_range = "$list_name!C3:ZA3";


    MySQLColumns::add_columns($columns_excel_range, $GoogleSheets_tablename, $mysql_tablename);



    $columns = Sheet::get_data($columns_excel_range, $GoogleSheets_tablename);
    $columns = $columns['values'][0];
    $values = Sheet::get_data("$list_name!C24999:ZA29000", $GoogleSheets_tablename);
    $values = $values['values'];

    echo "<br>Будет добавлено " . count($values) . " товаров<br>";

    foreach ($values as $values_i) {
        $insert_array = array();
        $types = "";
        foreach ($values_i as $key => $value) {
            if (in_array($value, ["[]", "-"])) $value = null;

            $insert_array[$columns[$key]] = $value;
            if (in_array($columns[$key], $integer_type)) {
                $types .= "i";
            } else {
                $types .= "s";
            }
        }
        $insert_array['all_links_id'] = $insert_array['id'];
        unset($insert_array['id']);

        $query = MySQL::bind_insert_data($types, $insert_array, $mysql_tablename);
    }

    echo "<br>Скрипт закончил - " . date('Y-m-d H:i:s', time());
} catch (Throwable $e) {
    var_dump($e);
}
