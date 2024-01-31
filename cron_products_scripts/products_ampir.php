<?php
require __DIR__ . "/../vendor/autoload.php";

use functions\MySQL;
use functions\Logs;
use functions\TechInfo;
use functions\Categories;
use functions\Parser;
use functions\Connect;
use functions\ParserMasterdom;
use functions\ParserMosplitka;


//МЕНЯТЬ ТОЛЬКО ЗДЕСЬ
$script_iteration_provider = 'ampir';
//


TechInfo::start();

try {
    for ($i = 1; $i < 6; $i++) {
        sleep(mt_rand(2, 6));
        if ($script_iteration_provider == 'domix' and $i > 8) TechInfo::errorExit("");

        echo "<br><b>Товар $i</b><br><br>";

        // Получаем ссылку, с которой будем парсить
        $query = MySQL::sql("SELECT link, product_views, provider FROM all_links WHERE type='product' and provider='" . $script_iteration_provider . "' ORDER BY product_views, id LIMIT 1");

        if (!$query->num_rows) {
            TechInfo::errorExit("не получено ссылки для парсинга");
        }
        $res = mysqli_fetch_assoc($query);

        // Получаем ссылку

        $url_parser = $res['link'];
        $provider = $res['provider'];

        // $links = MySQL::sql("SELECT link, provider from all_products WHERE provider='" . $script_iteraion_provider . "' and subcategory IS NULL or subcategory='null' ORDER BY date_edit LIMIT 10");
        // foreach ($links as $link) {
        // sleep(mt_rand(2, 6));

        // echo "<br><b>Товар $i</b><br><br>";

        // $url_parser = $link['link'];
        // $provider = $link['provider'];

        $date_edit = MySQL::get_mysql_datetime();

        // if ($provider == 'dplintus' and $i > 10) continue; //банят если много запросов

        //Увеличиваем просмотры ссылки
        $views = $res['product_views'] + 1;
        MySQL::sql("UPDATE all_links SET product_views=$views, date_edit='$date_edit' WHERE link='$url_parser'"); //для FromLinksTable

        TechInfo::whichLinkPass($url_parser);
        if ($provider == 'alpinefloor') {
            if (Parser::discardInvalideAlpinefloorLink($url_parser)) {
                echo "Не продукт";
                continue;
            }
        }

        //Получаем html страницы
        if ($provider == 'tdgalion' or $provider == 'surgaz') $encoding = "windows-1251";
        try {
            $document = Connect::guzzleConnect($url_parser, $encoding ?? null);
            MySQL::sql("UPDATE all_products SET status='ok', date_edit='$date_edit' WHERE link='$url_parser'");
        } catch (\Throwable $e) {
            MySQL::sql("UPDATE all_products SET status='invalide', date_edit='$date_edit' WHERE link='$url_parser'");
            Logs::writeLog1($e,  $provider, $url_parser);
            TechInfo::errorExit($e);
            var_dump($e);
        }

        if ($provider == 'surgaz') {
            include __DIR__ . "/../surgaz_attributes.php";
            break; //выход из цикла для получения новых ссылок, т.к. выгружает по 100 товаров с 1 ссылки
        } elseif ($provider == 'centerkrasok') {
            include __DIR__ . "/../centerkrasok_attributes.php";
        } elseif ($provider == 'artkera') {
            include __DIR__ . "/../artkera_attributes.php";
        } elseif ($provider == 'evroplast') {
            include __DIR__ . "/../evroplast_attributes.php";
        } elseif ($provider == 'masterdom') {
            include __DIR__ . "/../masterdom_attributes.php";
        } else {
            $all_product_data = [];

            $all_product_data['link'] = [$url_parser, 's'];
            $all_product_data['provider'] = [$provider, 's'];

            include __DIR__ . "/../all_attributes.php";

            include __DIR__ . "/../insert_ending.php";
        }
    } //конец итерации 1 товара

} catch (\Throwable $e) { //конец глобального try
    Logs::writeLog1($e,  $provider, $url_parser);
    TechInfo::errorExit($e);
    var_dump($e);
}

TechInfo::end();
