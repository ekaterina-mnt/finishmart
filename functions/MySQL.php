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
                    "https://mosplitka.ru/catalog/plitka/view_product/?PAGEN_1=1" => "catalog",
                    "https://mosplitka.ru/catalog/vanny/?PAGEN_1=1" => "catalog",
                    "https://mosplitka.ru/catalog/rakoviny/?PAGEN_1=1" => "catalog",
                    "https://mosplitka.ru/catalog/smesiteli/?PAGEN_1=1" => "catalog",
                    "https://mosplitka.ru/catalog/unitazy/?PAGEN_1=1" => "catalog",
                    "https://mosplitka.ru/catalog/dushevye-garnitury/?PAGEN_1=1" => "catalog",
                    "https://mosplitka.ru/catalog/installyatsii/?PAGEN_1=1" => "catalog",
                    "https://mosplitka.ru/catalog/dushevye-boksy/?PAGEN_1=1" => "catalog",
                    "https://mosplitka.ru/catalog/kukhonnye-moyki/?PAGEN_1=1" => "catalog",
                    "https://mosplitka.ru/catalog/poddony-trapy-lotki/dushevie_poddony/?PAGEN_1=1" => "catalog",
                    "https://mosplitka.ru/catalog/polotentsesushiteli/?PAGEN_1=1" => "catalog",
                    "https://mosplitka.ru/catalog/mebel-dlya-vannoy/?PAGEN_1=1" => "catalog",
                    "https://mosplitka.ru/catalog/aksessuary/?PAGEN_1=1" => "catalog",
                    "https://mosplitka.ru/catalog/svet/?PAGEN_1=1" => "catalog",
                    "https://mosplitka.ru/catalog/inzhenernaya_santekhnika/?PAGEN_1=1" => "catalog",
                    "https://mosplitka.ru/catalog/otoplenie/?PAGEN_1=1" => "catalog",
                ];
                break;
        }

        if ($provider) {
            foreach ($add_links as $link => $type) {
                $query = "INSERT INTO " . $provider . "_links (`link`, `type`) VALUES (?, ?) ON DUPLICATE KEY UPDATE type='$type'";
                $types = "ss";
                $values = array($link, $type);
                self::bind_sql($query, $types, $values);
            }
        }
    }

    static function decreaseViews(int $views, string $url_parser): void
    {
        $views -= 1;
        MySQL::sql("UPDATE links SET views=$views WHERE link='$url_parser'");
    }
}
