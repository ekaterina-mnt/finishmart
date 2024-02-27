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
        ];
        return $subcategories;
    }
}
