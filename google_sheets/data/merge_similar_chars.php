<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use functions\MySQL;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;
use functions\GoogleSheets\FormInsertData;
use functions\GoogleSheets\ParseCharacteristics\Napolnye;
use functions\GoogleSheets\ParseCharacteristics\SpecificChars;
use functions\GoogleSheets\ParseCharacteristics\CommonChars;
use functions\GoogleSheets\Goods\GetGoods;

try {
    echo "Скрипт начал - " . date('Y-m-d H:i:s', time()) . "<br><br>";
    echo "Категория: {$_POST['category']}, подкатегория: {$_POST['subcategory']}<br><br>";

    if (!isset($_POST['category']) or !isset($_POST['subcategory'])) exit("Нужны параметры 'категория' и 'подкатегория'");
    $napolnye = Napolnye::getSubcategoriesNapolnye();
    if (!in_array($_POST['subcategory'], $napolnye)) exit("Неподходящий параметр");

    $needed_category = $_POST['category'];
    $needed_subcategory = $_POST['subcategory'];
    $list_name = $needed_subcategory;

    // Первая ячейка, с которой начинается инзерт в Гугл Таблицу
    $current_cell = 4;
    // В какую таблицу будет инзерт 
    $GoogleSheets_tablename = "napolnye_edition"; // еще есть napolnye_raw

    // Общие для всех категорий характеристики
    $common_attributes = CommonChars::getChars();
    $count_common_attributes = count($common_attributes);

    // Определяем специфические атрибуты и заносим в таблицу

    $specific_attributes_cell = "$list_name!S" . $current_cell - 1;

    $specific_attributes = Napolnye::getMergedCharsArray();
    $all_spec_attrs = Napolnye::getAllAttrs(); // это в будущем для проверки все ли характеристики учтены в нашем списке
    $insert_specific_attributes = array(...array_unique(array_keys($specific_attributes)));
    Sheet::update_data($specific_attributes_cell, $insert_specific_attributes, $GoogleSheets_tablename);


    // Получаем id уже вставленных товаров и определяем последнюю заполненную строку

    $cells = Sheet::get_data("$list_name!C$current_cell:C10000", $GoogleSheets_tablename);

    if ($cells['values']) {
        $filled_ids = array_column($cells['values'], 0);
        $last_cell = array_key_last($filled_ids) + $current_cell;
        $filled_ids_str = implode(', ', $filled_ids);
        $current_cell = $last_cell + 1;
    }

    // Получаем все товары нужной категории и подкатегории
    $goods = GetGoods::getGoods($filled_ids_str, $needed_subcategory, $needed_category);
    $insert_data = array();

    foreach ($goods as $i => $good) {

        // Определяем значения для $common_attributes
        $common_values = array();
        foreach ($common_attributes as $attr) {
            $common_values[] = $good[$attr];
        }
        $common_values = array_merge([MySQL::get_mysql_datetime()], array_slice($common_values, 0, 1), ["-"], array_slice($common_values, 1));

        $characteristics = json_decode($good['characteristics'], 1);

        // Определяем значения для $specific_attributes
        $specific_values = array();

        $characteristics['Тест'] = 'тест';
        foreach ($specific_attributes as $merged_attr => $attrs) {
            foreach ($attrs as $attr) {
                foreach ($characteristics as $char => $value) {
                    if ($char === $attr) {
                        $specific_values[$merged_attr] = $good[$attr];
                    }
                }
            }
            if (!$specific_values[$merged_attr]) {
                $specific_values[$merged_attr] = "-";
            }
            if (!in_array($char, $all_spec_attrs)) echo "Эта характеристика не учтена в сопоставлении характеристик - $char<br>";
        }

        // Объединям
        $values = array_merge($common_values, $specific_values);
        $values = array_map(fn ($value) => $value ?? "-", $values);
        $insert_data[] = FormInsertData::get_i($list_name, $values, "B", $current_cell++);
    }
    echo "<br>Всего строк добавлено:" . count($insert_data);
    Sheet::update_few_data($insert_data, $GoogleSheets_tablename);
    echo "<br>Гугл таблицы успешно обновлены";

    echo "<br>Скрипт закончил - " . date('Y-m-d H:i:s', time());
} catch (Throwable $e) {
    var_dump($e);
}
