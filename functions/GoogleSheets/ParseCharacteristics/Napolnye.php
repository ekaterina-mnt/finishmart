<?php

namespace functions\GoogleSheets\ParseCharacteristics;

require_once __DIR__ . '/../../../vendor/autoload.php';

use functions\Parser;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;

class Napolnye
{
    public static $allAttrData;

    static function getSubcategoriesNapolnye()
    {
        $subcategoriesList = Parser::getSubcategoriesList();

        $subcategories = [
            $subcategoriesList[28], //'Инженерная доска',
            $subcategoriesList[29], //'Паркетная доска',
            $subcategoriesList[32], //'Подложка под напольные покрытия',
            $subcategoriesList[33], //'Плинтус',
            $subcategoriesList[34], //'Массивная доска',
            $subcategoriesList[35], //'Пробковое покрытие',
            $subcategoriesList[36], //'Линолеум',
            $subcategoriesList[37], //'Кварцвиниловые полы',
            $subcategoriesList[27], //'Ламинат',
            $subcategoriesList[30], //'Штучный паркет',
        ];
        return $subcategories;
    }

    static function getMergedCharsArray()
    {
        $data = self::getAllAttrData();

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

    static function getAllAttrs()
    {
        $data = self::getAllAttrData();

        $allAttrs = array();

        foreach ($data as $values) {
            $allAttrs[] = $values[1];
        }

        return $allAttrs;
    }

    static function getAllAttrData()
    {
        if (!self::$allAttrData) {
            $data = Sheet::get_data("B3:D", "napolnye_edition");
            $data = $data['values'];

            self::$allAttrData = $data;
        }

        return self::$allAttrData;
    }
}
