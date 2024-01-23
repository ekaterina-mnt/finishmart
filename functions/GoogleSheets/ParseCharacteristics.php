<?php

namespace functions\GoogleSheets;

require_once __DIR__ . '/../../vendor/autoload.php';


class ParseCharacteristics
{
    public static $characteristics;

    public function __construct($characteristics)
    {
        self::$characteristics = $characteristics;
    }


    public function parse($provider)
    {
        $chars = self::$characteristics;

        $char_synonyms = [
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
                "olimp" => $chars["Покрытие"] ?? null,
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
            ]
        ];

        $needed_char_values = array();
        foreach ($char_synonyms as $char_i) {
            $needed_char_values[] = $char_i[$provider];
        }

        return $needed_char_values;
    }
}
