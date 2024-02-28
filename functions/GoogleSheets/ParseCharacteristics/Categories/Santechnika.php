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
            6 => 'Аксессуары для ванной комнаты',
            2 => 'Ванны',
            26 => 'Другое',
            3 => 'Душевые',
            7 => 'Комплектующие',
            14 => 'Кухонные мойки',
            5 => 'Мебель для ванной',
            0 => 'Раковины',
            1 => 'Унитазы, писсуары и биде',
            4 => 'Смесители',
            8 => 'Полотенцесушители',
        ];
        return $subcategories;
    }
}
