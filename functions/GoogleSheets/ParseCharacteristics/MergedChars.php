<?php

namespace functions\GoogleSheets\ParseCharacteristics;

require_once __DIR__ . '/../../../vendor/autoload.php';

use functions\Parser;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;

class MergedChars
{
    public static $allAttrData;

    static function getMergedCharsArray($GoogleSheets_tablename)
    {
        $data = self::getAllAttrData($GoogleSheets_tablename);

        $charsArray = array();

        foreach ($data as $values) {
            if (!isset($values[2])) {
                $charsArray[$values[0]][] = $values[1];
            }
        }

        // [Артикул] => Array // пример ключ-значение $charsArray
        // (
        //     [0] => Артикул
        //     [1] => Штрихкод
        //     [2] => Код 1
        //     [3] => Код товара
        // )

        return $charsArray;
    }

    static function getAllAttrs($GoogleSheets_tablename)
    {
        $data = self::getAllAttrData($GoogleSheets_tablename);

        $allAttrs = array();

        foreach ($data as $values) {
            $allAttrs[] = $values[1];
        }

        return $allAttrs;
    }

    static function getAllAttrData($GoogleSheets_tablename)
    {
        if (!self::$allAttrData) {
            $data = Sheet::get_data("B3:D", $GoogleSheets_tablename);
            $data = $data['values'];

            self::$allAttrData = $data;
        }

        return self::$allAttrData;
    }
}
