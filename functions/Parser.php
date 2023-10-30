<?php

namespace functions;

require __DIR__ . "/../vendor/autoload.php";

use DiDom\Document;
use DOMDocument;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;

class Parser
{
    static function guzzleConnect(string $link): Document
    {
        $client = new GuzzleClient(['verify' => false]);
        $response = $client->request(
            'GET',
            $link,
            [
                'headers' => [
                    'X-Requested-With' => 'XMLHttpRequest',
                ]
            ]
        );

        $document = self::getHTML($response);

        return $document;
    }

    static function getHTML(ResponseInterface $response): Document
    {
        $document = $response->getBody()->getContents();
        $document = new Document($document);
        return $document;
    }

    static function getProvider(string $parser_link): array
    {
        $keys = [
            0 => str_contains($parser_link, 'masterdom'),
            1 => str_contains($parser_link, 'mosplitka'),
        ];

        $values = [
            [
                "name" => 'https://masterdom.ru/',
                "id" => 0
            ],
            [
                "name" => 'https://mosplitka.ru',
                "id" => 1
            ],
        ];

        foreach ($keys as $i => $key) {
            if ($key) {
                return $values[$i];
            }
        }
    }

    static function nextLink(string $link, int $limit): string|null
    {
        preg_match("#(.+offset=)(\d+)(.*)#", $link, $matches);
        if ($matches) {
            $new_offset_value = $matches[2] + $limit;
            $new_link = $matches[1] . $new_offset_value . $matches[3];

            $document = self::guzzleConnect($new_link);
            $api_data = self::getApiData($document);

            if (boolval(count($api_data) > 0)) {
                return $new_link;
            }
        }

        return null;
    }

    static function getCategoriesList(): array
    {
        $categories = [
            0 => 'Обои и настенные покрытия',
            1 => 'Напольные покрытия',
            2 => 'Плитка и керамогранит',
            3 => 'Сантехника',
            4 => 'Краски',
            5 => 'Лепнина',
        ];

        return $categories;
    }

    static function getSubcategoriesList(): array
    {
        $subcategories = [
            0 => 'Раковины',
            1 => 'Унитазы, писсуары и биде',
            2 => 'Ванны',
            3 => 'Душевые',
            4 => 'Смесители',
            5 => 'Мебель для ванной',
            6 => 'Аксессуары для ванной комнаты',
            7 => 'Комплектующие',
            8 => 'Полотенцесушители',
            9 => 'Декоративные обои',
            10 => 'Керамогранит',
            11 => 'Керамическая плитка',
            12 => 'Натуральный камень',
            13 => 'Мозаика',
            14 => 'Кухонные мойки',
            15 => 'Ступени и клинкер',
            16 => 'SPC-плитка',
            17 => 'Фотообои',
            18 => 'Обои под покраску',
            19 => 'Штукатурка',
            20 => 'Розетки',
        ];

        return $subcategories;
    }

    static function getApiData(Document $document): array
    {
        $api_data = json_decode($document->text(), 1);
        $api_data = $api_data[array_keys($api_data)[0]];
        return $api_data;
    }

    static function insertLink(string $link, string $link_type, string $provider): string
    {
        try {
            $query = "INSERT INTO " . $provider . "_links (link, type) VALUES (?, ?)";
            $types = "ss";
            $values = [$link, $link_type];
            MySQL::bind_sql($query, $types, $values);
            return "success";
        } catch (\Exception $e) {
            Logs::writeLog($e, $provider, $link);
            var_dump($e);
            return "fail";
        }
    }

    static function insertProductData(string $types, array $values, string $product_link, string $provider): void
    {
        //Получаем товар
        $product = MySQL::sql("SELECT id FROM " . $provider . "_products WHERE link='$product_link'");

        $quest = '';
        $colms = "";

        foreach ($values as $key => $value) {
            $values[$key] = isset($value) ? html_entity_decode($value) : null;
        }

        // echo count($values) . ' ' . count($columns) . '<br>';

        if ($product->num_rows) {
            $date_edit = MySQL::get_mysql_datetime();
            $types .= 's';
            $values["date_edit"] = $date_edit;
            $id = mysqli_fetch_assoc($product)['id'];

            $query = "UPDATE " . $provider . "_products SET ";
            foreach ($values as $key => $value) {
                $query .= "`" . $key . "`=?, ";
            }
            $query = substr($query, 0, -2);
            $query .= " WHERE id=$id";

            // echo $query . "<br>";
        } else {
            $query = "INSERT INTO " . $provider . "_products (";
            foreach ($values as $key => $value) {
                $colms .= $key . ", ";
                $quest .= "?, ";
            }
            $colms = substr($colms, 0, -2) . ")";
            $quest = substr($quest, 0, -2);
            $query .= $colms . " VALUES (" . $quest . ")";
            // echo $query . "<br>";
        }

        try {
            MySQL::bind_sql($query, $types, array_values($values));
            echo "<b>не возникло ошибок с добавлением продукта в БД</b><br><br>";
        } catch (\Exception $e) {
            Logs::writeLog($e, $provider);
            echo "<b>возникла ошибка с добавлением продукта в БД:</b><br>" . $e->getMessage() . '<br><br>';
        }
    }

    static function getEdizmList(): array
    {
        $edizm = [
            0 => "рулон",
            1 => "м2",
            2 => "шт",
            3 => "пог.м",
            4 => "л",
        ];
        return $edizm;
    }

    static function getEdizmByUnit(string $edizm): string|null
    {
        $edizm_values = self::getEdizmList();
        switch ($edizm) {
            case "рулон":
                return $edizm_values[0];
                break;
            case "м2":
                return $edizm_values[1];
                break;
            case "шт.":
                return $edizm_values[2];
                break;
            case "пог. м":
                return $edizm_values[3];
                break;
            case "краски":
                return $edizm_values[4];
                break;
        }
        return null;
    }

    static function getEdizm(string $category): string|null
    {
        $edizm_keys = [
            boolval($category == 'Обои и настенные покрытия'),
            boolval($category == 'Плитка и керамогранит'),
            boolval($category == 'Сантехника'),
        ];

        $edizm_values = self::getEdizmList();

        foreach ($edizm_keys as $i => $edizm_key) {
            if ($edizm_key) {

                $edizm = $edizm_values[$i];
                break;
            }
        }

        return $edizm ?? null;
    }
}
