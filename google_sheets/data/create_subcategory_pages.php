<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use functions\MySQL;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;
use functions\GoogleSheets\FormInsertData;
use functions\GoogleSheets\ParseCharacteristics\Napolnye;
use functions\GoogleSheets\ParseCharacteristics\Plitka;
use functions\GoogleSheets\ParseCharacteristics\SpecificChars;
use functions\GoogleSheets\ParseCharacteristics\ConnectedSubcategories;

try {
    echo "Скрипт начал - " . date('Y-m-d H:i:s', time()) . "<br><br>";
    echo "Категория: {$_POST['category']}, подкатегория: {$_POST['subcategory']}<br><br>";


    if (!isset($_POST['category'])) exit("Нужны параметры 'категория'");

    $subcategories = ConnectedSubcategories::getList();

    if (!in_array($_POST['category'], array_keys($subcategories))) exit("Неподходящий параметр");

    $needed_category = $_POST['category'];
    $needed_subcategories = $subcategories[$_POST['category']];

    $GoogleSheets_tablename =
        [
            'Напольные покрытия' => 'napolnye_raw',
            'Плитка и керамогранит' => 'plitka_raw',
            'Обои и настенные покрытия' => 'oboi_raw',
            'Лепнина' => 'lepnina_raw',
            'Сантехника' => 'santechnika_raw',
            'Краски' => 'kraski_raw',
        ][$needed_category];

    echo "Будут вставлены в таблицу '$GoogleSheets_tablename'";

    foreach ($needed_subcategories as $title) {
        Sheet::create_new_page($title, $GoogleSheets_tablename);
        echo "добавлен лист $title<br>";
    }

    echo "<br>Скрипт закончил - " . date('Y-m-d H:i:s', time());
} catch (Throwable $e) {
    var_dump($e);
}
