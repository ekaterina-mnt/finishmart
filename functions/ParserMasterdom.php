<?php

namespace functions;

require __DIR__ . "/../vendor/autoload.php";

use DiDom\Document;
use DOMDocument;
use functions\Connect;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;

class ParserMasterdom
{
    static function getCategory(string $url_parser): string
    {
        $all_categories = Parser::getCategoriesList();

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

    static function getSubcategory(string $category, array $datum): string|null
    {
        $subcategory_keys = [
            0 => (isset($datum['product_kind']) ? boolval($datum['product_kind'] == 'Раковина') : false),
            1 => (isset($datum['product_kind']) ? boolval($datum['product_kind'] == 'Унитаз' or $datum['product_kind'] == 'Биде') : false),
            2 => (isset($datum['product_kind']) ? boolval($datum['product_kind'] == 'Ванна') : false),
            3 => (isset($datum['product_kind']) ? boolval($datum['product_kind'] == 'Душевая кабина') : false),
            4 => (isset($datum['product_kind']) ? boolval($datum['product_kind'] == 'Смеситель') : false),
            5 => (isset($datum['product_kind']) ? boolval($datum['product_kind'] == 'Мебель') : false),
            6 => (isset($datum['product_kind']) ? boolval($datum['product_kind'] == 'Аксессуары') : false),
            7 => (isset($datum['product_kind']) ? boolval($datum['product_kind'] == 'Составляющие') : false),
            8 => (isset($datum['product_kind']) ? boolval(html_entity_decode($datum['product_kind']) == 'Полотенцесушитель') : false),
            9 => boolval($category == 'Обои и настенные покрытия'),
            10 => (isset($datum['product_kind']) ? boolval($datum['product_kind'] == 'Керамогранит') : false),
            11 => (isset($datum['product_kind']) ? boolval($datum['product_kind'] == 'Керамическая плитка') : false),
            12 => (isset($datum['product_kind']) ? boolval($datum['product_kind'] == 'Натуральный камень') : false),
            13 => (isset($datum['product_kind']) ? boolval($datum['product_kind'] == 'Мозаика') : false),
        ];

        $subcategories = Parser::getSubcategoriesList();

        foreach ($subcategory_keys as $subcategory_key => $stmnt) {
            if ($stmnt) {
                $subcategory = $subcategories[$subcategory_key];
                break;
            }
        }

        return $subcategory ?? null;
    }

    static function getProductLink(string $subcategory, string $articul, int|null $product_id, string|null $name_url): string|null
    {
        $product_link_keys = Parser::getSubcategoriesList();
        $product_link = null;

        $product_links = [
            0 => "https://santehnika.masterdom.ru/rakoviny/$name_url",
            1 => "https://santehnika.masterdom.ru/unitazy_i_bide/$name_url",
            2 => "https://santehnika.masterdom.ru/vanny/$name_url",
            3 => "https://santehnika.masterdom.ru/dushevye/$name_url",
            4 => "https://santehnika.masterdom.ru/smesiteli/$name_url",
            5 => "https://santehnika.masterdom.ru/mebel/$name_url",
            6 => "https://santehnika.masterdom.ru/aksessuary/$name_url",
            7 => "https://santehnika.masterdom.ru/parts/$name_url",
            8 => "https://santehnika.masterdom.ru/polotencesushitely/$name_url",

            9 => "https://oboi.masterdom.ru/#!srt=popular&v=single&la=$articul&id=$product_id",
            10 => "https://plitka.masterdom.ru/article/$name_url/",
            11 => "https://plitka.masterdom.ru/article/$name_url/",
            12 => "https://plitka.masterdom.ru/article/$name_url/",
            13 => "https://plitka.masterdom.ru/article/$name_url/",
        ];

        foreach ($product_link_keys as $subcategory_link_key => $subcategory_link) {
            if ($subcategory == $subcategory_link) {
                $product_link = $product_links[$subcategory_link_key];
                break;
            }
        }

        if (str_contains($product_link, "https://santehnika.masterdom.ru")) {
            $query = "INSERT INTO all_links (link, type, provider) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE type='additional'";
            $types = "sss";
            $values = [$product_link, 'additional', 'masterdom'];
            MySQL::bind_sql($query, $types, $values);
        }

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



    static function getDataPlitka(): array
    {
        $countries = []; //keys: name, id
        $fabrics = []; //keys: name, id, country_id
        $collctions = []; //keys: name, id, fabric_id
        $usage = []; //keys: name, id, name_url

        $document_1 = Connect::guzzleConnect("https://plitka.masterdom.ru/");

        $api_data_1 = $document_1->find('script')[9]->text();
        $api_data_1 = rtrim(str_replace("window.__initialData=", "", $api_data_1), ";");
        $api_data_1 = json_decode($api_data_1, 1);

        $usage = $api_data_1['store']['references']['data']['product_usages'];

        $api_data_1 = $api_data_1['store']['references']['data']['countries'];


        foreach ($api_data_1 as $circle_country) {
            //COUNTRIES
            $countries[$circle_country['id']] = [
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
                    $collections[$circle_collection['id']] = [
                        'id' => $circle_collection['id'],
                        'name' => $circle_collection['name'],
                        'fabric_id' => $circle_collection['fabric_id'],
                    ];
                }
            }
        }

        return ['fabrics' => $fabrics, 'collections' => $collections, 'countries' => $countries, 'usage' => $usage];
    }

    static function getDataOboi(): array
    {
        $countries = []; //keys: name, id
        $fabrics = null; //даны в общем массиве товара
        $collections = null; //даны в общем массиве товара

        $document_1 = Connect::guzzleConnect("https://oboi.masterdom.ru/ajax/reference/?type=wallpaper");

        $api_data_1 = $document_1->text();
        $api_data_1 = json_decode($api_data_1, 1)['country'];

        foreach ($api_data_1 as $id => $circle_country) {
            //COUNTRIES
            $countries[$id] = [
                'name' => $circle_country['name'],
                'id' => $circle_country['id'],
            ];
        }

        return ['fabrics' => $fabrics, 'collections' => $collections, 'countries' => $countries];
    }

    /*
    Возвращает на выбор: countries, collections, fabrics(производитель)
    */
    static function getDataSantechnika(): array
    {
        $document_1 = Connect::guzzleConnect("https://santehnika.masterdom.ru/rakoviny/catalog/");

        $api_data_1 = $document_1->find('script')[10]->text();
        $api_data_1 = rtrim(str_replace("window.__initialData=", "", $api_data_1), ";");
        $api_data_1 = json_decode($api_data_1, 1);
        $fabrics = $api_data_1['store']['references']['data']['fabrics']; //keys: name, name_url, id, country_id
        $countries = $api_data_1['store']['references']['data']['countries']; //keys: name, name_url, id
        $collections = $api_data_1['store']['references']['data']['collections']; //keys: name, name_url, id, fabric_id

        return ['fabrics' => $fabrics, 'collections' => $collections, 'countries' => $countries];
    }

    static function getDataPolotencesushitely(): array
    {
        $document_1 = Connect::guzzleConnect("https://santehnika.masterdom.ru/polotencesushitely/catalog/");
        $api_data_1 = $document_1->find('script')[10]->text();
        $api_data_1 = rtrim(str_replace("window.__initialData=", "", $api_data_1), ";");
        $api_data_1 = json_decode($api_data_1, 1);

        $fabrics = $api_data_1['store']['references']['data']['fabrics']; //здесь есть все нужные массивы? цифры с плиткой не совпадают, с сантехникой - да, но думаю можно брать цвета и материал
        $collections = $api_data_1['store']['references']['data']['collections'];
        $countries = $api_data_1['store']['references']['data']['countries'];

        $api_data_1 = $api_data_1['store']['products']['data'];

        return ['api_data' => $api_data_1, 'fabrics' => $fabrics, 'collections' => $collections, 'countries' => $countries];
    }
}
