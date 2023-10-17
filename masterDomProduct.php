<?php
require __DIR__ . "/vendor/autoload.php";

use functions\MySQL;
use functions\Logs;
use functions\TechInfo;
use functions\Parser;
use functions\ParserMasterdom;

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
    // $url_parser = 'https://api.masterdom.ru/api/rest/tile/search.json?sort=popularity_desc&limit=100&offset=0';
    // $url_parser = "https://santehnika.masterdom.ru/polotencesushitely/catalog/";
    // $url_parser = 'https://oboi.masterdom.ru/find/?sort=popular&offset=0';
    $url_parser = "https://api.masterdom.ru/api/rest/bathrooms/furniture/search.json?sort=popularity_desc&limit=100&offset=0";
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

    //категория
    $category = ParserMasterdom::getCategory($url_parser);

    echo 'here';
    //Нужные массивы до цикла (для плитки и сантехники)
    switch ($category) {
        case "Плитка и керамогранит":
            $fabrics = ParserMasterdom::plitka('fabrics');
            $countries = ParserMasterdom::plitka('countries');
            $collections = ParserMasterdom::plitka('collections');
            break;
        case "Сантехника":
            $fabrics = ParserMasterdom::santechnika('fabrics');
            $countries = ParserMasterdom::santechnika('countries');
            $collections = ParserMasterdom::santechnika('collections');
            break;
    }


    //Начинаем вытаскивать нужные данные
    $api_data = Parser::getApiData($document);

    echo "<b>Всего товаров в ссылке:</b> " . count($api_data) . " шт.<br><br>";
    $product_ord_num = 1;
    foreach ($api_data as $datum) {
        echo "<br><b>Товар " . $product_ord_num++ . "</b><br><br>";

        //ОБЩИЕ ДЛЯ ВСЕХ

        //название товара (сантехника - full_name)
        $title = (isset($datum['fullname']) ? $datum['fullname'] : $datum['full_name']) ?? null;

        //цена
        $price = (isset($datum['price_site']) ? $datum['price_site'] : $datum['price']) ?? null;

        //артикул
        $articul = $datum['article'] ?? null;

        //ссылка на товар
        $product_id = $datum['id'] ?? null;
        $name_url = $datum['name_url'] ?? null;
        $product_link = ParserMasterdom::getProductLink($category, $articul, $product_id, $name_url) ?? null;

        //подкатегория
        $subcategory = ParserMasterdom::getSubcategory($product_link, $datum) ?? null;

        //единица измерения
        $edizm = ParserMasterdom::getEdizm($category) ?? null;

        //остатки товара
        $stock = isset($datum['balance']) ? $datum['balance'] : null;

        //страна
        switch ($category) {
            case "Обои и настельные покрытия":
                $country = $datum['country'];
                break;
            case "Сантехника" or "Плитка и керамогранит":
                $country_key = $datum['country'] ?? null;
                $country = $countries[$country_key]['name'] ?? null;
                break;
        }

        //производитель
        switch ($category) {
            case "Обои и настельные покрытия":
                $producer = $datum['fabric_name'];
                break;
            case "Сантехника" or "Плитка и керамогранит":
                $producer_key = $datum['fabric'] ?? null;
                $producer = $fabrics[$producer_key]['name'] ?? null;
                break;
        }

        //коллекция
        switch ($category) {
            case "Обои и настельные покрытия":
                $collection = $datum['collection_name'];
                break;
            case "Сантехника" or "Плитка и керамогранит":
                $collection_key = $datum['collection'] ?? null;
                $collection = $collections[$collection_key]['name'] ?? null;
                break;
        }

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
        $images = ParserMasterdom::getImages($datum, $url_parser);

        //все характеристики
        $characteristics = json_encode($datum, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        //варианты исполнения
        $variants = null;

        //СПЕЦИФИЧЕСКИЕ АТРИБУТЫ



        // Итоговый массив для проверки
        $arr = [
            "ссылка" => $product_link, "остатки" => $stock, "цена" => $price, "ед.изм" => $edizm, "артикул" => $articul,
            "название" => $title, "картинки" => $images, "варианты" => $variants, "характеристики" => $characteristics,
            "категория" => $category, "подкатегория" => $subcategory,
            "длина" => $length, "ширина" => $width, "высота" => $height, "глубина" => $depth, "толщина" => $thickness,
            "формат" => $format, "материал" => $material, "производитель" => $producer, "коллекция" => $collection,
            "страна" => $country,
        ];

        echo "<b>итоговые данные, которые мы спарсили:</b><br><br>";
        TechInfo::preArray($arr);

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
} catch (\Throwable $e) {
    Logs::writeLog($e);
    TechInfo::errorExit($e);
    var_dump($e);
}

TechInfo::end();
