<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Title");
?>
<?php

$db = mysqli_connect('penzevrv.beget.tech', 'penzevrv_2109', 'r&R9QsYt', 'penzevrv_2109');
$product = mysqli_query($db, "SELECT * FROM all_products WHERE subcategory like 'Раковины' ORDER BY title LIMIT 1");
$product = mysqli_fetch_array($product);
var_dump($product);
$IBLOCK_ID = 5;
$IBLOCK_SECTION_ID = 5;
$NAME = $product['title'];
var_dump($NAME);
exit;

$el = new CIBlockElement;
$PROP = array();
$PROP[12] = "Белый";  // свойству с кодом 12 присваиваем значение "Белый"
$PROP[3] = 38;        // свойству с кодом 3 присваиваем значение 38
$arLoadProductArray = Array(
	"MODIFIED_BY"    => $USER->GetID(), // элемент изменен текущим пользователем
	"IBLOCK_SECTION_ID" => $IBLOCK_SECTION_ID,
	"IBLOCK_ID"      => $IBLOCK_ID,
	"PROPERTY_VALUES"=> $PROP,
	"NAME"           => "Элемент",
	"ACTIVE"         => "Y",            // активен
	"PREVIEW_TEXT"   => "текст для списка элементов",
	"DETAIL_TEXT"    => "текст для детального просмотра",
	"DETAIL_PICTURE" => CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"]."/image.gif")
);
if($PRODUCT_ID = $el->Add($arLoadProductArray))
	echo "New ID: ".$PRODUCT_ID;
else
	echo "Error: ".$el->LAST_ERROR;
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>