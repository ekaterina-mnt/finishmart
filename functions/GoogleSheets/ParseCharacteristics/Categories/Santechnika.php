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
            // $subcategoriesList[58], // 'Багет',
            $subcategoriesList[6], //'Аксессуары для ванной комнаты',
            $subcategoriesList[2], //'Ванны',
            $subcategoriesList[26], //'Другое',
            $subcategoriesList[3], //'Душевые',
            $subcategoriesList[7], //'Комплектующие',
            $subcategoriesList[14], //'Кухонные мойки',
            $subcategoriesList[5], //'Мебель для ванной',
            $subcategoriesList[0], //'Раковины',
            $subcategoriesList[1], //'Унитазы, писсуары и биде',
            $subcategoriesList[4], //'Смесители',
            $subcategoriesList[8], //'Полотенцесушители',
        ];
        return $subcategories;
    }
}
