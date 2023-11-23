<?php
require __DIR__ . "/vendor/autoload.php";

use DiDom\Document;
use functions\MySQL;
use functions\ParserMosplitka;
use functions\Parser;
use functions\Logs;
use functions\Connect;
use functions\Modes_1c;
use functions\ParserMasterdom;
use functions\TechInfo;
use GuzzleHttp\Client as GuzzleClient;


//для следующих поставщиков: centerkrasok.ru


TechInfo::start();

try {
    for ($i = 1; $i < 51; $i++) {

        echo "<br><b>Ссылка $i</b><br><br>";
        //Получаем ссылку, с которой будем парсить
        $query = MySQL::sql("SELECT link, check_invalide_links_views, provider FROM all_products WHERE provider='centerkrasok' ORDER BY check_invalide_links_views, id LIMIT 1");

        if (!$query->num_rows) {
            TechInfo::errorExit("не найдена ссылка по запросу '$query'");
        }

        $res = mysqli_fetch_assoc($query);

        //Получаем ссылку
        $url_parser = $res['link'];
        $provider = $res['provider'];

        TechInfo::whichLinkPass($url_parser);

        //Увеличиваем просмотры ссылки
        $views = $res['check_invalide_links_views'] + 1;
        $date_edit = MySQL::get_mysql_datetime();
        MySQL::sql("UPDATE all_products SET check_invalide_links_views=$views WHERE link='$url_parser'");

        //Получаем html у себя
        try {
            $document = Connect::guzzleConnect($url_parser, $encoding ?? null);
            MySQL::sql("UPDATE all_products SET status='ok', date_edit='$date_edit' WHERE link='$url_parser'");
        } catch (\Throwable $e) {
            MySQL::sql("UPDATE all_products SET status='invalide', date_edit='$date_edit' WHERE link='$url_parser'");
            Logs::writeLog1($e,  $provider, $url_parser);
            TechInfo::errorExit($e);
            var_dump($e);
        }

        //Получаем все данные со страницы
        $error_classes = [
            ".not-provides", //centerkrasok (Данный товар более не поставляется)
        ];

        $parser_result = $document->find(implode(', ', $error_classes));

        if ($parser_result) {
            if (preg_match("#(не поставляется)#", $parser_result[0]->text())) {
                echo "Итог: ссылка невалидная<br>";
                MySQL::sql("UPDATE all_products SET status='invalide' WHERE link='$url_parser'");
                echo "Успешно обновлен статус ссылки на 'invalide'<br>";
            }
        } else {
            echo "Итог: ссылка активная<br>";
            MySQL::sql("UPDATE all_products SET status='ok' WHERE link='$url_parser'");
            echo "Успешно обновлен статус ссылки на 'ok'<br>";
        }
    } //конец итерации 1 товара
} catch (\Throwable $e) {
    // Logs::writeLog1($e,  $provider, $url_parser);
    TechInfo::errorExit($e);
}

TechInfo::end();
