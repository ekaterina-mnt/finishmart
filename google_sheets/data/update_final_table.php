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
    $integer_type = ["all_links_id", "Цена", "Цена для клиента"];

    // $letter = "C"; // для формулы sql запроса
    // include("add_final_table_columns.php"); // чтобы добавить колонки в mysql 
    // SqlQuery::getInsertQuery($columns_excel_range, $letter, $GoogleSheets_tablename); // создать тест insert-запроса

    $columns = Sheet::get_data("Товары!C3:AU3", $GoogleSheets_tablename);
    $columns = $columns['values'][0];
    $values = Sheet::get_data("Товары!C4:AU13000", $GoogleSheets_tablename);
    $values = $values['values'];

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
        $query = MySQL::bind_insert_data($types, $insert_array, $tablename);
    }

    echo "<br>Скрипт закончил - " . date('Y-m-d H:i:s', time());
} catch (Throwable $e) {
    var_dump($e);
}
