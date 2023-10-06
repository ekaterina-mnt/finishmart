<?php

use DiDom\Document;
use functions\MySQL;
use functions\Logs;
use functions\Modes_1c;
use functions\TechInfo;
use GuzzleHttp\Client as GuzzleClient;

TechInfo::start();

try {
    //Получаем ссылку, с которой будем парсить
    try {
        $query = MySQL::sql("SELECT link, views FROM links WHERE type='catalog' ORDER BY views, id LIMIT 1");
    } catch (Throwable $e) {
        //Если too_many_connections
        TechInfo::errorExit($e);
    }
    if ($query->num_rows) {
        $res = mysqli_fetch_assoc($query);
        $url = $res['link'];
        $views = $res['views'] + 1;
        MySQL::sql("UPDATE links SET views=$views WHERE link='$url'"); 
    } else {
        $url = "https://mosplitka.ru/catalog"; //для самого первого запуска
    }
    
    echo '<b>скрипт проходил ссылку <a href="' . $url . '">' . $url . '</a></b><br><br>';
} catch (Throwable $e) {
    Logs::writeLog($e);
    echo "<b>была ошибка</b><br><br>";
    var_dump($e);
}

TechInfo::end();