<?php

namespace functions\GoogleSheets\Goods;

require_once __DIR__ . '/../../../vendor/autoload.php';

use functions\Parser;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;
use functions\MySQL;

class GetGoods
{
    static function getGoods($filled_ids_str, $needed_category, $needed_subcategory = null)
    {
        $add_str = "";
        if ($needed_subcategory) {
            $add_str = "subcategory like '{$needed_subcategory}' AND";
        }

        if ($filled_ids_str) {
            $query = "SELECT * FROM all_products WHERE {$add_str} category like '{$needed_category}' AND id NOT IN ($filled_ids_str) AND (status like 'ok' OR status IS NULL) AND char_views > 0";
        } else {
            $query = "SELECT * FROM all_products WHERE {$add_str} category like '{$needed_category}' AND (status like 'ok' OR status IS NULL) AND char_views > 0";
        }

        var_dump($query);

        $goods = MySQL::sql($query);

        var_dump(mysqli_fetch_assoc($goods));

        return $goods;
    }
}
