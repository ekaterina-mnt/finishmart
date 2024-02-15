<?php

namespace functions\GoogleSheets\ParseCharacteristics;

require_once __DIR__ . '/../../../vendor/autoload.php';

use functions\Parser;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;
use functions\MySQL;

class DefineNeededColumns
{
    static function define($list_name, $GoogleSheets_tablename, $needed_subcategory)
    {
        $needed_chars = Sheet::get_data("$list_name!A1:AW11", $GoogleSheets_tablename);
        $needed_chars_keys = array_slice($needed_chars['values'][0], 1);
        foreach ($needed_chars['values'] as $k) {
            if ($k[0] == $needed_subcategory) {
                $needed_chars_values = array_slice($k, 1);
                break;
            }
        }

        if (!isset($needed_chars_values)) die("Не определены статусы колонок (какие ок, какие удалить?)");
        if (count($needed_chars_values) != count($needed_chars_keys)) die("Не совпадает количество колонок и их статусов (какие ок, какие удалить?)");

        $needed_columns = array();
        foreach ($needed_chars_values as $key => $status) {
            if ($status == 'ок') {
                $needed_columns[$needed_chars_keys[$key]] = null;
            } elseif (preg_match('#значение: "(.+)"#', $status, $matches)) {
                $needed_columns[$needed_chars_keys[$key]] = $matches[1];
            }
        }

        return $needed_columns;
    }
}
