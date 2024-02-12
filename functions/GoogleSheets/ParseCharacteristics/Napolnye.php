<?php

namespace functions\GoogleSheets\ParseCharacteristics;

require_once __DIR__ . '/../../../vendor/autoload.php';

use functions\Parser;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;

class Napolnye
{
    public static $characteristics;
    public static $good;

    public function __construct($characteristics, $good)
    {
        self::$characteristics = $characteristics;
        self::$good = $good;
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
            $subcategoriesList[30], //'Штучный паркет',
        ];
        return $subcategories;
    }

    static function getCharsArray()
    {
        $data = Sheet::get_data("B3:D", "napolnye_edition");
        TechInfo::preArray($data);
        exit;
    }


    public function parse($provider, $subcategory)
    {
        $chars = self::$characteristics;
        $good = self::$good;
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
                "brand" => [
                    "domix" => $chars["Бренд"],
                    "olimp" => $chars["Бренд"],
                    "fargo" => $chars["Бренд"],
                    "alpinefloor" => "Alpinefloor",
                ],
                "collection" => [
                    "domix" => $chars["Коллекция"],
                    "olimp" => $chars["Коллекция"],
                    "fargo" => $chars["Коллекция"],
                    "alpinefloor" => "Достать из хлебных крошек",
                ],
                "country" => [
                    "domix" => $chars["Страна производства"],
                    "olimp" => $chars["Страна производства"],
                    "fargo" => $chars["Страна производитель"],
                    "alpinefloor" => "Россия",
                ],
                "class" => [
                    "domix" => $chars["Класс"],
                    "olimp" => $chars["Класс"],
                    "fargo" => $chars["Класс износостойкости"],
                    "alpinefloor" => $chars["Класс износостойкости"],
                ],
                "color" => [
                    "domix" => $chars["Цвет"],
                    "olimp" => $chars["Тон"],
                    "fargo" => "-",
                    "alpinefloor" => "-",
                ],
                "chamfer" => [ // фаска
                    "domix" => $chars["Вид фаски"],
                    "olimp" => $chars["Фаска"],
                    "fargo" => $chars["Фаска"],
                    "alpinefloor" => $chars["Фаска"],
                ],
                "design" => [
                    "domix" => $chars["Рисунок"],
                    "olimp" => $chars["Порода дерева"],
                    "fargo" => $chars["Дизайн"],
                    "alpinefloor" => $chars["Фактура"],
                ],
                "connection" => [
                    "domix" => $chars["Тип соединения"],
                    "olimp" => $chars["Тип соединения"],
                    "fargo" => $chars["Тип соединения"],
                    "alpinefloor" => $chars["Тип соединения"],
                ],
                "length" => [
                    "domix" => $chars["Длина, мм"],
                    "olimp" => $chars["Длина"],
                    "fargo" => (int) explode("*", $chars["Размер плашки"])[0],
                    "alpinefloor" => $chars["Длина 1 шт, мм"],
                ],
                "width" => [
                    "domix" => $chars["Ширина, мм"],
                    "olimp" => $chars["Ширина"],
                    "fargo" => (int) explode("*", $chars["Размер плашки"])[1],
                    "alpinefloor" => $chars["Ширина, мм"],
                ],
                "thickness" => [
                    "domix" => $chars["Общая толщина, мм"],
                    "olimp" => $chars["Толщина"],
                    "fargo" => $chars["Общая толщина"],
                    "alpinefloor" => $chars["Толщина, мм"],
                ],
                "weight" => [
                    "domix" => $chars["Вес упаковки, кг"],
                    "olimp" => "-",
                    "fargo" => $chars["Вес"],
                    "alpinefloor" => $chars["Вес упаковки, кг"],
                ],
                "in_pack" => [
                    "domix" => $chars["В одной упаковке, м²"],
                    "olimp" => $good["in_pack"],
                    "fargo" => $chars["Площадь упаковки"],
                    "alpinefloor" => $chars["Площадь упаковки, кв. м."],
                ],
            ],
        ];

        $needed_char_values = array();
        foreach ($char_synonyms[$subcategory] as $char_i) {
            $needed_char_values[] = $char_i[$provider];
        }

        return $needed_char_values;
    }
}
