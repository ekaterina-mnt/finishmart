<?php

namespace functions\GoogleSheets\ParseCharacteristics\Categories;

require_once __DIR__ . '/../../../../vendor/autoload.php';

use functions\Parser;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;

class Kraski
{
    static function getSubcategories()
    {
        $subcategoriesList = Parser::getSubcategoriesList();

        $subcategories = [
            $subcategoriesList[48], // 'Антисептики и пропитки',
            $subcategoriesList[46], // 'Грунтовки',
            $subcategoriesList[56], // 'Декоративное покрытие',
            $subcategoriesList[26], // 'Другое',
            $subcategoriesList[49], // 'Затирки и клей',
            $subcategoriesList[45], // 'Краски, эмали',
            $subcategoriesList[47], // 'Лаки и масла',
            $subcategoriesList[39], // 'Сопутствующие',
            $subcategoriesList[55], // 'Шпатлевки',
            $subcategoriesList[19], // 'Штукатурка',
        ];
        return $subcategories;
    }
}
