<?php
require __DIR__ . "/vendor/autoload.php";

use functions\MySQL;
use functions\Logs;
use functions\TechInfo;
use functions\Parser;

TechInfo::start();

try {
    //Получаем ссылку, с которой будем парсить
    $query = MySQL::sql("SELECT link, product_views FROM masterdom_links WHERE type='product' ORDER BY product_views, id LIMIT 1"); //поменять имя таблицы

    if (!$query->num_rows) {
        Logs::writeCustomLog("не получено ссылки для парсинга");
        TechInfo::errorExit("не получено ссылки для парсинга");
    }

    $res = mysqli_fetch_assoc($query);

    //Получаем ссылку
    $url_parser = $res['link'];
    // $url_parser = 'https://api.masterdom.ru/api/rest/tile/search.json?sort=popularity_desc&limit=100&offset=0'; //test
    // $url_parser = 'https://oboi.masterdom.ru/find/?sort=popular&offset=0';
    // $url_parser = "https://santehnika.masterdom.ru/polotencesushitely/catalog/";
    // $url_parser = "https://plitka.masterdom.ru/";
    $url_parser = "https://api.masterdom.ru/api/rest/bathrooms/sink/search.json?sort=popularity_desc&limit=100&offset=0";
    TechInfo::whichLinkPass($url_parser);

    //Увеличиваем просмотры ссылки
    $views = $res['product_views'] + 1;
    $date_edit = MySQL::get_mysql_datetime();
    MySQL::sql("UPDATE masterdom_links SET product_views=$views, date_edit='$date_edit' WHERE link='$url_parser'"); //поменять имя таблицы

    //Получаем html страницы
    try {
        $document = Parser::guzzleConnect($url_parser);
    } catch (Throwable $e) {
        //Снова уменьшаем просмотры, чтобы скрипт потом еще раз прошел ссылку и прекращаем работу скрипта
        $views -= 1;
        MySQL::sql("UPDATE links SET views=$views WHERE link='$url_parser'");
        Logs::writeLog($e);
        TechInfo::errorExit($e);
    }

    //Проверяем есть ли следующая ссылка для выгрузки товаров + добавляем если есть
    $limit = str_contains($url_parser, "oboi.masterdom") ? 30 : 100;
    $next_link = Parser::nextLink($url_parser, $limit);
    if ($next_link) {
        $query = "INSERT INTO masterdom_links (link, type) VALUES (?, ?) ON DUPLICATE KEY UPDATE type='product'";
        $types = "ss";
        $values = [$next_link, 'product'];
        MySQL::bind_sql($query, $types, $values);
        echo "<b>Следующая ссылка: </b> $next_link (добавлена в БД)<br><br>";
    } else {
        echo "<b>Следующая ссылка: </b> нет<br><br>";
    }

    //Начинаем вытаскивать нужные данные
    $api_data = Parser::getApiData($document);

    //Нужные данные для masterdom перед циклом:
    $all_tile_producers = Parser::masterdomProducersCollections('producers');
    $all_tile_collections = Parser::masterdomProducersCollections('collections');

    echo "<b>Всего товаров в ссылке:</b> " . count($api_data) . " шт.<br><br>";
    $product_ord_num = 1;
    foreach ($api_data as $datum) {
        echo "<br><b>Товар " . $product_ord_num++ . "</b><br><br>";

        //название товара
        $title = (isset($datum['fullname']) ? $datum['fullname'] : $datum['full_name']) ?? null;
        //цена
        $price = (isset($datum['price_site']) ? $datum['price_site'] : $datum['price']) ?? null;
        //артикул
        $articul = $datum['article'] ?? null;
        //ссылка на товар
        $product_id = $datum['id'] ?? null;
        $name_url = $datum['name_url'] ?? null;
        $product_link_key = array_search(1, [
            str_contains($url_parser, "oboi.masterdom"),
            str_contains($url_parser, "plitka.masterdom"),
            str_contains($url_parser, 'api.masterdom.ru/api/rest/bathrooms/sink'),
        ]);
        $product_link = [
            "https://oboi.masterdom.ru/#!srt=popular&v=single&la=$articul&id=$product_id",
            "https://plitka.masterdom.ru/article/$name_url/",
            "https://santehnika.masterdom.ru/rakoviny/$name_url",
        ][$product_link_key];

        //категории
        $category1_key = array_search(1, [
            str_contains($product_link, "oboi.masterdom"),
            str_contains($product_link, "plitka.masterdom")
        ]);
        $category1 = ['Обои и настельные покрытия', 'Плитка и керамогранит'][$category1_key];
        $category2_key = array_search(1, [
            str_contains($product_link, "oboi.masterdom"),
            (isset($datum['product_kind']) ? boolval($datum['product_kind'] == 'Керамогранит') : false),
            (isset($datum['product_kind']) ? boolval($datum['product_kind'] == 'Керамическая плитка') : false),
        ]);
        $category2 = ['Декоративные обои', 'Керамогранит', 'Керамическая плитка'][$category2_key];
        $category3 = null;

        //единица измерения
        $edizm = in_array([], $category1) ? "рулон" : "м^2";
        //остатки товара
        $stock = isset($datum['balance']) ? $datum['balance'] : 'Нет данных';
        //производитель
        $producer = (str_contains($url_parser, "oboi.masterdom") ? $datum['fabric'] : $all_tile_producers[$datum['fabric']]['fabric_name']) ?? null;
        //коллекция
        $collection = (str_contains($url_parser, "oboi.masterdom") ? $datum['collection_name'] : $all_tile_collections[$datum['collection']]['collection_name']) ?? null;
        //длина
        $length = $datum['length'] ?? null;
        //ширина
        $width = $datum['width'] ?? null;
        //высота
        $height = $datum['height'] ?? null;
        //глубина
        $depth = null;
        //толщина
        $thickness = null;
        //формат
        $format = null;
        //материал
        $material = $datum['type'] ?? null;
        //картинки
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

        //все характеристики
        $characteristics = json_encode($characteristics, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        //варианты исполнения
        $variants = null;
        //путь
        $path = null;

        // Итоговый массив для проверки
        $arr = [
            'id на сайте контрагента' => $product_id,
            "ссылка" => $product_link, "остатки" => $stock, "цена" => $price, "ед.изм" => $edizm, "артикул" => $articul,
            "название" => $title, "картинки" => $images, "варианты" => $variants, "характеристики" => $characteristics,
            "путь" => $path, "категория1" => $category1, "категория2" => $category2, "категория3" => $category3,
            "длина" => $length, "ширина" => $width, "высота" => $height, "глубина" => $depth, "толщина" => $thickness,
            "формат" => $format, "материал" => $material, "производитель" => $producer, "коллекция" => $collection,
        ];

        echo "<b>итоговые данные, которые мы спарсили:</b><br><br>";
        foreach ($arr as $key => $i) {
            echo "$key: ";
            var_dump($i);
            echo "<br><br>";
        }

        //добавление/обновление записи в БД

        $types = 'ssissssssssssdddddssss';
        $values = [
            $product_link, $stock, $price, $edizm, $articul, $title, $images, $variants, $characteristics, $path, $category1, $category2, $category3,
            $length, $width, $height, $depth, $thickness, $format, $material, $producer, $collection
        ];

        //Получаем товар
        $product = MySQL::sql("SELECT id FROM masterdom_products WHERE link='$product_link'");

        if ($product->num_rows) {

            $date_edit = MySQL::get_mysql_datetime();
            $types .= 's';
            $values[] = $date_edit;

            $id = mysqli_fetch_assoc($product)['id'];
            $query = "UPDATE masterdom_products 
                    SET `link`=?, `stock`=?, `price`=?,
                    `edizm`=?, `articul`=?, `title`=?, `images`=?, `variants`=?,
                    `characteristics`=?, `path`=?, `category1`=?, `category2`=?,
                    `category3`=?, `length`=?, `width`=?, `height`=?, `depth`=?, 
                    `thickness`=?, `format`=?, `material`=?, `producer`=?, 
                    `collection`=?, `date_edit`=?
                    WHERE id=$id";
        } else {
            $query = "INSERT INTO masterdom_products
    (`link`, `stock`, `price`, `edizm`, `articul`, `title`, `images`, `variants`, `characteristics`, `path`, `category1`, `category2`, `category3`, 
    `length`, `width`, `height`, `depth`, `thickness`, `format`, `material`, `producer`, `collection`) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        }

        try {
            MySQL::bind_sql($query, $types, $values);
            echo "<b>не возникло ошибок с добавлением продукта в БД</b><br><br>";
        } catch (Exception $e) {
            Logs::writeLog($e);
            echo "<b>возникла ошибка с добавлением продукта в БД:</b><br>" . $e->getMessage() . '<br><br>';
        }
    }
} catch (\Throwable $e) {
    Logs::writeLog($e);
    TechInfo::errorExit($e);
    var_dump($e);
}

TechInfo::end();
