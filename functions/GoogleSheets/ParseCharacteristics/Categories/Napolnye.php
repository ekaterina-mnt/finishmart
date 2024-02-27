<?php

namespace functions\GoogleSheets\ParseCharacteristics\Categories;

require_once __DIR__ . '/../../../../vendor/autoload.php';

use functions\Parser;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;

class Napolnye
{
    static function getSubcategories()
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
}
