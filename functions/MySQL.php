<?php

namespace functions;

class MySQL
{
    private static $db;

    static function sql(string $query)
    {
        $db = self::getDB();
        mysqli_query($db, 'SET character_set_results = "utf8"');
        $result = mysqli_query($db, $query);
        return $result;
    }

    static function bind_sql(string $query, string $types, array $values)
    {
        $db = self::getDB();
        $db->set_charset('utf8');
        $stmt = mysqli_prepare($db, $query);

        $stmt->bind_param($types, ...$values);
        $stmt->execute();
        $stmt->close();
    }

    static function getDB()
    {
        if (!self::$db) {
            self::$db = mysqli_connect('localhost', 'root', '', 'parser');
        }
        return self::$db;
    }

    static function add_url()
    {
        $data = $_SERVER['REQUEST_URI'];
        $time = date('Y-m-d H:i:s', time());

        self::sql("INSERT INTO data_1c_exchange (data, time) VALUES ('$data', '$time')");
    }

    static function get_mysql_datetime(): string
    {
        $date = date("Y-m-d H:i:s", time());
        return $date;
    }

    static function firstLinksInsert(string $provider = null)
    {
        $add_links = [];

        if ($provider) {
            switch ($provider) {
                case "masterdom":
                    $add_links = [
                        "https://oboi.masterdom.ru/find/?sort=popular&offset=0" => "product",
                        "https://api.masterdom.ru/api/rest/tile/search.json?sort=popularity_desc&limit=100&offset=0" => "product",
                        "https://api.masterdom.ru/api/rest/bathrooms/sink/search.json?sort=popularity_desc&limit=100&offset=0" => "product",
                        "https://api.masterdom.ru/api/rest/bathrooms/toilet_bidet/search.json?sort=popularity_desc&limit=100&offset=0" => "product",
                        "https://api.masterdom.ru/api/rest/bathrooms/bathtub/search.json?sort=popularity_desc&limit=100&offset=0" => "product",
                        "https://api.masterdom.ru/api/rest/bathrooms/shower/search.json?sort=popularity_desc&limit=100&offset=0" => "product",
                        "https://api.masterdom.ru/api/rest/bathrooms/faucet/search.json?sort=popularity_desc&limit=100&offset=0" => "product",
                        "https://api.masterdom.ru/api/rest/bathrooms/furniture/search.json?sort=popularity_desc&limit=100&offset=0" => "product",
                        "https://api.masterdom.ru/api/rest/bathrooms/accessories/search.json?sort=popularity_desc&limit=100&offset=0" => "product",
                        "https://santehnika.masterdom.ru/polotencesushitely/catalog/" => "product",
                        "https://api.masterdom.ru/api/rest/bathrooms/parts/search.json?sort=popularity_desc&limit=100&offset=0" => "product",
                    ];
                    break;
                case "mosplitka":
                    $add_links = [
                        "https://mosplitka.ru/catalog/rakoviny/?PAGEN_1=1" => "catalog",
                        "https://mosplitka.ru/catalog/unitazy/?PAGEN_1=1" => "catalog",
                        "https://mosplitka.ru/catalog/pissuary/?PAGEN_1=1" => "catalog",
                        "https://mosplitka.ru/catalog/bide/?PAGEN_1=1" => "catalog",
                        "https://mosplitka.ru/catalog/installyatsii/?PAGEN_1=1" => "catalog",
                        "https://mosplitka.ru/catalog/klavishi-smyva/?PAGEN_1=1" => "catalog",
                        "https://mosplitka.ru/catalog/vanny/?PAGEN_1=1" => "catalog",
                        "https://mosplitka.ru/catalog/kukhonnye-moyki/?PAGEN_1=1" => "catalog",
                        "https://mosplitka.ru/catalog/smesiteli/?PAGEN_1=1" => "catalog",
                        "https://mosplitka.ru/catalog/polotentsesushiteli/?PAGEN_1=1" => "catalog",
                        "https://mosplitka.ru/catalog/mebel-dlya-vannoy/?PAGEN_1=1" => "catalog",
                        "https://mosplitka.ru/catalog/aksessuary/?PAGEN_1=1" => "catalog",
                        "https://mosplitka.ru/catalog/dushevye-garnitury/?PAGEN_1=1" => "catalog",
                        "https://mosplitka.ru/catalog/poddony-trapy-lotki/?PAGEN_1=1" => "catalog",
                        "https://mosplitka.ru/catalog/dushevye-boksy/?PAGEN_1=1" => "catalog",
                        "https://mosplitka.ru/catalog/plitka/view_product/?PAGEN_1=1" => "catalog",
                    ];
                    break;
                case "ampir":
                    $add_links = [
                        "https://www.ampir.ru/catalog/oboi/page1/" => "catalog", //категория Обои, подкатегории: Обои под покраску, Фотообои, Декоративные
                        "https://www.ampir.ru/catalog/lepnina/page1/" => "catalog", //категория Лепнина, подкатегории: Карнизы, Молдинги, Плинтусы, Дверное обрамление, Потолочный декор, Другое
                        "https://www.ampir.ru/catalog/kraski/page1/" => "catalog", //категория Краски, подкатегорий нет
                        "https://www.ampir.ru/catalog/shtukaturka/page1/" => "catalog", //категория Краски, подкатегория Штукатурка
                        "https://www.ampir.ru/catalog/rozetki/page1/" => "catalog", //категория Лепнина, подкатегория Розетки
                    ];
                case "laparet":
                    $add_links = [
                        "https://laparet.ru/catalog/?page=1" => "catalog",
                    ];
            }

            if ($provider) {
                foreach ($add_links as $link => $type) {
                    $query = "INSERT INTO " . $provider . "_links (`link`, `type`) VALUES (?, ?) ON DUPLICATE KEY UPDATE type='$type'";
                    $types = "ss";
                    $values = array($link, $type);
                    self::bind_sql($query, $types, $values);
                }
            }
        } else {

            $add_links = [
                // "https://laparet.ru/catalog/?page=1" => [
                //     "catalog",
                //     'laparet',
                // ],
                // "https://ntceramic.ru/catalog/keramogranit/?PAGEN_1=1" => [
                //     "catalog",
                //     'ntceramic',
                // ],
                // "https://ntceramic.ru/catalog/santekhnika/?PAGEN_1=1" => [
                //     "catalog",
                //     'ntceramic',
                // ],
                // "https://ntceramic.ru/catalog/mebel/?PAGEN_1=1" => [
                //     "catalog",
                //     'ntceramic',
                // ],
                // "https://www.olimpparket.ru/catalog/" => [
                //     "catalog",
                //     'olimpparket',
                // ],
                "https://moscow.domix-club.ru/catalog/laminat/?PAGEN_1=1" => [
                    "catalog",
                    'domix',
                ],
                "https://moscow.domix-club.ru/catalog/vinilovaya_plitka/?PAGEN_1=1" => [
                    "catalog",
                    'domix',
                ],
                "https://moscow.domix-club.ru/catalog/kraski/?PAGEN_1=1" => [
                    "catalog",
                    'domix',
                ],
                "https://moscow.domix-club.ru/catalog/oboi/?PAGEN_1=1" => [
                    "catalog",
                    'domix',
                ],
                "https://moscow.domix-club.ru/catalog/plitka/?PAGEN_1=1" => [
                    "catalog",
                    'domix',
                ],
                "https://moscow.domix-club.ru/catalog/santehnika/?PAGEN_1=1" => [
                    "catalog",
                    'domix',
                ],
                "https://moscow.domix-club.ru/catalog/mebel_dlya_vannoi/?PAGEN_1=1" => [
                    "catalog",
                    'domix',
                ],
                "https://moscow.domix-club.ru/catalog/linoleum/?PAGEN_1=1" => [
                    "catalog",
                    'domix',
                ],
                "https://moscow.domix-club.ru/catalog/kovry/?PAGEN_1=1" => [
                    "catalog",
                    'domix',
                ],
                "https://moscow.domix-club.ru/catalog/inzhenernaya_doska/?PAGEN_1=1" => [
                    "catalog",
                    'domix',
                ],
                "https://moscow.domix-club.ru/catalog/parketnaya_doska/?PAGEN_1=1" => [
                    "catalog",
                    'domix',
                ],
                "https://moscow.domix-club.ru/catalog/kovrovye_pokrytiya/?PAGEN_1=1" => [
                    "catalog",
                    'domix',
                ],
                "https://moscow.domix-club.ru/catalog/freski-i-fotooboi/?PAGEN_1=1" => [
                    "catalog",
                    'domix',
                ],
                "https://moscow.domix-club.ru/catalog/soputstvuyushie-tovary/?PAGEN_1=1" => [
                    "catalog",
                    'domix',
                ],
                "https://moscow.domix-club.ru/catalog/arkhitekturnyy-dekor/?PAGEN_1=1" => [
                    "catalog",
                    'domix',
                ],
                "https://moscow.domix-club.ru/catalog/podlozhka/?PAGEN_1=1" => [
                    "catalog",
                    'domix',
                ],
                "https://moscow.domix-club.ru/catalog/plintusy_i_porogi/?PAGEN_1=1" => [
                    "catalog",
                    'domix',
                ],
                // "https://finefloor.ru/catalog/" => [
                //     "catalog",
                //     "finefloor",
                // ],
            ];

            foreach ($add_links as $link => $data) {
                $query = "INSERT INTO all_links (`link`, `type`, `provider`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE type='$data[0]'";
                $types = "sss";
                $values = array($link, $data[0], $data[1]);
                self::bind_sql($query, $types, $values);
            }
        }
    }

    static function decreaseProductViews(int $views, string $url_parser, string $provider): void
    {
        $query = "UPDATE " . $provider . "_links SET product_views=? WHERE link='$url_parser'";
        $types = "i";
        $views = --$views === 0 ? NULL : $views;
        $values = array($views);
        self::bind_sql($query, $types, $values);
    }

    static function decreaseLinkViews(int $views, string $url_parser, string $provider): void
    {
        $query = "UPDATE " . $provider . "_links SET views=? WHERE link='$url_parser'";
        $types = "i";
        $views = --$views === 0 ? NULL : $views;
        $values = array($views);
        self::bind_sql($query, $types, $values);
    }
}
