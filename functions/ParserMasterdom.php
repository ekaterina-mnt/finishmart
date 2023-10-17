<?php

namespace functions;

require __DIR__ . "/../vendor/autoload.php";

use DiDom\Document;
use DOMDocument;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;

class ParserMasterdom
{
    static function getCategory(string $url_parser): string
    {
        $all_categories = [
            0 => 'Обои и настенные покрытия',
            1 => 'Напольные покрытия',
            2 => 'Плитка и керамогранит',
            3 => 'Сантехника',
            4 => 'Краски',
            5 => 'Лепнина',
        ];

        $category_links = [
            0 => 'oboi.masterdom.ru/find',
            1 => null,
            2 => 'api.masterdom.ru/api/rest/tile',
            3 => ['api.masterdom.ru/api/rest/bathrooms/', 'santehnika.masterdom.ru'],
            4 => null,
            5 => null,
        ];

        foreach ($category_links as $key_cat => $value_cat) {
            if (!$value_cat) continue;
            if (is_array($value_cat)) {
                foreach ($value_cat as $value_cat_i) {
                    if (str_contains($url_parser, $value_cat_i)) {
                        return $all_categories[$key_cat];
                    };
                }
            }
            if (str_contains($url_parser, $value_cat)) {
                return $all_categories[$key_cat];
            };
        }
    }

    static function getSubcategory(string $product_link, array $datum): string|null
    {
        $subcategory_keys = [
            0 => str_contains($product_link, "oboi.masterdom"),
            1 => (isset($datum['product_kind']) ? boolval($datum['product_kind'] == 'Керамогранит') : false),
            2 => (isset($datum['product_kind']) ? boolval($datum['product_kind'] == 'Керамическая плитка') : false),
            3 => (isset($datum['product_kind']) ? boolval($datum['product_kind'] == 'Раковина') : false),
            4 => (isset($datum['product_kind']) ? boolval($datum['product_kind'] == 'Унитаз') : false),
            5 => (isset($datum['product_kind']) ? boolval($datum['product_kind'] == 'Ванна') : false),
            6 => (isset($datum['product_kind']) ? boolval($datum['product_kind'] == 'Душевая кабина') : false),
            7 => (isset($datum['product_kind']) ? boolval($datum['product_kind'] == 'Смеситель') : false),
            8 => (isset($datum['product_kind']) ? boolval($datum['product_kind'] == 'Мебель') : false),
        ];

        $subcategories = [
            0 => 'Декоративные обои',
            1 => 'Керамогранит',
            2 => 'Керамическая плитка',
            3 => 'Раковина',
            4 => 'Унитазы, писсуары и биде',
            5 => 'Ванны',
            6 => 'Душевые кабины и ограждения',
            7 => 'Смесители',
            8 => 'Мебель для ванной',
        ];

        foreach ($subcategory_keys as $subcategory_key => $stmnt) {
            if ($stmnt) {
                $subcategory = $subcategories[$subcategory_key];
                break;
            }
        }

        return $subcategory ?? null;
    }

    static function getEdizm(string $category): string
    {
        $edizm_key = array_search(1, [
            in_array($category, ['Обои и настельные покрытия']),
            in_array($category, ['Плитка и керамогранит']),
            in_array($category, ['Сантехника']),
        ]);

        $edizm = ["рулон", "м^2", "шт"][$edizm_key];

        return $edizm;
    }

    static function getProductLink($category, $articul, $product_id, $name_url)
    {
        $product_link_key = array_search(1, [
            in_array($category, ['Обои и настельные покрытия']),
            in_array($category, ['Плитка и керамогранит']),
            in_array($category, ['Сантехника']),
        ]);
        $product_link = [
            "https://oboi.masterdom.ru/#!srt=popular&v=single&la=$articul&id=$product_id",
            "https://plitka.masterdom.ru/article/$name_url/",
            "https://santehnika.masterdom.ru/rakoviny/$name_url",
        ][$product_link_key];

        return $product_link;
    }

    static function getImages(array $datum, string $url_parser): string|null
    {
        $characteristics = [];
        $images = [];

        foreach ($datum as $key => $value) {
            $characteristics[$key] = $value;
            if (str_contains($key, 'image')) {
                $value = is_array($value) ? $value['path'] : $value;
                $images[$key] = str_contains($url_parser, "oboi.masterdom") ? "https://oboi.masterdom.ru/$value" : $value;
            }
        }
        $images = json_encode($images, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $images;
    }


    /*
    Возвращает на выбор: countries, collections, fabrics(производитель)
    */
    static function plitka(string $needed): array
    {
        $countries = [];
        $fabrics = [];
        $collctions = [];

        $document_1 = Parser::guzzleConnect("https://plitka.masterdom.ru/");

        $api_data_1 = $document_1->find('script')[9]->text();
        $api_data_1 = rtrim(str_replace("window.__initialData=", "", $api_data_1), ";");
        $api_data_1 = json_decode($api_data_1, 1);
        $api_data_1 = $api_data_1['store']['references']['data']['countries'];

        foreach ($api_data_1 as $circle_country) {
            //COUNTRIES
            $countries[] = [
                'name' => $circle_country['name'],
                'id' => $circle_country['id'],
            ];

            if (!isset($circle_country['nested'])) continue;
            $circle_producer = $circle_country['nested']['items'];

            foreach ($circle_producer as $fabric_id => $fabric_name) {
                //FABRICS
                $fabrics[$fabric_id] = [
                    'id' => $fabric_id,
                    'name' => $fabric_name['name'],
                    'country_id' => $circle_country['id'],
                ];

                $circle_collections = $fabric_name['nested']['items'];
                foreach ($circle_collections as $circle_collection) {
                    //COLLECTIONS
                    $collections[] = [
                        'id' => $circle_collection['id'],
                        'name' => $circle_collection['name'],
                        'fabric_id' => $circle_collection['fabric_id'],
                    ];
                }
            }
        }

        switch ($needed) {
            case 'fabrics':
                return $fabrics; //keys: name, id, country_id
            case 'countries':
                return $countries; //keys: name, id
            case 'collections':
                return $collections; //keys: name, id, fabric_id
        }
        return null;
    }

    /*
    Возвращает на выбор: countries, collections, fabrics(производитель)
    */
    static function santechnika(string $needed): array|null
    {
        $document_1 = Parser::guzzleConnect("https://santehnika.masterdom.ru/rakoviny/catalog/");

        $api_data_1 = $document_1->find('script')[10]->text();
        $api_data_1 = rtrim(str_replace("window.__initialData=", "", $api_data_1), ";");
        $api_data_1 = json_decode($api_data_1, 1);
        $fabrics = $api_data_1['store']['references']['data']['fabrics'];
        $countries = $api_data_1['store']['references']['data']['countries'];
        $collections = $api_data_1['store']['references']['data']['collections'];

        switch ($needed) {
            case 'fabrics':
                return $fabrics; //keys: name, name_url, id, country_id
            case 'countries':
                return $countries; //keys: name, name_url, id
            case 'collections':
                return $collections; //keys: name, name_url, id, fabric_id
        }
        return null;
    }
}
