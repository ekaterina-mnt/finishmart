<?php
require __DIR__ . "/vendor/autoload.php";

use DiDom\Document;
use functions\MySQL;
use functions\Logs;
use functions\Modes_1c;
use functions\Parser;
use functions\TechInfo;
use GuzzleHttp\Client as GuzzleClient;

TechInfo::start();

try {
    $provider = 'masterdom';

    //Получаем ссылку, с которой будем парсить
    $query = MySQL::sql("SELECT link, views FROM masterdom_links WHERE type='catalog' ORDER BY views, id LIMIT 1");

    if (!$query->num_rows) {
        MySQL::firstLinksInsert(); //для самого первого запуска
        TechInfo::errorExit("первый запрос, добавлены первичные ссылки для парсинга (или нет ссылок с типом `каталог`)");
    }

    $res = mysqli_fetch_assoc($query);

    //Получаем ссылку
    $url_parser = $res['link'];
    TechInfo::whichLinkPass($url_parser);

    //Увеличиваем просмотры ссылки
    $views = $res['views'] + 1;
    $date_edit = MySQL::get_mysql_datetime();
    MySQL::sql("UPDATE masterdom_links SET views=$views, date_edit='$date_edit' WHERE link='$url_parser'");

    //Получаем html у себя
    try {
        $document = Parser::guzzleConnect($url_parser);
    } catch (Throwable $e) {
        //Снова уменьшаем просмотры, чтобы скрипт потом еще раз прошел ссылку и прекращаем работу скрипта
        $views -= 1;
        $date_edit = MySQL::get_mysql_datetime();
        MySQL::sql("UPDATE links SET views=$views date_edit='$date_edit' WHERE link='$url_parser'");
        Logs::writeLog($e);
        TechInfo::errorExit($e);
    }
} catch (\Throwable $e) {
    Logs::writeLog($e);
    TechInfo::errorExit($e);
    var_dump($e);
}

TechInfo::end();
