<?php

namespace functions;

require __DIR__ . "/../vendor/autoload.php";

use DiDom\Document;
use DOMDocument;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;

class Parser
{
    static function guzzleConnect(string $link, $encoding = null): Document
    {
        $client = new GuzzleClient(['verify' => false]);
        $response = $client->request(
            'GET',
            $link,
            [
                'headers' => [
                    'X-Requested-With' => 'XMLHttpRequest',
                ],
            ],
        );

        $document = self::getHTML($response, $encoding ?? null);

        return $document;
    }

    static function getHTML(ResponseInterface $response, $encoding = null): Document
    {
        $document = $response->getBody()->getContents();
        if ($encoding) {
            $document = new Document(string: $document, encoding: $encoding);
        } else {
            $document = new Document(string: $document);
        }
        return $document;
    }

    static function getProvider(string $parser_link): string
    {
        $keys = [
            0 => str_contains($parser_link, 'masterdom'),
            1 => str_contains($parser_link, 'mosplitka'),
            2 => str_contains($parser_link, 'ampir'),
            3 => str_contains($parser_link, 'laparet'),
            4 => str_contains($parser_link, 'ntceramic'),
            5 => str_contains($parser_link, 'olimpparket')
        ];

        $values = [
            0 => 'masterdom',
            1 => 'mosplitka',
            2 => 'ampir',
            3 => 'laparet',
            4 => 'ntceramic',
            5 => 'olimpparket',
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

    static function nextLinkSurgaz(string $url_parser)
    {
        preg_match("#(.+PAGEN_1=)(\d+)(.*)#", $url_parser, $matches);

        if ($matches) {
            $new_offset_value = $matches[2] + 1;
            $new_link = $matches[1] . $new_offset_value . $matches[3];

            if ($new_link) {
                $query = "INSERT INTO all_links (link, type, provider) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE type='product'";
                $types = "sss";
                $values = [$new_link, 'product', 'surgaz'];
                MySQL::bind_sql($query, $types, $values);
                echo "<b>Следующая ссылка: </b> $new_link (добавлена в БД)<br><br>";
            }
        }

        return null;
    }

    static function getCategoriesList(): array
    {
        //не менять порядок
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
        //не менять порядок
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
            21 => 'Карнизы',
            22 => 'Молдинги',
            23 => 'Плинтусы',
            24 => 'Дверное обрамление',
            25 => 'Потолочный декор',
            26 => 'Другое',
            27 => 'Ламинат',
            28 => 'Инженерная доска',
            29 => 'Паркетная доска',
            30 => 'Штучный паркет',
            31 => 'Виниловые полы',
            32 => 'Подложка под напольные покрытия',
            33 => 'Плинтус напольный',
            34 => 'Массивная доска',
            35 => 'Пробковое покрытие',
            36 => 'Линолиум',
            37 => 'Кварцвиниловые полы',
            38 => 'Кварциниловые панели',
            39 => 'Сопутствующие',
        ];

        return $subcategories;
    }

    static function getApiData(Document $document): array
    {
        $api_data = json_decode($document->text(), 1);
        $api_data = $api_data[array_keys($api_data)[0]];
        return $api_data;
    }

    static function insertLink(string $link, string $link_type, string $provider = null): string
    {
        if ($provider) {
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
    }

    static function insertLink1(string $link, string $link_type, string $provider): string
    {
        try {
            $query = "INSERT INTO all_links (link, type, provider) VALUES (?, ?, ?)";
            $types = "sss";
            $values = [$link, $link_type, $provider];
            MySQL::bind_sql($query, $types, $values);
            return "success";
        } catch (\Exception $e) {
            Logs::writeLog($e, $provider, $link);
            var_dump($e);
            return "fail";
        }
    }

    static function generateLink($href, $provider, $url_parser = null)
    {
        $starts = [
            'laparet' => 'https://laparet.ru',
            'ntceramic' => 'https://ntceramic.ru',
            'olimpparket' => 'https://olimpparket.ru',
            'domix' => 'https://moscow.domix-club.ru',
            'finefloor' => "https://finefloor.ru",
            'tdgalion' => "https://www.tdgalion.ru",
            'dplintus' => "https://dplintus.ru",
            'surgaz' => "https://surgaz.ru",
            'centerkrasok' => "https://www.centerkrasok.ru",
            'alpinefloor' => "https://alpinefloor.su",
        ];

        if ($url_parser == 'https://olimpparket.ru/catalog/plintusa_i_porogi/' and !str_contains($href, "/catalog")) {
            return $url_parser . $href;
        }

        if ($provider == 'lkrn') return $href;     
        
        return $starts[$provider] . $href;
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

    static function insertProductData1(string $types, array $values, string $product_link): void
    {
        //Получаем товар
        $product = MySQL::sql("SELECT id FROM all_products WHERE link='$product_link'");

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

            $query = "UPDATE all_products SET ";
            foreach ($values as $key => $value) {
                $query .= "`" . $key . "`=?, ";
            }
            $query = substr($query, 0, -2);
            $query .= " WHERE id=$id";
            // echo $query . "<br>";
        } else {
            $query = "INSERT INTO all_products (";
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
            case "рул.":
                return $edizm_values[0];
                break;
            case "м2":
            case "кв. м":
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
            default:
                return $edizm_values[2];
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

    static function getLinkType(string $link): string|null
    {
        $keys = [
            'product' => [
                // preg_match("#https://mosplitka.ru/product.+#", $link),
                // preg_match("#https://www.ampir.ru/catalog/.+/\d+/#", $link),
                preg_match("#https://laparet.ru/catalog/.+\.html#", $link),
                preg_match("#https://ntceramic.ru/catalog/.+/.*#", $link) and !preg_match("#https://ntceramic.ru/catalog/.+/?PAGEN_.+#", $link),
                preg_match("#https://olimpparket.ru/product/.+/#", $link),
                preg_match("#https://www.olimpparket.ru/catalog/plintusa_i_porogi/.+/.+/.+/#", $link),
                preg_match(("#https://moscow.domix-club.ru/catalog/.+/.+/.*#"), $link),
                preg_match("#https://finefloor.ru/product/.+#", $link),
                preg_match("#https://www.tdgalion.ru/catalog\/[^\/]+\/[^\/]+\/#", $link) and !preg_match("#https://www.tdgalion.ru/catalog.+PAGEN_.+#", $link),
                preg_match("#https://dplintus.ru/catalog\/[^\/]+\/[^\/]+\/#", $link),
                preg_match("#https://surgaz.ru/katalog\/[^\/]+\/#", $link),
                preg_match("#https://www.centerkrasok.ru/product\/[^\/]+\/#", $link),
                preg_match("#https://alpinefloor.su/catalog\/.+#", $link),
                preg_match("#https://lkrn.ru/product\/.+#", $link),
            ],
            'catalog' => [
                // preg_match("#https://mosplitka.ru/catalog.+#", $link) and !preg_match("#.php$#", $link),
                preg_match("#https://olimpparket.ru/catalog/.+/#", $link),
                preg_match("#https://www.ampir.ru/catalog/.+/page\d+.*#", $link),
                preg_match("#https://ntceramic.ru/catalog/.+/?PAGEN_.+#", $link),
                preg_match("#https://laparet.ru/catalog/.+page=\d+#", $link),
                preg_match("#https://finefloor.ru/catalog/.+#", $link),
                preg_match("#https://moscow.domix-club.ru/catalog/.+/?PAGEN_.+#", $link),
                preg_match("#https://www.tdgalion.ru/catalog.+PAGEN_.+#", $link),
                preg_match("#https://dplintus.ru/catalog\/[^\/]+\/#", $link),
                preg_match("#https://www.centerkrasok.ru/catalog\/[^\/]+\/#", $link),
                preg_match("#https://lkrn.ru/product-category/.+#", $link),
            ],
        ];

        foreach ($keys as $key => $statements) {
            foreach ($statements as $stmnt) {
                if ($stmnt) {
                    return $key;
                }
            }
        }
        return null;
    }

    static function getImages($images_res, string $provider): string
    {
        $keys = [
            'ntceramic' => [
                "attr" => "href",
                "start" => "https://ntceramic.ru",
            ],
            'domix' => [
                "attr" => "content",
                "start" => "https://moscow.domix-club.ru",
            ],
            "laparet" => [
                "attr" => "href",
                "start" => "https://laparet.ru",
            ],
            "olimpparket" => [
                "attr" => "href",
                "start" => "https://www.olimpparket.ru",
            ],
            "finefloor" => [
                "attr" => "href",
                "start" => "https://finefloor.ru",
            ],
            "surgaz" => [
                "attr" => "data-src",
                "start" => "https://surgaz.ru",
            ],
            "dplintus" => [
                "attr" => "src",
                "start" => "https://dplintus.ru",
            ],
            "tdgalion" => [
                "attr" => "data-src",
                "start" => "https://www.tdgalion.ru",
            ],
            "centerkrasok" => [
                "attr" => "data-image",
                "start" => "https://www.centerkrasok.ru",
            ],
            "alpinefloor" => [
                "attr" => "href", 
                "start" => "https://alpinefloor.su",
            ],
            "lkrn" => [
                "attr" => "href",
                "start" => "",
            ]
        ];

        $images = array();

        $n = 1;
        foreach ($images_res as $i => $img) {
            // echo $i . ' ' . $img->attr($keys[$provider]['attr']) . "<br />";
            if ($img->attr($keys[$provider]['attr'])) {
                $src = $keys[$provider]['start'] . $img->attr($keys[$provider]['attr']);
                if (array_search($src, $images) or str_contains($src, "youtube")) continue;
                $images["img$n"] = $src;
                $n += 1;
            }
        }
        $images = json_encode($images, JSON_UNESCAPED_SLASHES);
        return $images;
    }
}
