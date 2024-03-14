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

    static function getGoogleSheetsTableName($category, $subcategory = null)
    {
        $list = [
            'Напольные покрытия' => 'napolnye_raw',
            'Плитка и керамогранит' => 'plitka_raw',
            'Обои и настенные покрытия' => 'oboi_raw',
            'Лепнина' => 'lepnina_raw',
            'Сантехника' => [
                1 => 'santechnika_raw_1',
                2 => 'santechnika_raw_2',
                3 => 'santechnika_raw_3',
                4 => 'santechnika_raw_4',
                5 => 'santechnika_raw_5',
                6 => 'santechnika_raw_6',

            ],
            'Краски' => 'kraski_raw',
        ];

        $result = $list[$category];


        if (is_array($result)) {
            switch (array_search($subcategory, Santechnika::getSubcategories())) {
                case 9: // Смесители
                    $result = $result[1];
                    break;
                case 8: // Унитазы, писсуары и биде
                case 10: // Полотенцесушители
                    $result = $result[2];
                    break;
                case 0: // Аксессуары для ванной комнаты
                case 1: // Ванны
                case 2: // Другое
                    $result = $result[3];
                    break;
                case 3: // Душевые
                    $result = $result[4];
                    break;
                case 4: // Комплектующие
                case 5: // Кухонные мойки
                case 7: // Раковины
                    $result = $result[5];
                    break;
                case 6: // Мебель для ванной
                    $result = $result[6];
                    break;
            }
        }

        return $result;
    }
}
