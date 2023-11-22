<?php
require __DIR__ . "/vendor/autoload.php";

use functions\MySQL;
use functions\Logs;
use functions\TechInfo;
use functions\Parser;
use functions\ParserMasterdom;
use functions\ParserMosplitka;


//ПОМЕНЯТЬ ЗАПРОС MYSQL В САМОМ НАЧАЛЕ + ЗАХАРДКОЖЕННУЮ ССЫЛКУ И ПРОВАЙДЕРА


TechInfo::start();

try {
    for ($i = 1; $i < 2; $i++) { //тест
        sleep(mt_rand(2, 6));

        echo "<br><b>Товар $i</b><br><br>";


        //Получаем ссылку, с которой будем парсить
        $query = MySQL::sql("SELECT link, product_views, provider FROM all_links WHERE type='product' and provider='dplintus' ORDER BY product_views, id LIMIT 1");

        if (!$query->num_rows) {
            // Logs::writeCustomLog("не получено ссылки для парсинга", $provider);
            TechInfo::errorExit("не получено ссылки для парсинга");
        }
        $res = mysqli_fetch_assoc($query);

        //Получаем ссылку

        $url_parser = $res['link'];
        $provider = $res['provider'];


        $links = MySQL::sql("SELECT link, provider from all_products WHERE provider='dplintus' and subcategory IS NULL or subcategory='null' ORDER BY date_edit LIMIT 10"); //тест
        foreach ($links as $link) { //тест
            sleep(mt_rand(2, 6)); //тест
            $url_parser = $link['link']; //тест
            $provider = $link['provider']; //тест

            TechInfo::whichLinkPass($url_parser);
echo "here";
            if ($provider == 'dplintus' and $i > 10) continue; //банят если много запросов

            //Увеличиваем просмотры ссылки
            $views = $res['product_views'] + 1;
            $date_edit = MySQL::get_mysql_datetime();
            // MySQL::sql("UPDATE all_links SET product_views=$views, date_edit='$date_edit' WHERE link='$url_parser'"); //тест

            //Получаем html страницы
            if ($provider == 'tdgalion' or $provider == 'surgaz') $encoding = "windows-1251";
            try {
                echo "here2";
                $document = Parser::guzzleConnect($url_parser, $encoding ?? null);
                MySQL::sql("UPDATE all_products SET status='ok', date_edit='$date_edit' WHERE link='$url_parser'");
            } catch (Throwable $e) {
                MySQL::sql("UPDATE all_products SET status='invalide', date_edit='$date_edit' WHERE link='$url_parser'");
                Logs::writeLog1($e,  $provider, $url_parser);
                TechInfo::errorExit($e);
                var_dump($e);
            }
echo "here3";
            if ($provider == 'surgaz') {
                include "surgaz_attributes.php";
                break; //выход из цикла для получения новых ссылок, т.к. выгружает по 100 товаров с 1 ссылки
            } elseif ($provider == 'centerkrasok') {
                include "centerkrasok_attributes.php";
            } elseif ($provider == 'artkera') {
                include "artkera_attributes.php";
            } elseif ($provider == 'evroplast') {
                include "evroplast_attributes.php";
            } elseif ($provider == 'mosplitka') {
                include "mosplitka_attributes.php";
            } elseif ($provider == 'masterdom.php') {
                include "masterdom_attributes.php";
            } else {
                $all_product_data = [];

                $all_product_data['link'] = [$url_parser, 's'];
                $all_product_data['provider'] = [$provider, 's'];

                include "all_attributes.php";

                include "insert_ending.php";
            }
        } //тест
    } //конец итерации 1 товара (для сургаза стоит break, выгружает по 100 товаров с 1 ссылки)

} catch (\Throwable $e) { //конец глобального try
    Logs::writeLog1($e,  $provider, $url_parser);
    TechInfo::errorExit($e);
    var_dump($e);
}

TechInfo::end();
