<?php

namespace functions\GoogleSheets;

require_once __DIR__ . '/../../vendor/autoload.php';

use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;
use Google_Service_Sheets_BatchUpdateValuesRequest;
use Google_Service_Sheets_ClearValuesRequest;

class FormInsertData
{
    /*
    str $col_letter - буква колонки в Гугл Таблицах
    */
    static function get_i($list_name, $values, $col_letter, $cell_num)
    {
        try {
            if (is_string($values)) {
                $values = array(array($values));
            } elseif (is_array($values)) {
                $values = array(array_values($values));
            }

            $insert_data_i =
                [
                    'range' => "$list_name!$col_letter" . $cell_num,
                    'values' => $values,
                ];

            return $insert_data_i;
        } catch (\Throwable $e) {
            var_dump($e->getMessage());
        }
    }
}
