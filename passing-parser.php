<?php
require __DIR__ . "/vendor/autoload.php";

use DiDom\Document;
use functions\MySQL;
use functions\Logs;
use functions\Modes_1c;
use functions\TechInfo;
use GuzzleHttp\Client as GuzzleClient;



    $all_res = array_merge(
        $document->find('a[href*=catalog]'),
        $document->find('a[href*=product]'),
        $document->find('li[class*=menu] a')
    );

    //Получение запчастей для создания полной валидной ссылки
    preg_match("#(http.?:)//(.+$provider\..u)(.+)#", $url_parser, $url_matches);
    echo "<b>разделение url для приведения полных ссылок: </b>";
    var_dump($url_matches);
    echo "<br><br>";

    $protocol_url = $url_matches[1];
    $start_url = $url_matches[1] . '//' . $url_matches[2];

    //Получение всех ссылок
    echo "<b>скрипт нашел ссылки (" . count($all_res) . "шт):</b><br>";
    $add = [];
    foreach ($all_res as $href) {
        $attr_href = $href->attr('href');

        if (!$attr_href) continue;

        if (str_contains($attr_href, 'http')) {
            $link = $attr_href;
        } elseif (str_contains($attr_href, "$provider")) {
            $link = $protocol_url . $attr_href;
        } else {
            $link = $start_url . $attr_href;
        }
        echo "$link<br>";
    }
