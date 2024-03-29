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
use functions\GoogleSheets\ParseCharacteristics\Categories\Santechnika;
use Google\Service\AuthorizedBuyersMarketplace\Contact;
use functions\GoogleSheets\ParseCharacteristics\DefineNeededColumns;
use functions\GoogleSheets\ParseCharacteristics\GetFilledIds;
use functions\GoogleSheets\ParseCharacteristics\MergedChars;
use functions\GoogleSheets\ParseCharacteristics\ConnectedSubcategories;



try {
    echo "Скрипт начал - " . date('Y-m-d H:i:s', time()) . "<br><br>";

    // if (!$_POST['category']) $_POST['category'] = 'Обои и настенные покрытия';
    // if (!$_GET['category']) $_GET['category'] = 'Обои и настенные покрытия';
    // if (!$_POST['subcategory']) $_POST['subcategory'] = 'Декоративные обои';
    // if (!$_GET['subcategory']) $_GET['subcategory'] = 'Декоративные обои';

    $needed_category = ($_POST['category'] ?? $_GET['category']) ?? $argv[1];
    $needed_subcategory = ($_POST['subcategory'] ?? $_GET['subcategory']) ?? $argv[2];

    /////// ОПРЕДЕЛЯЕМ НУЖНЫЕ ПЕРЕМЕННЫЕ ///////

    echo "Категория: {$_POST['category']}, подкатегория: {$_POST['subcategory']}<br><br>";
    $subcategories = ConnectedSubcategories::getList();
    if (!isset($needed_category) or !isset($needed_subcategory)) exit("Нужны параметры 'категория' и 'подкатегория'");
    if (!in_array($needed_category, array_keys($subcategories))) exit("Неподходящий параметр");
    if (!in_array($needed_subcategory, $subcategories[$needed_category])) exit("Неподходящий параметр");





    // $list_name = "Товары";
    $list_name = $needed_subcategory;


    // Первая ячейка, с которой начинается инзерт в Гугл Таблицу
    $current_cell = 4;
    $start_column = "A";
    $additional_columns = ['id в новой таблице', 'Дата изменения'];

    // В какую таблицу будет инзерт   
    $GoogleSheets_tablename = ConnectedSubcategories::getGoogleSheetsTableName($needed_category, $needed_subcategory);


    /////// ВСТАВЛЯЕМ СТРОКУ С ЗАГОЛОВКАМИ ХАРАКТЕРИСТИК ///////

    // Общие для всех категорий характеристики
    $common_attributes = CommonChars::getChars();
    $insert_common_attributes = array_keys($common_attributes); // плюс добавляем ниже цену для клиента
    $insert_common_attributes = array_merge(array_slice($insert_common_attributes, 0, array_search("Цена", $insert_common_attributes) + 1), ["Цена для клиента"], array_slice($insert_common_attributes, array_search("Цена", $insert_common_attributes) + 1));

    // Пересекающаяся характеристика (есть и в common, и в specific)
    // $cross = "В одной упаковке";

    // Определяем специфические атрибуты и заносим в таблицу

    $attributes_cell = "$list_name!" . $start_column . $current_cell - 1;

    $specific_attributes = MergedChars::getMergedCharsArray($GoogleSheets_tablename);
    $all_spec_attrs = MergedChars::getAllAttrs($GoogleSheets_tablename); // это в будущем для проверки все ли характеристики учтены в нашем списке
    $insert_specific_attributes = array(...array_unique(array_keys($specific_attributes)));
    // unset($insert_specific_attributes[array_search($cross, $insert_specific_attributes)]); // удаляем пересекающуюся характеристику, чтобы не дублировалась
    $insert_attributes = array_merge($additional_columns, $insert_common_attributes, $insert_specific_attributes);
    // $insert_attributes = array_intersect($needed_columns, $insert_attributes);




    Sheet::update_data($attributes_cell, $insert_attributes, $GoogleSheets_tablename);


    // Получаем id уже вставленных товаров и определяем последнюю заполненную строку

    $filled_ids_data = GetFilledIds::get($list_name, $current_cell, $GoogleSheets_tablename);
    $filled_ids = $filled_ids_data['filled_ids'];
    $current_cell = $filled_ids_data['current_cell'];



    /////// ВСТАВЛЯЕМ ЗНАЧЕНИЯ ДЛЯ СТОЛБЦОВ ///////

    // Получаем все товары нужной категории и подкатегории
    $goods = GetGoods::getGoods("all_products", $filled_ids, $needed_category, $needed_subcategory, "char_views > 0");
    $insert_data = array();
    $notCountedChars = array();

    foreach ($goods as $i => $good) {
        if (count($insert_data) > 2999) {
            echo ("<br><b>Ограничение по 3000 товаров за раз</b>");
            break;
        }

        // Определяем значения для $common_attributes
        $common_values = array();
        foreach ($common_attributes as $key => $attr) {
            $common_values[$key] = $good[$attr];
            if ($key == 'Цена' and $good['price'] != null) $common_values['Цена для клиента'] = round($good[$attr] * 1.1);
            // if ($column[$key] == 'Цена для клиента' and $value  == '-') $value = null;
        }
        $common_values = array_merge([MySQL::get_mysql_datetime()], $common_values);

        $characteristics = json_decode($good['characteristics'], 1);

        // Определяем значения для $specific_attributes
        $specific_values = array();

        foreach ($specific_attributes as $merged_attr => $attrs) {

            foreach ($attrs as $attr) {
                // if (str_contains($attr, "good")) {
                //     echo $attr;
                //     echo "<br>";;
                // }
                // if (preg_match("#good\[\'(.+)\'\]#", $attr, $matches)) {
                //     var_dump($matches);
                //     echo "<br>";
                //     $attr_from_mysql_column[] = 
                // }
                foreach ($characteristics as $char => $value) {
                    if ($char === $attr) {
                        $specific_values[$merged_attr] = $value;
                    }
                    if (preg_match("#good\[\'(.+)\'\]#", $attr, $matches)) {
                        $specific_values[$merged_attr] = $good[$matches[1]];
                    }

                    if (!in_array($char, $all_spec_attrs)) $notCountedChars[] = $char;
                }
            }

            if (!$specific_values[$merged_attr]) {
                $specific_values[$merged_attr] = "-";
            }
        }

        // // Объединяем пересекающиеся поля
        // $common_values[$cross] = $common_values[$cross] ?? $specific_values[$cross];
        // unset($specific_values[$cross]);

        // Объединям
        $values = array_merge($common_values, $specific_values);
        $values = array_map(fn ($value) => $value ?? "-", $values);
        $insert_data[] = FormInsertData::get_i($list_name, $values, "B", $current_cell++);
    }

    echo "<br>Всего строк добавлено:" . count($insert_data);
    if (count($notCountedChars)) echo "<br><br>Эти характеристики не учтены в сопоставлении характеристик - " . implode(", ", array_unique($notCountedChars)) . "<br>";
    Sheet::update_few_data($insert_data, $GoogleSheets_tablename);
    echo "<br>Гугл таблицы успешно обновлены";

    echo "<br>Скрипт закончил - " . date('Y-m-d H:i:s', time());
} catch (Throwable $e) {
    var_dump($e);
}
