<?php
require "functions.php";
require __DIR__ . "/vendor/autoload.php";

use DiDom\Document;
use GuzzleHttp\Client as GuzzleClient;

try {
    sleep(mt_rand(5,10));
    $query = sql("SELECT link, views FROM links WHERE type='catalog' ORDER BY views, id LIMIT 1");
    if ($query->num_rows) {
        $res = mysqli_fetch_assoc($query);
        $url = $res['link'];
        $views = $res['views'] + 1;
        sql("UPDATE links SET views=$views WHERE link='$url'");
    } else {
        $url = "https://mosplitka.ru/catalog"; //для самого первого запуска
    }

    $client = new GuzzleClient();
    $response = $client->request(
        'GET',
        $url,
        [
            'timeout' => 3.14,
            'verify' => false,
        ]
    );
    
    if ($response->getStatusCode() != 200) exit();

    $document = $response->getBody()->getContents();

    $document = new Document($document);

    $catalog_res = $document->find('a[href*=catalog]');
    $product_res = $document->find('a[href*=product]');

    foreach ($catalog_res as $href) {
        $link = "https://mosplitka.ru" . $href->attr('href');

        if (preg_match("#https://mosplitka.ru/catalog.+#", $link) and !preg_match("#.php$#", $link)) {
            try {
                sql("INSERT INTO links (link, views, type, product_views) VALUES ('$link', 0, 'catalog', 0)");
            } catch (Exception $e) {
                continue;
            }
        }
    }

    foreach ($product_res as $href) {
        $link = "https://mosplitka.ru" . $href->attr('href');

        if (preg_match("#https://mosplitka.ru/product.+#", $link)) {
            try {
                $link = mysqli_real_escape_string(getDB(), $link);
                sql("INSERT INTO links (link, views, type, product_views) VALUES ('$link', 0, 'product', 0)");
            } catch (Exception $e) {
                continue;
            }
        }
    }
} catch (Throwable $e) {
    var_dump($e);
}
