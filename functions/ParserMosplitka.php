<?php

namespace functions;

require __DIR__ . "/../vendor/autoload.php";

use DiDom\Document;
use DOMDocument;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;

class ParserMosplitka
{
    static function getLinkType(string $link): string|null
    {
        if (preg_match("#https://mosplitka.ru/catalog.+#", $link) and !preg_match("#.php$#", $link)) {
            $type = 'catalog';
        } elseif (preg_match("#https://mosplitka.ru/product.+#", $link)) {
            $type = 'product';
        }

        $type = $type ?? null;

        return $type;
    }

    static function getCategory(string $link): string|null
    {
        $categories = array();
        foreach ($path_res as $a) {
            $a = $a->text();
            if (!isset($producer) && isset($collection)) {
                if ($a != 'На главную' && $a != 'Каталог' && !str_contains($a, $collection)) {
                    $categories[] = $a;
                }
            } elseif (isset($producer) && !isset($collection)) {
                if ($a != 'На главную' && $a != 'Каталог' && !str_contains($a, $producer)) {
                    $categories[] = $a;
                }
            } elseif (!isset($producer) && !isset($collection)) {
                if ($a != 'На главную' && $a != 'Каталог') {
                    $categories[] = $a;
                }
            } else {
                if ($a != 'На главную' && $a != 'Каталог' && !str_contains($a, $producer) && !str_contains($a, $collection)) {
                    $categories[] = $a;
                }
            }
        }

        
    }
}
