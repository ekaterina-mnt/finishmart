<?php

namespace functions;

class TechInfo
{
    static function start()
    {
        echo "<b>скрипт начал работу " . date("d-m-Y H:i:s", time()) . "</b><br><br>";
    }

    static function end()
    {
        echo "<br><br><b>скрипт закончил работу " . date("d-m-Y H:i:s", time()) . "</b><br><br>";
    }

    static function errorExit($e)
    {
        echo "<b>ошибка: </b>";
        var_dump($e);
        self::end();
        exit();
    }

    static function whichLinkPass($link, $sub = null)
    {
        echo '<b>скрипт проходил ссылку <a href="' . $link . '">' . $link . '</a>';
        if (isset($sub)) echo " - брались товары по API";
        echo '</b><br><br>';
    }

    static function iArray($array)
    {
        foreach ($array as $i) {
            var_dump($i);
            echo "<br><br><br>";
        }
    }

    static function preArray($array)
    {
        echo "<pre>";
        print_r($array);
        echo "</pre>";
    }

    static function allAtrr(array $all_product_data): array
    {
        $all_product_data['link'] = $all_product_data['link'] ?? [null, 's'];
        $all_product_data['title'] = $all_product_data['title'] ?? [null, 's'];
        $all_product_data['price'] = $all_product_data['price'] ?? [null, 'i'];
        $all_product_data['articul'] = $all_product_data['articul'] ?? [null, 's'];
        $all_product_data['category'] = $all_product_data['category'] ?? [null, 's'];
        $all_product_data['subcategory'] = $all_product_data['subcategory'] ?? [null, 's'];
        $all_product_data['producer'] = $all_product_data['producer'] ?? [null, 's'];
        $all_product_data['brand'] = $all_product_data['brand'] ?? [null, 's'];
        $all_product_data['collection'] = $all_product_data['collection'] ?? [null, 's'];
        $all_product_data['length'] = $all_product_data['length'] ?? [null, 'd'];
        $all_product_data['width'] = $all_product_data['width'] ?? [null, 'd'];
        $all_product_data['height'] = $all_product_data['height'] ?? [null, 'd'];
        $all_product_data['depth'] = $all_product_data['depth'] ?? [null, 'd'];
        $all_product_data['thickness'] = $all_product_data['thickness'] ?? [null, 'd'];
        $all_product_data['format'] = $all_product_data['format'] ?? [null, 's'];
        $all_product_data['material'] = $all_product_data['material'] ?? [null, 's'];
        $all_product_data['country'] = $all_product_data['country'] ?? [null, 's'];
        $all_product_data['form'] = $all_product_data['form'] ?? [null, 's'];
        $all_product_data['color'] = $all_product_data['color'] ?? [null, 's'];
        $all_product_data['montage'] = $all_product_data['montage'] ?? [null, 's'];
        $all_product_data['design'] = $all_product_data['design'] ?? [null, 's'];
        $all_product_data['pattern'] = $all_product_data['pattern'] ?? [null, 's'];
        $all_product_data['orientation'] = $all_product_data['orientation'] ?? [null, 's'];
        $all_product_data['surface'] = $all_product_data['surface'] ?? [null, 's'];
        $all_product_data['product_usages'] = $all_product_data['product_usages'] ?? [null, 's'];
        $all_product_data['facture'] = $all_product_data['facture'] ?? [null, 's'];
        $all_product_data['type'] = $all_product_data['type'] ?? [null, 's'];
        $all_product_data['edizm'] = $all_product_data['edizm'] ?? [null, 's'];
        $all_product_data['dilution'] = $all_product_data['dilution'] ?? [null, 's'];
        $all_product_data['consumption'] = $all_product_data['consumption'] ?? [null, 's'];
        $all_product_data['usable_area'] = $all_product_data['usable_area'] ?? [null, 's'];
        $all_product_data['method'] = $all_product_data['method'] ?? [null, 's'];
        $all_product_data['count_layers'] = $all_product_data['count_layers'] ?? [null, 's'];
        $all_product_data['blending'] = $all_product_data['blending'] ?? [null, 's'];
        $all_product_data['volume'] = $all_product_data['volume'] ?? [null, 's'];
        $all_product_data['stock'] = $all_product_data['stock'] ?? [null, 's'];
        $all_product_data['status'] = $all_product_data['archived'] ?? [null, 's'];

        return $all_product_data;
    }
}
