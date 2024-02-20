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

    echo "Категория: {$_POST['category']}<br><br>";

    if (!isset($_POST['category'])) exit("Нужен параметр 'категория'");
    $categories = Parser::getCategoriesList();
    if (!in_array($_POST['category'], $categories)) exit("Неподходящий параметр");


    $needed_category = $_POST['category'];

    // foreach ($napolnye as $i => $sub) {
    //     $napolnye[$i] = "'{$sub}'";
    // }
    // $subcategoriesList = implode(", ", $napolnye);


    // $query = "SELECT id, characteristics, char_views FROM all_products WHERE subcategory in ($subcategoriesList) AND category like '{$needed_category}' ORDER BY char_views LIMIT 100";
    $query = "SELECT id, characteristics, char_views FROM all_products WHERE category like '{$needed_category}' ORDER BY char_views LIMIT 100";
    $goods = MySQL::sql($query);

    foreach ($goods as $good) {
        $query = "SELECT `{$needed_category}` from characteristics";

        $res = MySQL::sql($query);
        $columns = array_column(mysqli_fetch_all($res, MYSQLI_ASSOC), $needed_category);

        $chars = json_decode($good['characteristics'], true);

        $add_chars = array();
        foreach ($chars as $char => $value) {
            if (in_array($char, $columns)) {
                echo "есть в mysql<br>";
            } else {
                echo "нет в mysql<br>";
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
        $values = $good['char_views'] + 1;
        $types = "i";
        

        $query = MySQL::update($types, $values, "all_products", $good['id'], false);
    }


    echo "<br>Скрипт закончил - " . date('Y-m-d H:i:s', time());
} catch (Throwable $e) {
    var_dump($e);
}
