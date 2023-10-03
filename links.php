<?php
require "functions.php";
require __DIR__ . "/vendor/autoload.php";

use DiDom\Document;
use GuzzleHttp\Client as GuzzleClient;

echo "<b>скрипт начал работу " . date("d-m-Y H:i:s", time()) . "</b><br><br>";

try {
    //Получаем ссылку, с которой будем парсить
    try {
        $query = sql("SELECT link, views FROM links WHERE type='catalog' ORDER BY views, id LIMIT 1");
    } catch (Throwable $e) {
        //Если too_many_connections
        echo "<b>ошибка: </b>";
        var_dump($e);
        echo "<br><br><b>скрипт закончил работу " . date("d-m-Y H:i:s", time()) . "</b><br><br>";
        exit();
    }
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
    echo '<b>скрипт проходил ссылку <a href="' . $url . '">' . $url . '</a></b><br><br>';

    //Если проблема с ссылкой отправляем лог в БД и прекращаем работу скрипта
    if ($response->getStatusCode() != 200) {
        writeCustomLog("Код у GuzzleClient не 200. Ссылка, которую парсим - $url");
        echo "<b>ошибка: код ссылки != 200</b><br><br>";
        echo "<b>скрипт закончил работу " . date("d-m-Y H:i:s", time()) . "</b><br><br>";
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
    foreach ($all_res as $href) {
        $link = "https://mosplitka.ru" . $href->attr('href');
        echo "$link<br>";

        //избавляемся от лишних ссылок
        $divided_link = array_slice(explode("/", $link), 4);
        if (!(strpos($link, "PAGEN") or in_array(count($divided_link), [1, 2]))) {
            continue;
        }

        //избавляемся от дублей
        if (sql("SELECT id, link FROM links WHERE link='$link'")->num_rows) {
            continue;
        };

        //определяем это ссылка на продукт или каталог
        if (preg_match("#https://mosplitka.ru/catalog.+#", $link) and !preg_match("#.php$#", $link)) {
            $type = 'catalog';
        } elseif (preg_match("#https://mosplitka.ru/product.+#", $link)) {
            $type = 'product';
        }

        //добавляем ссылку в БД
        if (isset($type)) {
            try {
                $link = mysqli_real_escape_string(getDB(), $link);
                sql("INSERT INTO links (link, views, type, product_views) VALUES ('$link', 0, '$type', 0)");
                $add[] = $link;
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
