<?php

namespace functions\GoogleSheets\ParseCharacteristics;

require_once __DIR__ . '/../../../vendor/autoload.php';

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
            26 => 'Другое',
            20 => 'Розетки',
            21 => 'Карнизы',
            22 => 'Молдинги',
            54 => 'Колонны',
            7 => 'Комплектующие',
            40 => 'Порожки',
            41 => 'Профили',
            44 => 'Панели',
        ];
        return $subcategories;
    }
}
