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
use functions\GoogleSheets\Sql\SqlQuery;



try {
    echo "Скрипт начал - " . date('Y-m-d H:i:s', time()) . "<br><br>";

    $GoogleSheets_tablename = "napolnye_raw";
    $tablename = "final_products";
    $columns_excel_range = "Товары!C3:AU3";
    $letter = "C"; // для формулы sql запроса

    // include("add_final_table_columns.php"); // чтобы добавить колонки в mysql 
    // SqlQuery::getInsertQuery($columns_excel_range, $letter, $GoogleSheets_tablename); // создать тест insert-запроса

    $columns = Sheet::get_data("Товары!C3:AU3", $GoogleSheets_tablename);
    TechInfo::preArray($values);
    $values = Sheet::get_data("Товары!C4:AU4", $GoogleSheets_tablename);
    TechInfo::preArray($values);
    var_dump($values);
    exit;
    $values = $values['values'];
    TechInfo::preArray($values);
    exit;
    $queries = array_column($queries['values'], 0);
    $i = 1;

    foreach ($queries as $query) {
        $query = str_replace("'-'", "''", $query);

        $query .= ", `date_edit`='" . MySQL::get_mysql_datetime() . "'";
        preg_match("#`all_links_id`='(\d+)'#", $query, $matches);
        $id = $matches[1];
        MySQL::sql($query);
        echo $i++ . ") Добавлен/обновлен товар с all_links_id = $id<br>";
    }




    echo "<br>Скрипт закончил - " . date('Y-m-d H:i:s', time());
} catch (Throwable $e) {
    var_dump($e);
}
