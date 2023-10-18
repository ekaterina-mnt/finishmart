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

    static function insertProductData(): void
    {
        // exit;

        //     //добавление/обновление записи в БД

        //     $types = 'ssissssssssssdddddssss';
        //     $values = [
        //         $product_link, $stock, $price, $edizm, $articul, $title, $images, $variants, $characteristics, $path, $category1, $category2, $category3,
        //         $length, $width, $height, $depth, $thickness, $format, $material, $producer, $collection
        //     ];

        //     //Получаем товар
        //     $product = MySQL::sql("SELECT id FROM masterdom_products WHERE link='$product_link'");

        //     if ($product->num_rows) {

        //         $date_edit = MySQL::get_mysql_datetime();
        //         $types .= 's';
        //         $values[] = $date_edit;

        //         $id = mysqli_fetch_assoc($product)['id'];
        //         $query = "UPDATE masterdom_products 
        //                 SET `link`=?, `stock`=?, `price`=?,
        //                 `edizm`=?, `articul`=?, `title`=?, `images`=?, `variants`=?,
        //                 `characteristics`=?, `path`=?, `category1`=?, `category2`=?,
        //                 `category3`=?, `length`=?, `width`=?, `height`=?, `depth`=?, 
        //                 `thickness`=?, `format`=?, `material`=?, `producer`=?, 
        //                 `collection`=?, `date_edit`=?
        //                 WHERE id=$id";
        //     } else {
        //         $query = "INSERT INTO masterdom_products
        // (`link`, `stock`, `price`, `edizm`, `articul`, `title`, `images`, `variants`, `characteristics`, `path`, `category1`, `category2`, `category3`, 
        // `length`, `width`, `height`, `depth`, `thickness`, `format`, `material`, `producer`, `collection`) 
        // VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        //     }

        //     try {
        //         MySQL::bind_sql($query, $types, $values);
        //         echo "<b>не возникло ошибок с добавлением продукта в БД</b><br><br>";
        //     } catch (Exception $e) {
        //         Logs::writeLog($e);
        //         echo "<b>возникла ошибка с добавлением продукта в БД:</b><br>" . $e->getMessage() . '<br><br>';
        //     }
    }
}
