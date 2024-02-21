<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use functions\MySQL;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;
use functions\Parser;
use functions\GoogleSheets\FormInsertData;
use functions\GoogleSheets\ParseCharacteristics\Napolnye;

try {
    echo "Скрипт начал - " . date('Y-m-d H:i:s', time()) . "<br><br>";

    $needed_category = $_POST['category'] ?? $_GET['category'];
    $categories = Parser::getCategoriesList();
    if (!$needed_category) {
        $needed_category = $categories[0];
        echo "Категория не была передана в качестве параметра, определяется категория по умолчанию<br>";
    }

    echo "Категория: {$needed_category}<br><br>";


    $query = "SELECT id, title, characteristics, char_views FROM all_products WHERE category like '{$needed_category}' ORDER BY char_views LIMIT 100";
    $goods = MySQL::sql($query);

    foreach ($goods as $good) {
        echo "<h3>{$good['title']}</h3><br>";
        $query = "SELECT `{$needed_category}` from characteristics";

        $res = MySQL::sql($query);
        $columns = array_column(mysqli_fetch_all($res, MYSQLI_ASSOC), $needed_category);

        $chars = json_decode($good['characteristics'], true);

        $add_chars = array();
        foreach ($chars as $char => $value) {
            if (in_array($char, $columns)) {
                echo "есть в mysql - $char<br>";
            } else {
                echo "нет в mysql - $char<br>";
                $add_chars[] = $char;
            }
        }

        // ДОБАВЛЕНИЕ КОЛОНОК
        if (count($add_chars)) {
            //     $query = "INSERT INTO final_products (`{$needed_category}`) VALUES ";
            //     foreach ($add_chars as $add_char) {
            //         $query .= "('$add_char'), ";
            //     }
            //     $query = substr($query, 0, -2);
            //     var_dump($query);
            //     MySQL::sql($query);

            $values = $add_chars;
            $types = str_repeat("s", count($values));
            MySQL::multiple_insert($needed_category, $types, $values, "characteristics");
        }

        // ДОБАВЛЕНИЕ САМИХ ХАРАКТЕРИСТИК
        // $types = str_repeat("s", count($chars));
        // обновляем char_views
        // $chars['char_views'] = $good['char_views'] + 1;
        $char_views = $good['char_views'] + 1;

        $query = "UPDATE all_products SET char_views = $char_views WHERE id = {$good['id']}";
        MySQL::sql($query);
        echo "<br>";
    }


    echo "<br>Скрипт закончил - " . date('Y-m-d H:i:s', time());
} catch (Throwable $e) {
    var_dump($e);
}
