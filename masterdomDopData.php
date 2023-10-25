<?php
require __DIR__ . "/vendor/autoload.php";

use DiDom\Document;
use functions\MySQL;
use functions\Logs;
use functions\Modes_1c;
use functions\TechInfo;
use functions\Parser;

TechInfo::start();

try {
    for ($i = 1; $i < 21; $i++) {
        echo "<b>Товар $i:</b><br><br>";

        //Получаем ссылку, с которой будем парсить
        $query = MySQL::sql("SELECT link, product_views FROM masterdom_links WHERE type='additional' ORDER BY product_views, id LIMIT 1"); //поменять имя таблицы

        if (!$query->num_rows) {
            Logs::writeCustomLog("не получено ссылки для парсинга");
            TechInfo::errorExit("не получено ссылки для парсинга");
        }

        $res = mysqli_fetch_assoc($query);

        //Получаем ссылку
        $url_parser = $res['link'];

        $provider = Parser::getProvider($url_parser);

        TechInfo::whichLinkPass($url_parser);

        //Увеличиваем просмотры ссылки
        $views = $res['product_views'] + 1;
        $date_edit = MySQL::get_mysql_datetime();
        MySQL::sql("UPDATE masterdom_links SET product_views=$views, date_edit='$date_edit' WHERE link='$url_parser'"); //поменять имя таблицы

        //Получаем html страницы
        try {
            $document = Parser::guzzleConnect($url_parser);

            $data_keys = $document->find('.b-cart__info_wrapper dt');
            $data_values = $document->find('.b-cart__info_wrapper dd');
            $all_data = [];
            foreach ($data_keys as $key => $value) {
                $all_data[$value->text()] = $data_values[$key]->text();
            }
            $all_product_data = [];

            echo "<b>Артикул товара: " . $all_data['Артикул']. "</b><br><br>";

            //добавляю ссылку на случай, если не были получены основные данные о товаре => нет строки в БД
            $all_product_data['link'] = [$url_parser, 's'];

            //Материал
            $material = $all_data['Материал'] ?? null;
            $all_product_data['material'] = [$material, 's'];

            //Комплектация 
            $complectation = $all_data['Комплектация'] ?? null;
            $all_product_data['complectation'] = [$complectation, 's'];

            //Монтаж
            $montage = $all_data['Тип монтажа'] ?? null;
            if ($montage == 'Наполная') $montage = 'Напольная';
            $all_product_data['montage'] = [$montage, 's'];

            //Дизайн
            $design = $all_data['Стиль дизайна'] ?? null;
            $all_product_data['design'] = [$design, 's'];

            //Форма
            $form = $all_data['Форма'] ?? null;
            $all_product_data['form'] = [$form, 's'];

            //Назначение
            $usage = $all_data['Назначение аксессуара'] ?? null;
            $all_product_data['product_usages'] = [$usage, 's'];

            echo "<b>итоговые дополнительные данные, которые мы спарсили:</b><br><br>";
            $print_result = [];
            foreach ($all_product_data as $key => $val) {
                $print_result[$key] = $val[0];
            }
            TechInfo::preArray($print_result);

            if (!empty(array_filter($print_result))) {
                //Для передачи в MySQL

                $types = '';
                $values = array();
                foreach ($all_product_data as $key => $value) {
                    $types .= $value[1];
                    $values[$key] = $value[0];
                }

                Parser::insertProductData($types, $values, $url_parser);
            }
        } catch (Throwable $e) {
            //Снова уменьшаем просмотры, чтобы скрипт потом еще раз прошел ссылку и прекращаем работу скрипта
            $views -= 1;
            MySQL::sql("UPDATE links SET views=$views WHERE link='$url_parser'");
            Logs::writeLog($e);
            TechInfo::errorExit($e);
        }
    }
} catch (\Throwable $e) {
    Logs::writeLog($e);
    TechInfo::errorExit($e);
    var_dump($e);
}

TechInfo::end();
