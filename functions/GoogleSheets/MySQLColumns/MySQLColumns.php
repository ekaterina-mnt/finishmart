<?php

namespace functions\GoogleSheets\MySQLColumns;

require_once __DIR__ . '/../../../vendor/autoload.php';

use functions\Parser;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;
use functions\MySQL;

class MySQLColumns
{
    static function add_columns($columns_excel_range, $GoogleSheets_tablename, $mysql_tablename)
    {
        $columns_excel = Sheet::get_data($columns_excel_range, $GoogleSheets_tablename);
        $columns_excel = $columns_excel['values'][0];

        $query = "SELECT COLUMN_NAME as 'columns' 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'penzevrv_2109' AND TABLE_NAME = '$mysql_tablename'";

        $res = MySQL::sql($query);
        $columns_mysql = array_column(mysqli_fetch_all($res, MYSQLI_ASSOC), "columns");

        TechInfo::preArray($columns_mysql);


        $insert_columns = array();

        foreach ($columns_excel as $column) {
            if (!in_array($column, $columns_mysql)) $insert_columns[] = $column;
        }

        foreach (array_intersect($columns_excel, $columns_mysql) as $column) {
            echo "Уже есть колонка $column<br>";
        }

        $query = "ALTER TABLE $mysql_tablename";
        foreach ($insert_columns as $column) {
            $query .= " ADD COLUMN `$column` TEXT(1500) DEFAULT NULL, ";
            echo "Добавится колонка $column<br>";
        }
        $query = substr($query, 0, -2);
        MySQL::sql($query);
        echo "Все добавлено<br>";
    }
}
