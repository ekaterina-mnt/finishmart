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
        $client = new GuzzleClient();
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
                "id" => 0
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

    static function getApiData(Document $document): array
    {
        $api_data = json_decode($document->text(), 1);
        $api_data = $api_data[array_keys($api_data)[0]];
        return $api_data;
    }

    static function insertProductData(string $types, array $values, string $product_link): void
    {
        //добавление/обновление записи в БД

        //Получаем товар
        $product = MySQL::sql("SELECT id FROM masterdom_products WHERE link='$product_link'");

        $columns = [
            0 => "title",
            1 => "articul",
            2 => "category",
            3 => "subcategory",
            4 => "link",
            5 => "price",
            6 => "edizm",
            7 => "stock",
            8 => "country",
            9 => "producer",
            10 => "collection",
            11 => "provider_id",
            12 => "length",
            13 => "width",
            14 => "height",
            15 => "depth",
            16 => "thickness",
            17 => "format",
            18 => "material",
            19 => "images",
            20 => "variants",
            21 => "product_usages",
            22 => "characteristics",
        ];

        $quest = '';
        $colms = "";

        foreach ($values as $key => $value) {
            $values[$key] = isset($value) ? html_entity_decode($value) : null;
        }

        // echo count($values) . ' ' . count($columns) . '<br>';

        if ($product->num_rows) {
            $date_edit = MySQL::get_mysql_datetime();
            $types .= 's';
            $columns[count($columns)] = "date_edit";
            $values[] = $date_edit;
            $id = mysqli_fetch_assoc($product)['id'];

            $query = "UPDATE masterdom_products SET ";
            foreach ($values as $i => $value) {
                $query .= "`" . $columns[$i] . "`=?, ";
            }
            $query = substr($query, 0, -2);
            $query .= " WHERE id=$id";

            // echo $query . "<br>";
        } else {
            $query = "INSERT INTO masterdom_products (";
            foreach ($values as $i => $value) {
                $colms .= $columns[$i] . ", ";
                $quest .= "?, ";
            }
            $colms = substr($colms, 0, -2) . ")";
            $quest = substr($quest, 0, -2);
            $query .= $colms . " VALUES (" . $quest . ")";
            // echo $query . "<br>";
        }

        try {
            MySQL::bind_sql($query, $types, $values);
            echo "<b>не возникло ошибок с добавлением продукта в БД</b><br><br>";
        } catch (\Exception $e) {
            Logs::writeLog($e);
            echo "<b>возникла ошибка с добавлением продукта в БД:</b><br>" . $e->getMessage() . '<br><br>';
        }
    }
}
