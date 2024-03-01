<?php

namespace functions\GoogleSheets\ParseCharacteristics\Categories;

require_once __DIR__ . '/../../../../vendor/autoload.php';

use functions\Parser;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;

class Santechnika
{
    static function getSubcategories()
    {
        $subcategoriesList = Parser::getSubcategoriesList();

        $subcategories = [
            0 => $subcategoriesList[6], //'Аксессуары для ванной комнаты',
            1 => $subcategoriesList[2], //'Ванны',
            2 => $subcategoriesList[26], //'Другое',
            3 => $subcategoriesList[3], //'Душевые',
            4 => $subcategoriesList[7], //'Комплектующие',
            5 => $subcategoriesList[14], //'Кухонные мойки',
            6 => $subcategoriesList[5], //'Мебель для ванной',
            7 => $subcategoriesList[0], //'Раковины',
            8 => $subcategoriesList[1], //'Унитазы, писсуары и биде',
            9 => $subcategoriesList[4], //'Смесители',
            10 => $subcategoriesList[8], //'Полотенцесушители',
        ];
        return $subcategories;
    }
}
