<?php
require __DIR__ . "/../vendor/autoload.php";

use DiDom\Document;
use functions\MySQL;
use functions\ParserMosplitka;
use functions\Parser;
use functions\Logs;
use functions\Modes_1c;
use functions\ParserMasterdom;
use functions\TechInfo;
use functions\Connect;
use GuzzleHttp\Client as GuzzleClient;


//МЕНЯТЬ ТОЛЬКО ЗДЕСЬ
$script_iteration_provider = 'ampir';
//


TechInfo::start();

try {
    for ($i = 1; $i < 6; $i++) {

        echo "<br><b>Ссылка $i</b><br><br>";
        //Получаем ссылку, с которой будем парсить
        $query_str = "SELECT link, views, provider FROM all_links WHERE type='catalog' and provider='" . $script_iteration_provider . "' ORDER BY views, id LIMIT 1";
        $query = MySQL::sql($query_str);

        if (!$query->num_rows) {
            MySQL::firstLinksInsert(); //для самого первого запуска
            TechInfo::errorExit("первый запрос, добавлены первичные ссылки для парсинга (или нет ссылок с типом `каталог` у этого провайдера)");
        }

        $res = mysqli_fetch_assoc($query);

        //Получаем ссылку
        $url_parser = $res['link'];
        $provider = $res['provider'];

        TechInfo::whichLinkPass($url_parser);

        //Увеличиваем просмотры ссылки
        $views = $res['views'] + 1;
        $date_edit = MySQL::get_mysql_datetime();
        MySQL::sql("UPDATE all_links SET views=$views WHERE link='$url_parser'");

        if ($provider == 'masterdom') continue; //

        //Получаем html у себя
        $document = Connect::guzzleConnect($url_parser);

        //Получаем все данные со страницы
        $search_classes = Parser::get_links_search_classes();

        $search_classes = implode(", ", $search_classes);
        $all_res = $document->find($search_classes);

        echo "<b>скрипт нашел ссылки (" . count($all_res) . "шт):</b><br>";

        $add = [];
        foreach ($all_res as $href) {
            $link = Parser::generateLink($href->attr('href'), $provider, $url_parser);
            if ($provider == 'alpinefloor' and $href->attr('data-endpoint')) {
                $link = Parser::generateLink(str_replace(["is_ajax=y&", "ajax=y&"], '', $href->attr('data-endpoint')), $provider, $url_parser);
            }


            // избавляемся от дублей
            if (MySQL::sql("SELECT id, link FROM all_links WHERE link='$link'")->num_rows) {
                echo "$link - ссылка уже есть в БД<br>";
                continue;
            }

            if (($provider == 'mosplitka' and str_contains($link, "//filter//")) or
                ($provider == 'mosplitka' and $link == 'https://mosplitka.ru/catalog/plitka/')
            ) {
                echo "$link - ненужная ссылка-фильтр<br>";
                continue;
            }

            //определяем это ссылка на продукт или каталог
            $link_type = Parser::getLinkType($link);
            if (!$link_type) {
                echo "$link - не определился тип ссылки<br>";
                continue;
            }

            echo "$link<br>"; //оставить для вывода

            $res = Parser::insertLink1($link, $link_type, $provider);
            if ($res == "success") $add[] = ['link' => $link, 'comment' => $link_type];
            if ($res == "fail") $add[] = ['link' => $link . ' - не получилось добавить в БД', 'comment' => $link_type];
        }
        sort($add);
        echo "<br><b>из них скрипт добавил (" . count($add) . "шт):</b><br>";
        foreach ($add as $add_key => $add_value) {
            $add_key += 1;
            echo $add_key . ") " . $add_value['link'] . " (" . $add_value['comment'] . ")<br>";
        }
        echo "<br><b>не было ошибок</b><br><br>";
    } //конец итерации 1 товара
} catch (\Throwable $e) {
    Logs::writeLog1($e,  $provider, $url_parser);
    TechInfo::errorExit($e);
}

TechInfo::end();
