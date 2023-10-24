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


    $provider = Parser::getProvider($url_parser);

    TechInfo::whichLinkPass($url_parser);

    //Увеличиваем просмотры ссылки
    $views = $res['product_views'] + 1;
    $date_edit = MySQL::get_mysql_datetime();
    MySQL::sql("UPDATE masterdom_links SET product_views=$views, date_edit='$date_edit' WHERE link='$url_parser'"); //поменять имя таблицы

    //Получаем html страницы
    // try {
    // $document = Parser::guzzleConnect($url_parser);

} catch (\Throwable $e) {
    Logs::writeLog($e);
    TechInfo::errorExit($e);
    var_dump($e);
}

TechInfo::end();
