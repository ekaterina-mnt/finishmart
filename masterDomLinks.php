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
        $url_parser = $res['link'];
        $views = $res['views'] + 1;
        MySQL::sql("UPDATE masterdom_links SET views=$views WHERE link='$url_parser'");
    } else {
        $url_parser = "https://masterdom.ru"; //для самого первого запуска
    }

    //отдельно обычной проходкой https://santehnika.masterdom.ru/polotencesushitely/catalog/
    //шторы не нужны
    //нужны https://oboi.masterdom.ru/#!srt=popular&v=list&lim=60
    //нужны https://svet.masterdom.ru/#!srt=popular&v=list&lim=120

    $api_json = [
        'https://api.masterdom.ru/api/rest/tile/search.json?partial_match=true&sort=popularity_desc&signs[]=&limit=500&offset=0',
        'https://api.masterdom.ru/api/rest/bathrooms/sink/search.json?partial_match=false&sort=popularity_desc&signs[]=&category_name[]=sink&limit=500&offset=0',
        'https://api.masterdom.ru/api/rest/bathrooms/toilet_bidet/search.json?partial_match=false&sort=popularity_desc&signs[]=&category_name[]=toilet_bidet&limit=500&offset=0',
        'https://api.masterdom.ru/api/rest/bathrooms/bathtub/search.json?partial_match=false&sort=popularity_desc&signs[]=&category_name[]=bathtub&limit=500&offset=0',
        'https://api.masterdom.ru/api/rest/bathrooms/shower/search.json?partial_match=false&sort=popularity_desc&signs[]=&category_name[]=shower&limit=500&offset=0',
        'https://api.masterdom.ru/api/rest/bathrooms/faucet/search.json?partial_match=false&sort=popularity_desc&signs[]=&category_name[]=faucet&limit=500&offset=0',
        'https://api.masterdom.ru/api/rest/bathrooms/furniture/search.json?partial_match=false&sort=popularity_desc&signs[]=&category_name[]=furniture&limit=500&offset=0',
        'https://api.masterdom.ru/api/rest/bathrooms/accessories/search.json?partial_match=false&sort=popularity_desc&signs[]=&category_name[]=accessories&limit=500&offset=0',
        'https://api.masterdom.ru/api/rest/bathrooms/parts/search.json?partial_match=false&sort=popularity_desc&signs[]=&category_name[]=parts&limit=500&offset=0',
    ];

    $url_parser = $api_json[0]; //test

    echo '<b>скрипт проходил ссылку <a href="' . $url_parser . '">' . $url_parser . '</a></b><br><br>';

    //Получаем html у себя
    try {
        $client = new GuzzleClient();
        $response = $client->request(
            'GET',
            $url_parser
        );
    } catch (Throwable $e) {
        //Если проблема с ссылкой (чаще всего 502) отправляем лог в БД 
        Logs::writeLog($e);

        //снова уменьшаем просмотры, чтобы скрипт еще раз прошел ссылку и прекращаем работу скрипта
        $views -= 1;
        MySQL::sql("UPDATE links SET views=$views WHERE link='$url_parser'");
        TechInfo::errorExit($e);
    }

    //Получаем все данные со страницы
    $document = $response->getBody()->getContents();
    $document = new Document($document);

    var_dump(json_decode($document->text())->tiles[0]);

} catch (Throwable $e) {
    Logs::writeLog($e);
    echo "<b>была ошибка</b><br><br>";
    var_dump($e);
}

TechInfo::end();
