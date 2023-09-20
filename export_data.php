<?php

$db = mysqli_connect('localhost', 'root', '', 'parser');
mysqli_query($db, 'SET character_set_results = "utf8"');

$query = mysqli_query($db, "SELECT link, export_views FROM links WHERE type='product' ORDER BY export_views, id LIMIT 1");

// if ($query->num_rows) {
//     $res = mysqli_fetch_assoc($query);
//     $link = $res['link'];
//     $views = $res['export_views'] + 1;
//     mysqli_query($db, "UPDATE links SET product_views=$views WHERE link='$link'");
// }

$link = "https://mosplitka.ru/product/polka-dlya-polotenets-artwelle-harmonie-har-033-dlina-60-sm/";

$result = mysqli_query($db, "SELECT * FROM products WHERE link='$link'");
$product = mysqli_fetch_assoc($result);

$date = date('Y-m-d H:m:i', time());


if (!empty($product['category3'])) {
    $inGroup = $product['category3'];
} elseif (!empty($product['category2'])) {
    $inGroup = $product['category2'];
} elseif (!empty($product['category1'])) {
    $inGroup = $product['category1'];
} else {
    $inGroup = 'Не установлено';
}


$xmlString = '<?xml version="1.0" encoding="UTF-8"?>
<ФайлОбмена ВерсияФормата="3.0" ДатаВыгрузки="' . $date . '" ИмяКонфигурацииИсточника="" ИмяКонфигурацииПриемника="" ИдПравилКонвертации="" Комментарий="">  </ФайлОбмена>
<urlset xmlns:xsi
<ПравилаОбмена>…</ПравилаОбмена>
<ИнформацияОТипахДанных>… </ИнформацияОТипахДанных>
<ДанныеПоОбмену ПланОбмена="" Кому="" ОтКого="" НомерИсходящегоСообщения="" НомерВходящегоСообщения="" УдалитьРегистрациюИзменений=""/>
<ТабличнаяЧасть Имя="">
    <Запись>
        <Свойство Имя="Типа" Тип="Строка">
            <Значение>Запас</Значение>
        </Свойство>
        <Свойство Имя="Наименование" Тип="Строка">
            <Значение>' . $product['title'] . '</Значение>
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
        <Значение>' . $product['articul'] . '</Значение>
        </Свойство>
        <Свойство Имя="Ссылка" Тип="Строка">
            <Значение>' . $product['link'] . '</Значение>
        </Свойство>
        <Свойство Имя="Изображения" Тип="Строка">
            <Значение>' . $product['images'] . '</Значение>
        </Свойство>
        <Свойство Имя="Варианты" Тип="Строка">
            <Значение>' . $product['variants'] . '</Значение>
        </Свойство>
        <Свойство Имя="Характеристики" Тип="Строка">
        <Значение>' . $product['characteristics'] . '</Значение>
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
            <Значение>' . $product['format'] . '</Значение>
        </Свойство>
        <Свойство Имя="Материал" Тип="Строка">
        <Значение>' . $product['material'] . '</Значение>
        </Свойство>
        <Свойство Имя="Производитель" Тип="Строка">
            <Значение>' . $product['producer'] . '</Значение>
        </Свойство>
        <Свойство Имя="Коллекция" Тип="Строка">
            <Значение>' . $product['collection'] . '</Значение>
        </Свойство>
    </Запись>
</ТабличнаяЧасть>';


$dom = new DOMDocument();
$dom->loadXML($xmlString);
$dom->formatOutput = true;
echo $dom->saveXML();
