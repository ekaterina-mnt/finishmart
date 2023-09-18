<?php

require __DIR__ . "/vendor/autoload.php";

use DiDom\Document;

$db = mysqli_connect('localhost', 'root', '', 'parser');

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
$product_links = $document->find('a[href*=product]');

$catalog_links = [];
foreach ($catalog_res as $link) {
    $href = "https://mosplitka.ru" . $link->attr('href');
    if (!preg_match("#.php$#", $href))
        try {
            mysqli_query($db, "INSERT INTO links (link, views) VALUES ('$href', 0)");
        } catch (Exception $e) {
            continue;
        }
}

if ($product_links) {
    foreach ($product_links as $link) {
        header('product.php');
    }
}



// //все категории (7 штук) - потом нужно добавить ключи - категория на русском
// $categories = array();

// $res = $document->find('.catalog_content__head_list_title a[href*=catalog]');
// foreach ($res as $link) {
//     $categories[] = $link->attr('href');
// }

// // var_dump($categories);

// //все сылки субкатегорий (через child может)
// $links = array();

// $res = $document->find("ul.cc__hl_inner li a[href*=catalog]");
// foreach ($res as $link) {
//     $links[] = $link->attr('href');
// }

// // var_dump($links);


// //все коллекции?

// $collections = array();
// foreach ($links as $link) {
//     $doc = new \DiDom\Document("https://mosplitka.ru/$link", true);
//     $res = $doc->find('.card__name a[href*=catalog]');
//     foreach ($res as $link) {
//         $collections[] = $link->attr('href');
//     }
//     sleep(1);
// }
// var_dump($collections);
