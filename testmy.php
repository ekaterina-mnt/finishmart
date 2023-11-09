<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Title");
?>
<?

$db = mysqli_connect('ekatergz.beget.tech', 'ekatergz_2109', 'G&LCN&8t', 'ekatergz_2109');
mysqli_query($db, 'SET character_set_results = "utf8"');
$query = "SELECT * FROM all_products LIMIT 1";
$result = mysqli_query($db, $query);

foreach ($result as $good) {
 // echo "<pre>";
// print_r($good);
//  echo "</pre>";
}
echo "<br><br><br>";

CModule::IncludeModule('iblock');
CModule::IncludeModule('sale');

///////////////ОБЪЯВЛЕНИЕ НУЖНЫХ ПЕРЕМЕННЫХ
$PRODUCT_ID = $PRODUCT_OFFER_ID = 327;
$IBLOCK_ID = 2; //категория
$IBLOCK_SECTION_ID = 2; //подкатегория
$CODE = "blahf1fdfff2er";
$TITLE = 'Тестовый товар битрикс';
$PRICE = 666;
$QUANTITY = 10;
$ARTICLE =  "my45arti";
$PROP1 = array();
$PROP[2] = $TITLE;
$PROP[3] = '';
$PROP[4] = '';
$PROP[5] = "MY brend";
$PROP[6] = "Y";
$PROP[7] = "N";
$PROP[8] = "N";
$PROP[9] = $ARTICLE;
$PROP[10] = "FF";
$PROP[11] = 'f';
$PROP[12] = 'Розовый';
$PROP[13] = 'fa';
$PROP[14] = "MY brend";
$PROP[15] = "Y";
$PROP[16] = "N";
$PROP[17] = "N";
$PROP[18] = "my45arti";
$PROP2 = array();
$PROP[19] = $PRODUCT_ID;
$PROP[20] = $ARTICLE;
$PROP[21] = '';
$PROP[22] = "MY brend";
$PROP[23] = "Y";
$PROP[24] = "N";
///////////////////////////////////////////




$ciBlockElement = new CIBlockElement;

// Добавляем товар-родитель, у которго будут торг. предложения

$arProductFields = array(
    "ID" => $PRODUCT_ID,
    "MODIFIED_BY"    => $USER->GetID(), // элемент изменен текущим пользователем
    "IBLOCK_SECTION_ID" => $IBLOCK_SECTION_ID,
    "IBLOCK_ID"      => $IBLOCK_ID,
    "CODE" => $CODE,
    "PROPERTY_VALUES" => $PROP1,
    "NAME"           => $TITLE,
    "TYPE" => \Bitrix\Catalog\ProductTable::TYPE_SKU,
    "WEIGHT" => 3,
    "WIDTH" => 4,
    "LENGTH" => 5,
    "HEIGHT" => 6,
    "ACTIVE"         => "Y", // активен
    "PREVIEW_TEXT"   => "текст для списка элементов",
    "DETAIL_TEXT"    => "текст для детального просмотра",
);


// добавляем нужное кол-во торговых предложений
$arFields = array(
    "ID" => $PRODUCT_OFFER_ID,
    "IBLOCK_ID"      => $IBLOCK_ID, // IBLOCK торговых предложений
    "NAME"           => $TITLE,
    "ACTIVE"         => "Y",
    "IBLOCK_SECTION_ID" => $IBLOCK_SECTION_ID,
    "CODE" => $CODE,
    'PROPERTY_VALUES' => $PROP2,
    // Прочие параметры товара 
);





$existProduct = \Bitrix\Catalog\Model\Product::getCacheItem($PRODUCT_ID, true);
var_dump($existProduct);

if (!empty($existProduct)) {
    $i = CCatalogProduct::update($PRODUCT_ID, $arFields);
} else {
    $i = CCatalogProduct::add($arFields);
}

// проверка на ошибки
if (!empty($i)) {
    echo "Ошибка добавления торгового предложения: ";
    var_dump($i->LAST_ERROR);
    die();
}
echo "here";
// Добавляем параметры к торг. предложению
if ($ciBlockElement->GetByID($PRODUCT_ID)) {
    $ciBlockElement->Update($PRODUCT_ID, $PROP2);
    // проверка на ошибки
if (!empty($ciBlockElement->LAST_ERROR)) {
    echo "Ошибка добавления торгового предложения: " . $ciBlockElement->LAST_ERROR;
    die();
}
    CCatalogProduct::Update($PRODUCT_ID, 
        array(
            
            "ID" => $PRODUCT_OFFER_ID,
            "QUANTITY" => $QUANTITY,
        )
    );
    $re = CPrice::Update($PRODUCT_ID,
        array(
            "CURRENCY" => "RUB",
            "PRICE" => $PRICE,
            "CATALOG_GROUP_ID" => $IBLOCK_ID,
            "PRODUCT_ID" => $PRODUCT_OFFER_ID,
        )
    );
} else {
    $PRODUCT_OFFER_ID = $ciBlockElement->Add($arProduct);
    // проверка на ошибки
if (!empty($ciBlockElement->LAST_ERROR)) {
    echo "Ошибка добавления торгового предложения: " . $ciBlockElement->LAST_ERROR;
    die();
}
    // Добавляем цены к торг. предложению

    CCatalogProduct::Add(
        array(
            "ID" => $PRODUCT_OFFER_ID,
            "QUANTITY" => $QUANTITY,
        )
    );
    CPrice::Add(
        array(
            "CURRENCY" => "RUB",
            "PRICE" => $PRICE,
            "CATALOG_GROUP_ID" => $IBLOCK_ID,
            "PRODUCT_ID" => $PRODUCT_OFFER_ID,
        )
    );
}

echo "<pre>";
print_r(CCatalogProduct::getByID($PRODUCT_ID));
//print_r(CPrice::getByID($PRODUCT_ID));
//print_r(CIBlockElement::getProperty($IBLOCK_ID, $PRODUCT_ID));
echo "</pre>";

// проверка на ошибки
if (!empty($ciBlockElement->LAST_ERROR)) {
    echo "Ошибка добавления торгового предложения: " . $ciBlockElement->LAST_ERROR;
    die();
}


?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>