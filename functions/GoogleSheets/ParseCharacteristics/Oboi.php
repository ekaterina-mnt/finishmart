<?php

namespace functions\GoogleSheets\ParseCharacteristics;

require_once __DIR__ . '/../../../vendor/autoload.php';

use functions\Parser;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;

class Oboi
{
    static function getSubcategories()
    {
        $subcategoriesList = Parser::getSubcategoriesList();

        $subcategories = [
            $subcategoriesList[9], //'Декоративные обои',
            $subcategoriesList[52], //'Настенная плитка',
            $subcategoriesList[18], //'Обои под покраску',
            $subcategoriesList[17], //'Фотообои и фрески',
            $subcategoriesList[26], //'Другое',
        ];
        return $subcategories;
    }
}
