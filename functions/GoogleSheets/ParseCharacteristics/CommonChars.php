<?php

namespace functions\GoogleSheets\ParseCharacteristics;

require_once __DIR__ . '/../../../vendor/autoload.php';

use functions\Parser;
use functions\GoogleSheets\Sheet;
use functions\TechInfo;
use functions\MySQL;

class CommonChars
{
    static function getChars()
    {
        $chars = [
            'id',
            'articul',
            'title',
            'link',
            'category',
            'subcategory',
            'good_id_from_provider',
            'price',
            'stock',
            'images',
            'status',
            'provider',
            'characteristics',
            'description',
            'in_pack'
        ];

        return $chars;
    }
}
