<?php

namespace functions\GoogleSheets\ParseCharacteristics;

require_once __DIR__ . '/../../../vendor/autoload.php';

use DiDom\Document;
use DOMDocument;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;
use functions\GoogleSheets\ParseCharacteristics\Categories\Napolnye;
use functions\GoogleSheets\ParseCharacteristics\Categories\Plitka;
use functions\GoogleSheets\ParseCharacteristics\Categories\Lepnina;
use functions\GoogleSheets\ParseCharacteristics\Categories\Santechnika;
use functions\GoogleSheets\ParseCharacteristics\Categories\Kraski;
use functions\GoogleSheets\ParseCharacteristics\Categories\Oboi;

class ConnectedSubcategories
{
    static function getList()
    {
        $subcategories = [
            'Напольные покрытия' => Napolnye::getSubcategories(),
            'Плитка и керамогранит' => Plitka::getSubcategories(),
            'Обои и настенные покрытия' => Oboi::getSubcategories(),
            'Лепнина' => Lepnina::getSubcategories(),
            'Сантехника' => Santechnika::getSubcategories(),
            'Краски' => Kraski::getSubcategories(),
        ];

        return $subcategories;
    }

    static function getNeededSubcategories($needed_category)
    {
        $subcategories = self::getList();
        return $subcategories[$needed_category];
    }
}
