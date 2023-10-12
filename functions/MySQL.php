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
        $stmt = mysqli_prepare($db, $query);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
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

    static function firstLinksInsert()
    {
        $add_links = [
            // Мосплитка
            // "https://mosplitka.ru/catalog" => "catalog",
            // МастерДом
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
            "https://svet.masterdom.ru/find/" => "product",
        ];

        foreach ($add_links as $link => $type) {
            $query = "INSERT INTO masterdom_links (`link`, `type`) VALUES (?, ?) ON DUPLICATE KEY UPDATE type='$type'";
            $types = "ss";
            $values = array($link, $type);
            self::bind_sql($query, $types, $values);
        }
    }
}
