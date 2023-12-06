<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Title");
?>
<?php

$db = mysqli_connect('penzevrv.beget.tech', 'penzevrv_2109', 'r&R9QsYt', 'penzevrv_2109');
$products = mysqli_query($db, "SELECT * FROM all_products WHERE subcategory like 'Керамическая плитка' OR subcategory like 'Керамогранит' ORDER BY bitrix_views LIMIT 100");

foreach ($products as $product) {

	$product_id = $product['id'];
	$new_views = $product['bitrix_views'] + 1;
	mysqli_query($db, "UPDATE all_products SET bitrix_views=$new_views WHERE id=$product_id");

	$IBLOCK_ID = 14;
	switch ($product['subcategory']) {
		case 'Керамическая плитка':
			$IBLOCK_SECTION_ID = 18;
			break;
		case 'Керамогранит':
			$IBLOCK_SECTION_ID = 19;
			break;
	}

	$NAME = $product['title'];

	$el = new CIBlockElement;
	$PROP = array();
	$PROP[12] = "Белый";  // свойству с кодом 12 присваиваем значение "Белый"
	$PROP[3] = 38;        // свойству с кодом 3 присваиваем значение 38
	$arLoadProductArray = array(
		"MODIFIED_BY"    => $USER->GetID(), // элемент изменен текущим пользователем
		"IBLOCK_SECTION_ID" => $IBLOCK_SECTION_ID,
		"IBLOCK_ID"      => $IBLOCK_ID,
		"PROPERTY_VALUES" => $PROP,
		"NAME"           => $NAME,
		"ACTIVE"         => "Y",            // активен
		"PREVIEW_TEXT"   => "текст для списка элементов",
		"DETAIL_TEXT"    => "текст для детального просмотра",
		"DETAIL_PICTURE" => CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"] . "/image.gif")
	);
	if ($PRODUCT_ID = $el->Add($arLoadProductArray))
		echo "New ID: " . $PRODUCT_ID;
	else
		echo "Error: " . $el->LAST_ERROR;
}
?>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>