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
            "id" => 'id',
            "Артикул" => 'articul',
            "Название" => 'title',
            "Ссылка на товар" => 'link',
            "Категория" => 'category',
            "Подкатегория" => 'subcategory',
            // "Код товара поставщика" => 'good_id_from_provider',
            "Цена" => 'price',
            "Наличие" => 'stock',
            "Картинки" => 'images',
            "Актуальный ли товар" => 'status',
            "Поставщик" => 'provider',
            "Характеристики поставщика" => 'characteristics',
            "Описание" => 'description',
            "В одной упаковке" => 'in_pack'
        ];

        return $chars;
    }
}
