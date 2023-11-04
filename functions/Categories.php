<?php

namespace functions;

require __DIR__ . "/../vendor/autoload.php";

use DiDom\Document;
use DOMDocument;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;

class Categories
{

    static function getSubcategoryByCharacteristics($char_value)
    {
        // $all_subcategories = Parser::getSubcategoriesList();

        // $keys = [
        //     'laparet' => [
        //         $all_subcategories[27] => str_contains(mb_strtolower($char_value), "унитаз"),
        //     ],
        // ];
        // $subcategories = Parser::getSubcategoriesList();

        // $subcategories_keys = [
        //     0 => 'Раковины',
        //     1 => ['Унитазы', 'Инсталляции', 'Писсуары', 'Биде', 'Кнопки смыва'],
        //     2 => 'Ванны',
        //     3 => ['Душевые', 'Поддоны, трапы, лотки'],
        //     4 => 'Смесители',
        //     5 => 'Мебель для ванной',
        //     6 => ['Аксессуары для ванной комнаты', 'Аксессуары для ванной'],
        //     7 => 'Комплектующие',
        //     8 => 'Полотенцесушители',
        //     9 => 'Декоративные обои',
        //     10 => ['Керамогранит', 'керамогранит'],
        //     11 => ['Керамическая плитка', 'керамическая плитка'],
        //     12 => 'Натуральный камень',
        //     13 => ['Мозаика', 'мозаика'],
        //     14 => 'Кухонные мойки',
        //     15 => 'клинкер',
        //     16 => 'SPC-плитка',
        // ];

        // foreach ($subcategories_keys as $key => $value) {
        //     if (is_array($value)) {
        //         foreach ($value as $value_i) {
        //             if (str_contains($subcategory, $value_i)) {
        //                 return $subcategories[$key];
        //             };
        //         }
        //     } elseif (str_contains($subcategory, $value)) {
        //         return $subcategories[$key];
        //     };
        // }

        return $char_value;
    }


    static function getCategoriesByPath(array $path, $provider)
    {
        $all_categories = Parser::getCategoriesList($provider);
        $all_subcategories = Parser::getSubcategoriesList($provider);

        $keys = [
            'ntceramic' => [
                'сантехника' => [
                    'category' => $all_categories[3],
                    'subcategory' => null, //в характеристиках - "тип"
                ],
                'керамогранит' => [
                    'category' => $all_categories[2],
                    'subcategory' => $all_subcategories[10],
                ],
                'мебель' => [
                    'category' => $all_categories[3],
                    'subcategory' => $all_subcategories[5],
                ],
            ],
            'laparet' => [
                'сантехника' => [
                    'category' => $all_categories[3],
                    'subcategory' => null, //в характеристиках - "категория"
                ],
                'керамогранит' => [
                    'category' => $all_categories[2],
                    'subcategory' => $all_subcategories[10],
                ],
                'керамическая плитка' => [
                    'category' => $all_categories[2],
                    'subcategory' => $all_subcategories[11],
                ],
            ],
            'laparet' => [
                'сантехника' => [
                    'category' => $all_categories[3],
                    'subcategory' => null, //в характеристиках - "категория"
                ],
                'керамогранит' => [
                    'category' => $all_categories[2],
                    'subcategory' => $all_subcategories[10],
                ],
                'керамическая плитка' => [
                    'category' => $all_categories[2],
                    'subcategory' => $all_subcategories[11],
                ],
            ],
            'domix' => [],
        ];

        $result = [
            'category' => null,
            'subcategory' => null,
        ];

        foreach ($path as $path_key => $path_value) {
            $path_value = $path_value->text();
            foreach ($keys[$provider] as $category_key => $category_value) {
                if (str_contains(trim(mb_strtolower($path_value)), $category_key)) {
                    $category_value['category_key'] = $path_key;
                    return $category_value;
                }
            }

            //если категории не прописаны в моем массиве $keys - прописываются категории поставщика

            if (!str_contains(trim(mb_strtolower($path_value)), 'каталог') and !str_contains(trim(mb_strtolower($path_value)), 'главная')) {
                if (!isset($result['category'])) {
                    $result['category'] = $path_value;
                } elseif (isset($result['category']) and !isset($result['subcategory'])) {
                    $result['subcategory'] = $path_value;
                }
            };
        }

        return $result;
    }

    static function getSubcategoryByPath(array $path, $provider, $category_key)
    {
        $all_subcategories = Parser::getSubcategoriesList($provider);
        if ($path[$category_key + 2]) {
            return $path[$category_key + 2]->text(); //плюс два, потому что $document->find() добавляет по две копии почему-то
        }
        return null;
    }


    static function getCategoriesByTitle($title, $provider)
    {
        $all_categories = Parser::getCategoriesList();
        $all_subcategories = Parser::getSubcategoriesList();

        if (!$title) {
            return [
                'category' => null,
                'subcategory' => null,
            ];
        }

        $title = mb_strtolower($title);

        $keys = [
            'olimpparket' => [
                $all_subcategories[27] => str_contains($title, "ламинат"),
                $all_subcategories[28] => str_contains($title, "инженерная доска"),
                $all_subcategories[29] => str_contains($title, "паркетная доска"),
                $all_subcategories[30] => str_contains($title, "штучный паркет"),
                $all_subcategories[31] => str_contains($title, "виниловый пол"),
                $all_subcategories[32] => str_contains($title, "подложка"),
                $all_subcategories[33] => str_contains($title, "плинтус"),
                $all_subcategories[34] => str_contains($title, "массивная доска"),
                $all_subcategories[35] => str_contains($title, "пробковое покрытие"),
                $all_subcategories[36] => str_contains($title, "линолиум"),
            ],
        ];

        foreach ($keys[$provider] as $key => $stmnt) {
            if ($stmnt) {
                return [
                    'category' => $all_categories[1],
                    'subcategory' => $key,
                ];
            }
        }
        return null;

        $keys = [
            'olimpparket' => [
                'сантехника' => [
                    'category' => $all_categories[3],
                    'subcategory' => null, //в характеристиках - "тип"
                ],
                'керамогранит' => [
                    'category' => $all_categories[2],
                    'subcategory' => $all_subcategories[10],
                ],
                'мебель' => [
                    'category' => $all_categories[3],
                    'subcategory' => $all_subcategories[5],
                ],
            ],
        ];

        return [
            'category' => null,
            'subcategory' => null,
        ];
    }

    static function getPath(array $path_res)
    {
        $path = array();
        foreach ($path_res as $value) {
            if (!str_contains(mb_strtolower($value->text()), 'каталог') && !str_contains(mb_strtolower($value->text()), 'главная')) {
                $path[] = $value;
            }
        }

        return $path;
    }
}
