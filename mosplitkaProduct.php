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
    for ($i = 1; $i < 21; $i++) {

        echo "<br><b>Товар $i</b><br><br>";


        //Получаем ссылку, с которой будем парсить
        $query = MySQL::sql("SELECT link, product_views, provider FROM all_links WHERE type='product' and provider='mosplitka' ORDER BY product_views, id LIMIT 1");

        if (!$query->num_rows) {
            // Logs::writeCustomLog("не получено ссылки для парсинга", $provider);
            TechInfo::errorExit("не получено ссылки для парсинга");
        }
        $res = mysqli_fetch_assoc($query);

        //Получаем ссылку
        $url_parser = $res['link'];
        $provider = $res['provider'];
        TechInfo::whichLinkPass($url_parser);

        if ($provider == 'dplintus' and $i > 10) continue; //банят если много запросов

        //Увеличиваем просмотры ссылки
        $views = $res['product_views'] + 1;
        $date_edit = MySQL::get_mysql_datetime();
        MySQL::sql("UPDATE all_links SET product_views=$views, date_edit='$date_edit' WHERE link='$url_parser'");

        //Получаем html страницы
        if ($provider == 'tdgalion' or $provider == 'surgaz') $encoding = "windows-1251";
        $document = Parser::guzzleConnect($url_parser, $encoding ?? null);

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
        } else {
            $all_product_data = [];

            $all_product_data['link'] = [$url_parser, 's'];
            $all_product_data['provider'] = [$provider, 's'];

            include "all_attributes.php";

            include "insert_ending.php";
        }
    } //конец итерации 1 товара (для сургаза стоит break, выгружает по 100 товаров с 1 ссылки)

} catch (\Throwable $e) { //конец глобального try
    // Logs::writeLog1($e,  $provider, $url_parser);
    TechInfo::errorExit($e);
    var_dump($e);
}

TechInfo::end();
