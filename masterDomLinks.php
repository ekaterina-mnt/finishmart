<?php
require __DIR__ . "/vendor/autoload.php";

use DiDom\Document;
use functions\MySQL;
use functions\Logs;
use functions\Modes_1c;
use functions\TechInfo;
use GuzzleHttp\Client as GuzzleClient;

TechInfo::start();

try {
    $provider = 'masterdom';

    //Получаем ссылку, с которой будем парсить
    try {
        $query = MySQL::sql("SELECT link, views FROM masterdom_links WHERE type='catalog' ORDER BY views, id LIMIT 1");
    } catch (Throwable $e) {
        //Если too_many_connections
        TechInfo::errorExit($e);
    }

    if ($query->num_rows) {
        $res = mysqli_fetch_assoc($query);
        $url = $res['link'];
        $views = $res['views'] + 1;
        MySQL::sql("UPDATE masterdom_links SET views=$views WHERE link='$url'");
    } else {
        $url = "https://masterdom.ru"; //для самого первого запуска
    }

    echo '<b>скрипт проходил ссылку <a href="' . $url . '">' . $url . '</a></b><br><br>';

    //Получаем html у себя
    try {
        $client = new GuzzleClient();
        $response = $client->request(
            'GET',
            $url
        );
    } catch (Throwable $e) {
        //Если проблема с ссылкой (чаще всего 502) отправляем лог в БД 
        Logs::writeLog($e);

        //снова уменьшаем просмотры, чтобы скрипт еще раз прошел ссылку и прекращаем работу скрипта
        $views -= 1;
        MySQL::sql("UPDATE links SET views=$views WHERE link='$url'");
        TechInfo::errorExit($e);
    }

    //Получаем все данные со страницы
    $document = $response->getBody()->getContents();
    $document = new Document($document);


    $all_res = array_merge(
        $document->find('a[href*=catalog]'),
        $document->find('a[href*=product]'),
        $document->find('li[class*=menu] a')
    );

    echo "<b>скрипт нашел ссылки (" . count($all_res) . "шт):</b><br>";
    $add = [];
    foreach ($all_res as $href) {
        $attr_href = $href->attr('href');

        if (!$attr_href) continue;

        if (str_contains($attr_href, 'http')) {
            $link = $attr_href;
        } else {
            $link = 'https:' . $attr_href;
        }
        echo "$link<br>";
    }
} catch (Throwable $e) {
    Logs::writeLog($e);
    echo "<b>была ошибка</b><br><br>";
    var_dump($e);
}

TechInfo::end();
