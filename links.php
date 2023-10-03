<?php
require "functions.php";
require __DIR__ . "/vendor/autoload.php";

use DiDom\Document;
use GuzzleHttp\Client as GuzzleClient;

echo "<b>скрипт начал работу " . date("d-m-Y H:i:s", time()) . "</b><br><br>";

try {
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
    echo '<b>скрипт проходил ссылку <a href="$url">' . $url . '</a></b><br><br>';

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

    echo "<b>скрипт нашел ссылки:</b><br>";
    $add = [];
    foreach ($catalog_res as $href) {
        $link = "https://mosplitka.ru" . $href->attr('href');
        echo "$link<br>";

        //добавляем ссылки с type=catalog
        if (preg_match("#https://mosplitka.ru/catalog.+#", $link) and !preg_match("#.php$#", $link)) {
            try {
                sql("INSERT INTO links (link, views, type, product_views) VALUES ('$link', 0, 'catalog', 0)");
                $add[] = $link;
            } catch (Exception $e) {
                continue; //для дублей
            }

            //добавляем ссылки с type=product
        } elseif (preg_match("#https://mosplitka.ru/product.+#", $link)) {
            try {
                $link = mysqli_real_escape_string(getDB(), $link);
                $add[] = $link;
                sql("INSERT INTO links (link, views, type, product_views) VALUES ('$link', 0, 'product', 0)");
            } catch (Exception $e) {
                continue; //для дублей
            }
        }
    }
    echo "<br><b>из них скрипт добавил:</b><br>";
    foreach ($add as $i) {
        echo "$i<br>";
    }
    echo "<br><b>не было ошибок</b><br><br>";
} catch (Throwable $e) {
    writeLog($e);
    echo "<b>была ошибка</b><br><br>";
    var_dump($e);
}


echo "<b>скрипт закончил работу " . date("d-m-Y H:i:s", time()) . "</b><br><br>";
