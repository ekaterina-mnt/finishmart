<?php

namespace functions\GoogleSheets\ParseCharacteristics\Categories;

require_once __DIR__ . '/../../../../vendor/autoload.php';

use functions\Parser;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;

class Plitka
{
    static function getSubcategories()
    {
        $subcategoriesList = Parser::getSubcategoriesList();

        $subcategories = [
            $subcategoriesList[10], //'Керамогранит',
            $subcategoriesList[13], //'Мозаика',
            $subcategoriesList[44], //'Панели',
            $subcategoriesList[56], //'Декоративное покрытие',
            $subcategoriesList[11], //'Керамическая плитка',
            $subcategoriesList[26], //'Другое',
            $subcategoriesList[12], //'Натуральный камень',

        ];
        return $subcategories;
    }
}
