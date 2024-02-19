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

    $queries = Sheet::get_data("Товары!AV4:AV", $GoogleSheets_tablename);
    // while (str_contains($query, "'-'")) {
    //     str_replace("'-'", "''", $query);
    // }
    $queries = array_column($queries['values'], 0);

    TechInfo::preArray($queries);
    


    echo "<br>Скрипт закончил - " . date('Y-m-d H:i:s', time());
} catch (Throwable $e) {
    var_dump($e);
}
