<?php

require __DIR__ . "/vendor/autoload.php";

use DiDom\Document;

$db = mysqli_connect('localhost', 'root', '', 'parser');
mysqli_query($db, 'SET character_set_results = "utf8"');

$query = mysqli_query($db, "SELECT link, views FROM links ORDER BY views, id LIMIT 1");

if ($query->num_rows) {
    $res = mysqli_fetch_assoc($query);
    $url = $res['link'];
    $views = $res['views'] + 1;
    mysqli_query($db, "UPDATE links SET views=$views WHERE link='$url'");
} else {
    $url = "https://mosplitka.ru/catalog"; //для самого первого запуска
}

$document = new Document($url, true);

$catalog_res = $document->find('a[href*=catalog]');
$product_res = $document->find('a[href*=product]');

foreach ($catalog_res as $href) {
    $link = "https://mosplitka.ru" . $href->attr('href');
    
    if (preg_match("#https://mosplitka.ru/catalog.+#", $link) and !preg_match("#.php$#", $link)) {
        try {
            mysqli_query($db, "INSERT INTO links (link, views, type, product_views) VALUES ('$link', 0, 'catalog', 0)");
        } catch (Exception $e) {
            continue;
        }
    }
}

foreach ($product_res as $href) {
    $link = "https://mosplitka.ru" . $href->attr('href');
    
    if (preg_match("#https://mosplitka.ru/product.+#", $link)) {
        try {
            mysqli_query($db, "INSERT INTO links (link, views, type, product_views) VALUES ('$link', 0, 'product', 0)");
        } catch (Exception $e) {
            continue;
        }
    }
}
