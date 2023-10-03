<?php
require "functions.php";
require __DIR__ . "/vendor/autoload.php";

use DiDom\Document;
use GuzzleHttp\Client as GuzzleClient;

try {
    sleep(mt_rand(5, 10));

    //Получаем ссылку, с которой будем парсить
    $query = sql("SELECT link, views FROM links WHERE type='catalog' ORDER BY views, id LIMIT 1");
    if ($query->num_rows) {
        $res = mysqli_fetch_assoc($query);
        $url = $res['link'];
        $views = $res['views'] + 1;
        sql("UPDATE links SET views=$views WHERE link='$url'");
    } else {
        $url = "https://mosplitka.ru/catalog"; //для самого первого запуска
    }

    //Получаем html у себя
    $client = new GuzzleClient();
    $response = $client->request(
        'GET',
        $url
    );
    echo "скрипт проходил ссылку $url<br><br>";

    //Если проблема с ссылкой отправляем лог в БД и прекращаем работу скрипта
    if ($response->getStatusCode() != 200) {
        writeCustomLog("Код у GuzzleClient не 200. Ссылка, которую парсим - $url");
        exit();
    };

    //Получаем все данные со страницы
    $document = $response->getBody()->getContents();
    $document = new Document($document);

    $catalog_res = $document->find('a[href*=catalog]');
    $product_res = $document->find('a[href*=product]');
    $all_res = array_merge($catalog_res, $product_res);

echo "скрипт нашел ссылки: <br>";
    foreach ($catalog_res as $href) {
        $link = "https://mosplitka.ru" . $href->attr('href');
        echo "$link<br>";

        //добавляем ссылки с type=catalog
        if (preg_match("#https://mosplitka.ru/catalog.+#", $link) and !preg_match("#.php$#", $link)) {
            try {
                sql("INSERT INTO links (link, views, type, product_views) VALUES ('$link', 0, 'catalog', 0)");
                echo "была добавлена ссылка $link<br><br>";
            } catch (Exception $e) {
                continue; //для дублей
                }

            //добавляем ссылки с type=product
        } elseif (preg_match("#https://mosplitka.ru/product.+#", $link)) {
            try {
                $link = mysqli_real_escape_string(getDB(), $link);
                sql("INSERT INTO links (link, views, type, product_views) VALUES ('$link', 0, 'product', 0)");
                echo "была добавлена ссылка $link<br><br>";
            } catch (Exception $e) {
                continue; //для дублей
            }
        }
    }
    
        echo "не было ошибок<br><br>";
} catch (Throwable $e) {
    writeLog($e);
    echo "была ошибка<br><br>";
    var_dump($e);
}
