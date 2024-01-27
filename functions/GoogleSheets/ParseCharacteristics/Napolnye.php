<?php

namespace functions\GoogleSheets\ParseCharacteristics;

require_once __DIR__ . '/../../../vendor/autoload.php';

use functions\Parser;

class Napolnye
{
    public static $characteristics;

    public function __construct($characteristics)
    {
        self::$characteristics = $characteristics;
    }

    static function getSubcategoriesNapolnye()
    {
        $subcategoriesList = Parser::getSubcategoriesList();

        $subcategories = [
            $subcategoriesList[28], //'Инженерная доска',
            $subcategoriesList[29], //'Паркетная доска',
            $subcategoriesList[32], //'Подложка под напольные покрытия',
            $subcategoriesList[33], //'Плинтус',
            $subcategoriesList[34], //'Массивная доска',
            $subcategoriesList[35], //'Пробковое покрытие',
            $subcategoriesList[36], //'Линолеум',
            $subcategoriesList[37], //'Кварцвиниловые полы',
            $subcategoriesList[27], //'Ламинат',
        ];
        return $subcategories;
    }


    public function parse($provider, $subcategory)
    {
        $chars = self::$characteristics;
        $subcategoriesList = Parser::getSubcategoriesList();


        $char_synonyms = [
            $subcategoriesList[28] => [ //'Инженерная доска',
                "brand" => [
                    "domix" => $chars["Бренд"],
                    "olimp" => $chars["Бренд"],
                    "alpenfloor" => "alpinefloor",
                ],
                "collection" => [
                    "domix" => $chars["Наименование дизайна"],
                    "olimp" => $chars["Коллекция"],
                    "alpinefloor" => "нужно допарсить",
                ],
                "color" => [
                    "domix" => $chars["Цвет"],
                    "olimp" => $chars["Тон"],
                    "alpinefloor" => $chars["Фактура"],

                ],
                "chamfer" => [ // фаска
                    "domix" => $chars["Вид фаски"],
                    "olimp" => $chars["Фаска"],
                    "alpinefloor" => $chars["Вид фаски"],

                ],
                "finish_cover" => [
                    "domix" => $chars["Финишное покрытие"],
                    "olimp" => $chars["Покрытие"] ?? "-",
                    "alpinefloor" => $chars["Защитный слой"],
                ],
                "connection_type" => [
                    "domix" => $chars["Тип соединения"],
                    "olimp" => $chars["Тип соединения"],
                    "alpinefloor" => $chars["Тип замка"],
                ],
                "construction" => [
                    "domix" => $chars["Конструкция"],
                    "olimp" => $chars["Конструкция"],
                    "alpinefloor" => $chars["Основа"],
                ],
                "thickness" => [
                    "domix" => $chars["Общая толщина, мм"],
                    "olimp" => $chars["Толщина"],
                    "alpinefloor" => $chars["Толщина, мм"],
                ],
                "length" => [
                    "domix" => $chars["Длина, мм"],
                    "olimp" => $chars["Длина"],
                    "alpinefloor" => $chars["Длина 1 шт, мм"],
                ],
                "width" => [
                    "domix" => $chars["Ширина, мм"],
                    "olimp" => $chars["Ширина"],
                    "alpinefloor" => $chars["Ширина, мм"],
                ],
                "weight" => [
                    "domix" => $chars["Вес упаковки, кг"],
                    "olimp" => "-",
                    "alpinefloor" => $chars["Вес упаковки, кг"],
                ],
                "in_one_pack" => [
                    "domix" => $chars["В одной упаковке, м²"],
                    "olimp" => "нужно допарсить",
                    "alpinefloor" => $chars["Площадь упаковки, кв. м."],
                ],
                "selection" => [
                    "domix" => $chars["Сортировка"],
                    "olimp" => $chars["Селекция"],
                    "alpinefloor" => $chars["Селекция"],
                ],
                "country" => [
                    "domix" => $chars["Страна производства"],
                    "olimp" => "-",
                    "alpinefloor" => "Россия",
                ],
                "shine" => [
                    "domix" => $chars["Блеск"],
                    "olimp" => "-",
                    "alpinefloor" => "-",
                ],
                "stripes" => [
                    "domix" => $chars["Полосность (вид дизайна)"],
                    "olimp" => $chars["Количество полос"],
                    "alpinefloor" => "-",
                ],
            ],
            $subcategoriesList[29] => [ //'Паркетная доска',
            ],
            $subcategoriesList[32] => [ //'Подложка под напольные покрытия',
            ],
            $subcategoriesList[33] => [ //'Плинтус',
            ],
            $subcategoriesList[34] => [ //'Массивная доска',
            ],
            $subcategoriesList[35] => [ //'Пробковое покрытие',
            ],
            $subcategoriesList[36] => [ //'Линолеум',
            ],
            $subcategoriesList[37] => [ //'Кварцвиниловые полы',
            ],
            $subcategoriesList[27] => [ //'Ламинат',
            ],
        ];

        $needed_char_values = array();
        foreach ($char_synonyms[$subcategory] as $char_i) {
            $needed_char_values[] = $char_i[$provider];
        }

        return $needed_char_values;
    }
}
