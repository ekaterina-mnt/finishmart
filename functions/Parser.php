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
            Logs::writeLog($e, $link);
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
            Logs::writeLog($e);
            echo "<b>возникла ошибка с добавлением продукта в БД:</b><br>" . $e->getMessage() . '<br><br>';
        }
    }
}
