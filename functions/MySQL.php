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
        if ($stmt->errno) var_dump($stmt->errno);
        $stmt->close();
    }

    static function getDB()
    {
        if (!self::$db) {
            // self::$db = mysqli_connect('localhost', 'root', '', 'parser');
            self::$db = mysqli_connect('localhost', 'penzevrv_2109', 'Q7&ziPyd', 'penzevrv_2109');
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
            "https://mosplitka.ru/catalog/" => [
                "catalog",
                "mosplitka",
            ],
            "https://mosplitka.ru/catalog/plitka/view_product/" => [
                'catalog',
                'mosplitka',
            ],
            "https://www.ampir.ru/catalog/oboi/page1/" => [ //категория Обои, подкатегории: Обои под покраску, Фотообои, Декоративные
                "catalog",
                "ampir",
            ],
            "https://www.ampir.ru/catalog/lepnina/page1/" => [ //категория Лепнина, подкатегории: Карнизы, Молдинги, Плинтусы, Дверное обрамление, Потолочный декор, Другое
                "catalog",
                "ampir",
            ],
            "https://www.ampir.ru/catalog/kraski/page1/" => [ //категория Краски, подкатегорий нет
                "catalog",
                "ampir",
            ],
            "https://www.ampir.ru/catalog/shtukaturka/page1/" => [ //категория Краски, подкатегория Штукатурка
                "catalog",
                "ampir",
            ],
            "https://www.ampir.ru/catalog/rozetki/page1/" => [ //категория Лепнина, подкатегория Розетки
                "catalog",
                "ampir",
            ],
            "https://oboi.masterdom.ru/find/?sort=popular&offset=0" => [
                "product",
                "masterdom",
            ],
            "https://api.masterdom.ru/api/rest/tile/search.json?sort=popularity_desc&limit=100&offset=0" => [
                "product",
                "masterdom",
            ],
            "https://api.masterdom.ru/api/rest/bathrooms/sink/search.json?sort=popularity_desc&limit=100&offset=0" => [
                "product",
                "masterdom",
            ],
            "https://api.masterdom.ru/api/rest/bathrooms/toilet_bidet/search.json?sort=popularity_desc&limit=100&offset=0" => [
                "product",
                "masterdom",
            ],
            "https://api.masterdom.ru/api/rest/bathrooms/bathtub/search.json?sort=popularity_desc&limit=100&offset=0" => [
                "product",
                "masterdom",
            ],
            "https://api.masterdom.ru/api/rest/bathrooms/shower/search.json?sort=popularity_desc&limit=100&offset=0" => [
                "product",
                "masterdom",
            ],
            "https://api.masterdom.ru/api/rest/bathrooms/faucet/search.json?sort=popularity_desc&limit=100&offset=0" => [
                "product",
                "masterdom",
            ],
            "https://api.masterdom.ru/api/rest/bathrooms/furniture/search.json?sort=popularity_desc&limit=100&offset=0" => [
                "product",
                "masterdom",
            ],
            "https://api.masterdom.ru/api/rest/bathrooms/accessories/search.json?sort=popularity_desc&limit=100&offset=0" => [
                "product",
                "masterdom",
            ],
            "https://santehnika.masterdom.ru/polotencesushitely/catalog/" => [
                "product",
                "masterdom",
            ],
            "https://api.masterdom.ru/api/rest/bathrooms/parts/search.json?sort=popularity_desc&limit=100&offset=0" => [
                "product",
                "masterdom",
            ],
            "https://laparet.ru/catalog/?page=1" => [
                "catalog",
                'laparet',
            ],
            "https://ntceramic.ru/catalog/keramogranit/?PAGEN_1=1" => [
                "catalog",
                'ntceramic',
            ],
            "https://ntceramic.ru/catalog/santekhnika/?PAGEN_1=1" => [
                "catalog",
                'ntceramic',
            ],
            "https://ntceramic.ru/catalog/mebel/?PAGEN_1=1" => [
                "catalog",
                'ntceramic',
            ],
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
            "https://finefloor.ru/catalog/" => [
                "catalog",
                "finefloor",
            ],
            // "https://surgaz.ru/ajax.php?ajax=Y&PAGEN_1=1&PAGE_ELEMENT_COUNT=1000&LANGUAGE_ID=ru&act=collection" => [
            //     "catalog",
            //     "surgaz",
            // ],
            "https://surgaz.ru/katalog/" => [
                "catalog",
                "surgaz",
            ],
            "https://alpinefloor.su/catalog/spc-laminat/" => [
                "catalog",
                "alpinefloor",
            ],
            "https://alpinefloor.su/catalog/kvartsvinilovaya-plitka/" => [
                "catalog",
                "alpinefloor",
            ],
            "https://alpinefloor.su/catalog/laminat/" => [
                "catalog",
                "alpinefloor",
            ],
            "https://alpinefloor.su/catalog/inzhenernaya-doska/" => [
                "catalog",
                "alpinefloor",
            ],
            "https://alpinefloor.su/catalog/quartz-tiles-vinyl-for-walls/" => [
                "catalog",
                "alpinefloor",
            ],
            "https://alpinefloor.su/catalog/related-products/" => [
                "catalog",
                "alpinefloor",
            ],
            "https://www.centerkrasok.ru/catalog/" => [
                "catalog",
                "centerkrasok",
            ],
            "https://www.tdgalion.ru/catalog/?view=products" => [
                "catalog",
                "tdgalion",
            ],
            "https://dplintus.ru/catalog/" => [
                "catalog",
                "dplintus",
            ],
            "https://lkrn.ru/catalog/" => [
                "catalog",
                "lkrn",
            ],
            "https://artkera.ru/collections/" => [
                "catalog",
                "artkera",
            ],
            "https://evroplast.ru/smart_search/ajax.php?type=get_list" => [
                "product",
                "evroplast",
            ],
            "https://olimp-parketa.ru" => [
                "catalog",
                "olimp",
            ], 
            "https://moscow.fargospc.ru" => [
                "catalog",
                "fargo",
            ]
        ];

        foreach ($add_links as $link => $data) {
            $query = "INSERT INTO all_links (`link`, `type`, `provider`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE type='$data[0]'";
            $types = "sss";
            $values = array($link, $data[0], $data[1]);
            self::bind_sql($query, $types, $values);
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
