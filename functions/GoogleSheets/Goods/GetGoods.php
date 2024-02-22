<?php

namespace functions\GoogleSheets\Goods;

require_once __DIR__ . '/../../../vendor/autoload.php';

use functions\Parser;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;
use functions\MySQL;

class GetGoods
{
    static function getGoods($filled_ids, $needed_category, $needed_subcategory = null)
    {
        $add_str = "";
        if ($needed_subcategory) {
            $add_str = "subcategory like '{$needed_subcategory}' AND";
        }

        if ($filled_ids) {
            $query = "SELECT * FROM all_products WHERE {$add_str} category like '{$needed_category}' AND id NOT IN ($filled_ids) AND (status like 'ok' OR status IS NULL) AND char_views > 0";
        } else {
            $query = "SELECT * FROM all_products WHERE {$add_str} category like '{$needed_category}' AND (status like 'ok' OR status IS NULL) AND char_views > 0";
        }

        $goods = MySQL::sql($query);

        return $goods;
    }
}
