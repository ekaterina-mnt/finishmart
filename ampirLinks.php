<?php
require __DIR__ . "/vendor/autoload.php";

use DiDom\Document;
use functions\MySQL;
use functions\ParserMosplitka;
use functions\Parser;
use functions\Logs;
use functions\Modes_1c;
use functions\ParserMasterdom;
use functions\TechInfo;
use GuzzleHttp\Client as GuzzleClient;

TechInfo::start();

try {
    $provider = 'ampir';

    //Получаем ссылку, с которой будем парсить
    $query = MySQL::sql("SELECT link, views FROM " . $provider . "_links WHERE type='catalog' ORDER BY views, id LIMIT 1");

    if (!$query->num_rows) {
        MySQL::firstLinksInsert($provider); //для самого первого запуска
        TechInfo::errorExit("первый запрос, добавлены первичные ссылки для парсинга (или нет ссылок с типом `каталог`)");
    }

    $res = mysqli_fetch_assoc($query);

    //Получаем ссылку
    $url_parser = $res['link'];
    // $url_parser = "https://www.ampir.ru/catalog/rozetki/page1/";

    TechInfo::whichLinkPass($url_parser);

    //Увеличиваем просмотры ссылки
    $views = $res['views'] + 1;
    $date_edit = MySQL::get_mysql_datetime();
    MySQL::sql("UPDATE " . $provider . "_links SET views=$views, date_edit='$date_edit' WHERE link='$url_parser'");

    //Получаем html у себя
    try {
        $document = Parser::guzzleConnect($url_parser);

        //Получаем все данные со страницы
        $catalog_res = $document->find('.product-list-block a[href*=product], .catSection a[href*=product], .brand__row a[href*=catalog]');
        $product_res = $document->find('.pagination-catalog a[href*=catalog], .pagination-list a[href*=catalog]');
        $all_res = array_merge($catalog_res, $product_res);

        echo "<b>скрипт нашел ссылки (" . count($all_res) . "шт):</b><br>";

        $add = [];
        foreach ($all_res as $href) {
            $link = $href->attr('href'); //отличается от мосплитки
            echo "$link<br>";

            //избавляемся от дублей
            if (MySQL::sql("SELECT id, link FROM " . $provider . "_links WHERE link='$link'")->num_rows) continue;

            //определяем это ссылка на продукт или каталог
            $link_type = ParserMosplitka::getLinkType($link);
            if (!$link_type) continue;           

            $res = Parser::insertLink($link, $link_type, $provider);
            if ($res == "success") $add[] = $link;
            if ($res == "fail") $add[] = $link . ' - не получилось добавить в БД'; 
        }
        sort($add);
        echo "<br><b>из них скрипт добавил (" . count($add) . "шт):</b><br>";
        foreach ($add as $n => $i) {
            echo $n + 1 . ") $i<br>";
        }
        echo "<br><b>не было ошибок</b><br><br>";
    } catch (Throwable $e) {
        MySQL::decreaseViews($views, $url_parser, $provider);
        // Logs::writeLog($e, $provider);
     TechInfo::errorExit($e);
    }
    
} catch (\Throwable $e) {
    // Logs::writeLog($e, $provider);
    // TechInfo::errorExit($e);
    var_dump($e);
}

TechInfo::end();
