<?php

namespace functions\GoogleSheets\Sql;

require_once __DIR__ . '/../../../vendor/autoload.php';

use functions\Parser;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;
use functions\MySQL;

class SqlQuery
{
    static function getInsertQuery($columns_range, $letter, $GoogleSheets_tablename)
    {
        $columns = self::getColumns($columns_range, $GoogleSheets_tablename);
        
        $str = "=\"INSERT INTO final_products (";
        $str_vals = "";
        $str_dubl = "";
        // $str .= implode(", ", array_slice($insert_attributes, 3));
        foreach ($columns as $attr) {
            $str .= "`$attr`, ";
            $str_vals .= "'\"&$letter" . "4&\"', ";
            $str_dubl .= "`$attr`=" . "'\"&$letter" . "4&\"', ";
            $letter++;
        }
        $str = substr($str, 0, -2);
        $str_vals = substr($str_vals, 0, -2);
        $str_dubl = substr($str_dubl, 0, -2);
        $str .= ") VALUES (" . $str_vals . ")";
        $str .= " ON DUPLICATE KEY UPDATE " . $str_dubl . '"';

        return $str;
    }

    static function getColumns($columns_range, $GoogleSheets_tablename)
    {
        $columns_excel = Sheet::get_data($columns_range, $GoogleSheets_tablename);
        $columns_excel = $columns_excel['values'][0];

        return $columns_excel;
    }
}
