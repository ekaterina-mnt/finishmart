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
    $tablename = "final_products";
    // $quaries = 

    // include("add_final_table_columns.php"); // чтобы добавить колонки в mysql 

    // $queries = Sheet::get_data("AW4:AW", $GoogleSheets_tablename);



    // Создать тест insert-запроса
    $letter = "D";
    $str = "=\"INSERT INTO final_products (";
    // $str .= implode(", ", array_slice($insert_attributes, 3));
    foreach ($insert_attributes as $attr) {
        if (in_array($attr, ["id в новой таблице", "id", "Дата изменения"])) continue;
        $str .= "'$attr', ";
        $str_vals .= "'\"&$letter" . "4&\"', ";
        $letter++;
    }
    $str = substr($str, 0, -1);
    $str .= ") VALUES (" . $str_vals . ")";
    echo $str . "<br><br>";

    // Создать текст update-запроса
    $letter = "D";
    $str = "=\"UPDATE final_products SET ";
    foreach ($insert_attributes as $attr) {
        if (in_array($attr, ["id в новой таблице", "id", "Дата изменения"])) continue;
        $str .= "'$attr'" . '=' . "'\"&$letter" . "4&\"', ";
        $letter++;
    }
    $str = substr($str, 0, -2);
    $str .= " WHERE id=\"C4\"\";";
    echo $str . "<br><br>";

    echo "<br>Скрипт закончил - " . date('Y-m-d H:i:s', time());
} catch (Throwable $e) {
    var_dump($e);
}
