<?php

namespace functions\GoogleSheets\ParseCharacteristics;

require_once __DIR__ . '/../../../vendor/autoload.php';

use functions\Parser;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;
use functions\MySQL;

class GetFilledIds
{
    static function get($list_name, $current_cell, $GoogleSheets_tablename)
    {
        $cells = Sheet::get_data("$list_name!C" . "$current_cell:C10000", $GoogleSheets_tablename);

        if ($cells['values']) {
            $filled_ids = array_column($cells['values'], 0);
            $last_cell = array_key_last($filled_ids) + $current_cell;
            $filled_ids_str = implode(', ', $filled_ids);
            $current_cell = $last_cell + 1;
        }

        return ['filled_ids' => $filled_ids_str, 'current_cell' => $current_cell];
    }
}
