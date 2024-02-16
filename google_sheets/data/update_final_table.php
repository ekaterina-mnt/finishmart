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

 

    // Создать текст update-запроса
    $letter = "D";
    $str = "=\"UPDATE final_products SET ";
    foreach ($insert_attributes as $attr) {
        if (in_array($attr, ["id в новой таблице", "id", "Дата изменения"])) continue;
        $str .= $attr . '=' . "'\"&$letter" . "4&\"', ";
        $letter++;
    }
    $str = substr($str, 0, -2);
    $str .= " WHERE id=\"C4\"\";";


    // Создать тест insert-запроса
    $letter = "=\"INSERT INTO final_products (";
    $ins_val = "(";
    foreach ($insert_attributes as $attr) {
        if (in_array($attr, ["id в новой таблице", "id", "Дата изменения"])) continue;
        $str .= $attr . '=' . "'\"&$letter" . "4&\"', ";
        $ins_val .= "\"&$letter" . "4&\", ";
        $letter++;
    }


    echo "<br>Скрипт закончил - " . date('Y-m-d H:i:s', time());
} catch (Throwable $e) {
    var_dump($e);
}
