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
use Google\Service\AuthorizedBuyersMarketplace\Contact;
use functions\GoogleSheets\ParseCharacteristics\DefineNeededColumns;
use functions\GoogleSheets\ParseCharacteristics\GetFilledIds;


// Этот файл используем когда уже есть итоговая сравнительная таблица со значениями "ок", "удалить", "значение: "...""



try {
    echo "Скрипт начал - " . date('Y-m-d H:i:s', time()) . "<br><br>";




    /////// ОПРЕДЕЛЯЕМ НУЖНЫЕ ПЕРЕМЕННЫЕ ///////

    echo "Категория: {$_POST['category']}, подкатегория: {$_POST['subcategory']}<br><br>";

    if (!isset($_POST['category']) or !isset($_POST['subcategory'])) exit("Нужны параметры 'категория' и 'подкатегория'");
    $napolnye = Napolnye::getSubcategoriesNapolnye();
    if (!in_array($_POST['subcategory'], $napolnye)) exit("Неподходящий параметр");

    $needed_category = $_POST['category'];
    $needed_subcategory = $_POST['subcategory'];
    $list_name = $needed_subcategory;
    $needed_chars_list_name = "Сопоставления по подкатегориям";

    // Первая ячейка, с которой начинается инзерт в Гугл Таблицу
    $current_cell = 4;
    $start_column = "A";
    $additional_columns = ['id в новой таблице', 'Дата изменения'];

    $attributes_cell = "$list_name!" . $start_column . $current_cell - 1;

    // В какую таблицу будет инзерт 
    $GoogleSheets_tablename = "napolnye_raw"; // еще есть napolnye_raw

    // Пересекающаяся характеристика (есть и в common, и в specific)
    $cross = "В одной упаковке";

    // 
    $common_attributes = CommonChars::getChars(); // чтобы потом брать значения из $good
    $specific_attributes = Napolnye::getMergedCharsArray($GoogleSheets_tablename);




    /////// ОПРЕДЕЛЯЕМ КАКИЕ КОЛОНКИ НАМ НУЖНЫ ДЛЯ КАЖДОЙ ОТДЕЛЬНОЙ ПОДКАТЕГОРИИ ///////

    $needed_columns = DefineNeededColumns::define($needed_chars_list_name, $GoogleSheets_tablename, $needed_subcategory);
    Sheet::update_data($attributes_cell, array_keys($needed_columns), $GoogleSheets_tablename);


    // Получаем id уже вставленных товаров и определяем последнюю заполненную строку


    $filled_ids_data = GetFilledIds::get($list_name, $current_cell, $GoogleSheets_tablename);
    $filled_ids = $filled_ids_data['filled_ids'];
    $current_cell = $filled_ids_data['current_cell'];





    /////// ВСТАВЛЯЕМ ЗНАЧЕНИЯ ДЛЯ СТОЛБЦОВ ///////

    // Получаем все товары нужной категории и подкатегории
    $goods = GetGoods::getGoods($filled_ids_str, $needed_subcategory, $needed_category);

    $insert_values = array();
    $notCountedChars = array();

    foreach ($goods as $i => $good) {
        $good_insert_values = array();
        $good_insert_values[] = MySQL::get_mysql_datetime();

        // Определяем значения для колонок

        TechInfo::preArray($needed_columns);
        TechInfo::preArray($common_attributes);
        foreach (array_keys($needed_columns) as $column) {

            // если у этой колонки одинаковые для всей подкатегории значения
            if ($needed_columns[$column]) {
                $good_insert_values[$column] = $needed_columns[$column];
                continue;
            }

            // для общих для всех подкатегорий характеристик
            if (in_array($column, array_keys($common_attributes))) {
                var_dump($common_attributes[array_search($column, array_keys($common_attributes))]);
                $good_insert_values[$column] = $good[$common_attributes[array_search($column, array_keys($common_attributes))]];
                if ($key == 'Цена') $good_insert_values['Цена для клиента'] = round($good['price'] * 1.1);
                continue;
            }

            // для специфичных характеристик из сводной таблицы
            $characteristics = json_decode($good['characteristics'], 1);
            foreach ($specific_attributes[$column] as $merged_attr => $attrs) {
                foreach ($attrs as $attr) {
                    if (in_array($attr, $characteristics)) {
                        if ($column == $cross) {
                            $good_insert_values[$column] = $good_insert_values[$column] ?? $characteristics[$attr];
                        } else {
                            $good_insert_values[$column] = $characteristics[$attr];
                        }
                        continue;
                    } else {
                        $notCountedChars[] = $char;
                    }
                }
            }
        }

            // if (!$specific_values[$merged_attr]) {
            //     $specific_values[$merged_attr] = "-";
            // }

            // $good_insert_values = array_map(fn ($value) => $value ?? "-", $good_insert_values);
            TechInfo::preArray($good_insert_values);

        
            $insert_values[] = FormInsertData::get_i($list_name, $good_insert_values, "B", $current_cell++);

            TechInfo::preArray($insert_values);
            exit;
        
    }


    echo "<br>Всего строк добавлено:" . count($insert_values);
    if (count($notCountedChars)) echo "<br><br>Эти характеристики не учтены в сопоставлении характеристик - " . implode(", ", array_unique($notCountedChars)) . "<br>";
    Sheet::update_few_data($insert_values, $GoogleSheets_tablename);
    echo "<br>Гугл таблицы успешно обновлены";

    echo "<br>Скрипт закончил - " . date('Y-m-d H:i:s', time());
} catch (Throwable $e) {
    var_dump($e);
}
