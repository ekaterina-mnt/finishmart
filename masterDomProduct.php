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
    // $url_parser = "https://oboi.masterdom.ru/find/?sort=popular&offset=0";
    $url_parser = "https://api.masterdom.ru/api/rest/tile/search.json?sort=popularity_desc&limit=100&offset=2000";
    // $url_parser = "https://api.masterdom.ru/api/rest/bathrooms/sink/search.json?sort=popularity_desc&limit=100&offset=0";
    // $url_parser = "https://api.masterdom.ru/api/rest/bathrooms/toilet_bidet/search.json?sort=popularity_desc&limit=100&offset=0";
    // $url_parser = "https://api.masterdom.ru/api/rest/bathrooms/bathtub/search.json?sort=popularity_desc&limit=100&offset=0";
    // $url_parser = "https://api.masterdom.ru/api/rest/bathrooms/shower/search.json?sort=popularity_desc&limit=100&offset=0";
    // $url_parser = "https://api.masterdom.ru/api/rest/bathrooms/faucet/search.json?sort=popularity_desc&limit=100&offset=0";
    // $url_parser = "https://api.masterdom.ru/api/rest/bathrooms/furniture/search.json?sort=popularity_desc&limit=100&offset=0";
    // $url_parser = "https://api.masterdom.ru/api/rest/bathrooms/accessories/search.json?sort=popularity_desc&limit=100&offset=0";
    // $url_parser = "https://santehnika.masterdom.ru/polotencesushitely/catalog/";
    // $url_parser = "https://api.masterdom.ru/api/rest/bathrooms/parts/search.json?sort=popularity_desc&limit=100&offset=0";


    $provider = Parser::getProvider($url_parser);

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

    if (str_contains($url_parser, 'polotencesushitely/catalog')) {
        $country_coll_producer_res = ParserMasterdom::getDataPolotencesushitely();
        $api_data = $country_coll_producer_res['api_data'];

        $category = 'Сантехника';
    } else {
        //категория
        $category = ParserMasterdom::getCategory($url_parser);

        //Нужные массивы до цикла (для плитки и сантехники)
        switch ($category) {
            case "Плитка и керамогранит":
                $country_coll_producer_res = ParserMasterdom::getDataPlitka();
                break;
            case "Сантехника":
                $country_coll_producer_res = ParserMasterdom::getDataSantechnika();
                break;
            case "Обои и настенные покрытия":
                $country_coll_producer_res = ParserMasterdom::getDataOboi();
                break;
        }

        //Начинаем вытаскивать нужные данные
        $api_data = Parser::getApiData($document);
    }

    $fabrics = isset($country_coll_producer_res['fabrics']) ? $country_coll_producer_res['fabrics'] : null;
    $collections = isset($country_coll_producer_res['collections']) ? $country_coll_producer_res['collections'] : null;
    $countries = isset($country_coll_producer_res['countries']) ? $country_coll_producer_res['countries'] : null;
    $product_usages_array = isset($country_coll_producer_res['product_usages']) ? $country_coll_producer_res['product_usages'] : null;






    //НЕПОСРЕДСТВЕННАЯ ОБРАБОТКА ПОЛУЧЕННЫХ ДАННЫХ


    echo "<b>Всего товаров в ссылке:</b> " . count($api_data) . " шт.<br><br>";
    $product_ord_num = 1;
    foreach ($api_data as $datum) {
        echo "<br><b>Товар " . $product_ord_num++ . "</b><br><br>";
        $all_product_data = [];

        //ОБЩИЕ ДЛЯ ВСЕХ

        //название товара (сантехника - full_name)
        $title = (isset($datum['fullname']) ? $datum['fullname'] : $datum['full_name']) ?? null;
        $all_product_data['название'] = [$title, 's'];

        //артикул
        $articul = $datum['article'] ?? null;
        $all_product_data['артикул'] = [$articul, 's'];

        //категория
        $all_product_data['категория'] = [$category, 's'];

        //подкатегория
        $subcategory = ParserMasterdom::getSubcategory($category, $datum) ?? null;
        $all_product_data['подкатегория'] = [$subcategory, 's'];

        //ссылка на товар
        $product_id = $datum['id'] ?? null;
        $name_url = $datum['name_url'] ?? null;
        $product_link = ParserMasterdom::getProductLink($subcategory, $articul, $product_id, $name_url);
        $all_product_data['ссылка на товар'] = [$product_link, 's'];

        //цена
        $price = (isset($datum['price_site']) ? $datum['price_site'] : $datum['price']) ?? null;
        $all_product_data['цена'] = [$price, 'i'];


        //единица измерения
        $edizm = ParserMasterdom::getEdizm($category) ?? null;
        $all_product_data['единица измерения'] = [$edizm, 's'];

        //остатки товара
        $stock = isset($datum['balance']) ? $datum['balance'] : null;
        $all_product_data['остатки товара'] = [$stock, 'i'];

        //страна
        $country_key = (isset($datum['country']) ? $datum['country'] : $datum['data']['country']) ?? null;
        $country = $countries[$country_key]['name'] ?? null;
        $all_product_data['страна'] = [$country, 's'];

        //производитель
        switch ($category) {
            case "Сантехника":
            case "Плитка и керамогранит":
                $producer_key = $datum['fabric'] ?? null;
                $producer = $fabrics[$producer_key]['name'] ?? null;
                break;
            default:
                $producer = (isset($datum['fabric_name']) ? $datum['fabric_name'] : $datum['fabric']) ?? null;
                break;
        }
        $all_product_data['производитель'] = [$producer, 's'];

        //коллекция
        switch ($category) {
            case "Сантехника":
            case "Плитка и керамогранит":
                $collection_key = $datum['collection'] ?? null;
                $collection = $collections[$collection_key]['name'] ?? null;
                break;
            default:
                $collection = $datum['collection_name'];
                break;
        }
        $all_product_data['коллекция'] = [$collection, 's'];

        //провайдер
        $providerID = $provider['id'] ?? null;
        $all_product_data['провайдер_ID'] = [$providerID, 'i'];

        //длина
        $length = $datum['length'] ?? null;
        $all_product_data['длина'] = [$length, 'd'];

        //ширина
        $width = $datum['width'] ?? null;
        $all_product_data['ширина'] = [$width, 'd'];

        //высота
        $height = $datum['height'] ?? null;
        $all_product_data['высота'] = [$height, 'd'];

        //глубина
        $depth = null;
        $all_product_data['глубина'] = [$depth, 'd'];

        //толщина
        $thickness = null;
        $all_product_data['толщина'] = [$thickness, 'd'];

        //формат
        $format = null;
        $all_product_data['формат'] = [$format, 's'];

        //материал
        $material = $datum['type'] ?? null;
        $all_product_data['материал'] = [$material, 's'];

        //картинки
        $images = ParserMasterdom::getImages($datum, $url_parser);
        $all_product_data['картинки'] = [$images, 's'];

        //варианты исполнения
        $variants = null;
        $all_product_data['варианты исполнения'] = [$variants, 's'];

        //СПЕЦИФИЧЕСКИЕ АТРИБУТЫ
        $product_usages_keys = $datum['product_usages'] ?? null;
        if ($product_usages_keys) {
            $product_usages = [];
            foreach ($product_usages_keys as $usage_i) {
                $product_usages[$usage_i] = $product_usages_array[$usage_i]['name'];
            }
            $product_usages = json_encode($product_usages, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        $product_usages = $product_usages ?? null;


        //все характеристики
        $characteristics = json_encode($datum, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $all_product_data['все характеристики'] = [$characteristics, 's'];







        echo "<b>итоговые данные, которые мы спарсили:</b><br><br>";
        // TechInfo::preArray($all_product_data);




        try {
            if (!$product_link) {
                throw new \Exception();
            }
        } catch (\Throwable $e) {
            Logs::writeLinkLog("Не удалось найти ссылку для товара с артикулом $articul", $articul, $provider['name'], $parser_link);
            TechInfo::errorExit($e);
        }


        //Для передачи в MySQL

        $types = '';
        $values = array();
        foreach ($all_product_data as $n) {
            $types .= $n[1];
            $values[] = $n[0];
        }


        Parser::insertProductData($types, $values, $product_link);
    }
} catch (\Throwable $e) {
    var_dump($e);
    echo "выше <br><br>";
    // Logs::writeLog($e);
    // TechInfo::errorExit($e);
    // var_dump($e);
}

TechInfo::end();
