<?php

namespace functions\GoogleSheets\ParseCharacteristics\Categories;

require_once __DIR__ . '/../../../../vendor/autoload.php';

use functions\Parser;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;

class Lepnina
{
    static function getSubcategories()
    {
        $subcategoriesList = Parser::getSubcategoriesList();

        $subcategories = [
            $subcategoriesList[58], // 'Багет',
            $subcategoriesList[24], // 'Дверное обрамление',
            $subcategoriesList[51], // 'Декоративные элементы',
            $subcategoriesList[26], // 'Другое',
            $subcategoriesList[50], // 'Камины',
            $subcategoriesList[21], // 'Карнизы',
            $subcategoriesList[54], // 'Колонны',
            $subcategoriesList[53], // 'Кронштейны, ниши',
            $subcategoriesList[22], // 'Молдинги',
            $subcategoriesList[44], // 'Панели',
            $subcategoriesList[60], // 'Пилястры',
            $subcategoriesList[40], // 'Порожки',
            $subcategoriesList[41], // 'Профили',
            $subcategoriesList[20], // 'Розетки',
        ];
        return $subcategories;
    }
}
