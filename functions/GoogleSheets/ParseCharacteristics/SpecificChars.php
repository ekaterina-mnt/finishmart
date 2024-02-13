<?php

namespace functions\GoogleSheets\ParseCharacteristics;

require_once __DIR__ . '/../../../vendor/autoload.php';

use functions\Parser;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;
use functions\MySQL;

class SpecificChars
{
    static function getChars($needed_category)
    {
        $char_table_name = [ // Название таблицы в MySQL
            "Напольные покрытия" => "napolnye_characteristics",
        ][$needed_category];

        $query = "SELECT name FROM $char_table_name";
        $specific_attributes = array_column(mysqli_fetch_all(MySQL::sql($query), MYSQLI_ASSOC), "name");

        return $specific_attributes;
    }
}
