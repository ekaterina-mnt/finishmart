<?php

namespace functions\GoogleSheets\ParseCharacteristics;

require_once __DIR__ . '/../../../vendor/autoload.php';

use functions\Parser;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;

class Napolnye
{
    public static $allAttrs;

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

    static function getCharsArray()
    {
        $data = self::getAllAttrs();

        TechInfo::preArray($data);
        exit;

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
        if (!self::$allAttrs) {
            $data = Sheet::get_data("B3:D", "napolnye_edition");
            $data = $data['values'];

            self::$allAttrs = $data;
        }

        return self::$allAttrs;
    }
}
