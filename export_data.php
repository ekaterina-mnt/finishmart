<?php
require "functions.php";

$data = $_SERVER['REQUEST_URI'];
$time = date('Y-m-d H:i:s', time());

try {
sql("INSERT INTO data (data, time) VALUES ('$data', '$time')");
} catch (Exception $e) {
}

session_start();
setcookie("bitr", 1);

if (isset($_GET['mode'])) {
    if ($_GET['mode'] == 'checkauth') {
        echo "success\n"; 
        echo "bitr";
        echo 1;
        echo session_name() ."\n"; 
        echo "sessid=" . session_id() ."\n";
    } 
    if ($_GET['mode'] == 'init') {
        echo "zip=no"."\n";
        echo "file_limit=204800" . "\n";
        exit();
    }
    if ($_GET['mode'] == 'query') {
        echo "success\n"; 
        exit();
    }
}

$products = sql("SELECT * FROM products LIMIT 5");

$xmlString = '<?xml version="1.0" encoding="UTF-8"?>
<ФайлОбмена ВерсияФормата="3.0">
<ТабличнаяЧасть>';

foreach ($products as $product) {

    if (!empty($product['category3'])) {
        $inGroup = $product['category3'];
    } elseif (!empty($product['category2'])) {
        $inGroup = $product['category2'];
    } elseif (!empty($product['category1'])) {
        $inGroup = $product['category1'];
    } else {
        $inGroup = 'Не установлено';
    }

    $xmlString .= '<Запись>
        <Свойство Имя="Наименование" Тип="Строка">
            <Значение><![CDATA[' . $product['title'] . ']]></Значение>
        </Свойство>
        <Свойство Имя="Остатки" Тип="Строка">
            <Значение>' . $product['stock'] . '</Значение>
        </Свойство>
        <Свойство Имя="Ед. изм. хранения" Тип="Строка">
            <Значение>' . $product['edizm'] . '</Значение>
        </Свойство>
        <Свойство Имя="Розничная цена" Тип="Число">
            <Значение>' . $product['price'] . '</Значение>
        </Свойство>
        <Свойство Имя="Артикул" Тип="Строка">
        <Значение><![CDATA[' . $product['articul'] . ']]></Значение>
        </Свойство>
        <Свойство Имя="Ссылка" Тип="Строка">
            <Значение><![CDATA[' . $product['link'] . ']]></Значение>
        </Свойство>
        <Свойство Имя="Изображения" Тип="Строка">
            <Значение><![CDATA[' . $product['images'] . ']]></Значение>
        </Свойство>
        <Свойство Имя="Варианты" Тип="Строка">
            <Значение><![CDATA[' . $product['variants'] . ']]></Значение>
        </Свойство>
        <Свойство Имя="Характеристики" Тип="Строка">
        <Значение><![CDATA[' . $product['characteristics'] . ']]></Значение>
        </Свойство>
        <Свойство Имя="В группе" Тип="Строка">
            <Значение>' . $inGroup . '</Значение>
        </Свойство>
        <Свойство Имя="Длина" Тип="Число">
            <Значение>' . $product['length'] . '</Значение>
        </Свойство>
        <Свойство Имя="Высота" Тип="Число">
            <Значение>' . $product['height'] . '</Значение>
        </Свойство>
        <Свойство Имя="Глубина" Тип="Число">
        <Значение>' . $product['depth'] . '</Значение>
        </Свойство>
        <Свойство Имя="Толщина" Тип="Число">
            <Значение>' . $product['thickness'] . '</Значение>
        </Свойство>
        <Свойство Имя="Формат" Тип="Строка">
            <Значение><![CDATA[' . $product['format'] . ']]></Значение>
        </Свойство>
        <Свойство Имя="Материал" Тип="Строка">
        <Значение><![CDATA[' . $product['material'] . ']]></Значение>
        </Свойство>
        <Свойство Имя="Производитель" Тип="Строка">
            <Значение><![CDATA[' . $product['producer'] . ']]></Значение>
        </Свойство>
        <Свойство Имя="Коллекция" Тип="Строка">
            <Значение><![CDATA[' . $product['collection'] . ']]></Значение>
        </Свойство>
    </Запись>';
}

$xmlString .= '</ТабличнаяЧасть></ФайлОбмена>';

$dom = new DOMDocument();
$dom->formatOutput = true;

header("Content-type: text/xml");
echo $xmlString;

$dom->loadXML($xmlString);
$dom->save("1c_catalog.xml");




// Старое
// $dom = new DOMDocument();
// $dom->formatOutput = true;
// $dom->loadXML($xmlString);
// echo $dom->saveXML();
// header("Content-type: text/xml");
// $xmlString .= '</ТабличнаяЧасть></ФайлОбмена>';

// 1 вариант 
// $dom = new DOMDocument();
// $dom->formatOutput = true;
// echo $xmlString;

// 2 вариант (можно сохранить файл xml) - не до конца разобрано
// $dom = new DOMDocument();
// $dom->loadXML($xmlString);
// $dom->formatOutput = true;
// echo $dom->saveXML();
// $dom->save("result.xml");