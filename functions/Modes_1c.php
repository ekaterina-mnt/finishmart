<?php

namespace functions;

class Modes_1c
{
    static function mode_checkauth()
    {
        echo "success\n";
        echo session_name() . "\n";
        echo "sessid=" . session_id() . "\n";
        echo "timestamp=" . time() . "\n";
        exit();
    }

    static function mode_init()
    {
        echo "zip=yes" . "\n";
        exit();
    }

    static function mode_query_package_start()
    {
        $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
<КоммерческаяИнформация ПараметрПакета="1" ВерсияСхемы="2.021" ДатаФормирования="2023-10-20T23:56:59">
	<Классификатор>
		<Ид>1c-classificator-masterdom</Ид>
		<Наименование>Мастердом</Наименование>
		<Свойства>
		<Свойство>
				<Ид>CML2_ATTRIBUTES</Ид>
				<Наименование>Характеристики</Наименование>
				<Множественное>true</Множественное>
			</Свойство>
			<Свойство>
				<Ид>CML2_TRAITS</Ид>
				<Наименование>Реквизиты</Наименование>
				<Множественное>true</Множественное>
			</Свойство>
			<Свойство>
				<Ид>CML2_BASE_UNIT</Ид>
				<Наименование>Базовая единица</Наименование>
				<Множественное>false</Множественное>
			</Свойство>
			<Свойство>
				<Ид>CML2_STOCK</Ид>
				<Наименование>Остатки</Наименование>
				<Множественное>false</Множественное>
			</Свойство>
			<Свойство>
				<Ид>CML2_PRODUCT_LINK</Ид>
				<Наименование>Ссылка на товар</Наименование>
				<Множественное>false</Множественное>
			</Свойство>
			<Свойство>
				<Ид>CML2_LENGTH</Ид>
				<Наименование>Длина</Наименование>
				<Множественное>false</Множественное>
			</Свойство>
			<Свойство>
				<Ид>CML2_WIDTH</Ид>
				<Наименование>Ширина</Наименование>
				<Множественное>false</Множественное>
			</Свойство>
			<Свойство>
				<Ид>CML2_HEIGHT</Ид>
				<Наименование>Высота</Наименование>
				<Множественное>false</Множественное>
			</Свойство>
			<Свойство>
				<Ид>CML2_THICKNESS</Ид>
				<Наименование>Толщина</Наименование>
				<Множественное>false</Множественное>
			</Свойство>
			<Свойство>
				<Ид>CML2_DEPTH</Ид>
				<Наименование>Глубина</Наименование>
				<Множественное>false</Множественное>
			</Свойство>
			<Свойство>
				<Ид>CML2_FORMAT</Ид>
				<Наименование>Формат</Наименование>
				<Множественное>false</Множественное>
			</Свойство>
			<Свойство>
				<Ид>CML2_MANUFACTURER</Ид>
				<Наименование>Производитель</Наименование>
				<Множественное>false</Множественное>
			</Свойство>
			<Свойство>
				<Ид>CML2_COLLECTION</Ид>
				<Наименование>Коллекция</Наименование>
				<Множественное>false</Множественное>
			</Свойство>
			<Свойство>
				<Ид>CML2_COUNTRY</Ид>
				<Наименование>Страна</Наименование>
				<Множественное>false</Множественное>
			</Свойство>
			<Свойство>
				<Ид>CML2_MATERIAL</Ид>
				<Наименование>Материал</Наименование>
				<Множественное>false</Множественное>
			</Свойство>
			<Свойство>
				<Ид>CML2_PRODUCT_USAGE</Ид>
				<Наименование>Назначение</Наименование>
				<Множественное>false</Множественное>
			</Свойство>
			<Свойство>
				<Ид>CML2_IMAGES</Ид>
				<Наименование>Картинки</Наименование>
				<Множественное>false</Множественное>
			</Свойство>
			<Свойство>
				<Ид>CML2_VARIANTS</Ид>
				<Наименование>Варианты исполнения</Наименование>
				<Множественное>false</Множественное>
			</Свойство>
			<Свойство>
				<Ид>CML2_DATE_EDIT</Ид>
				<Наименование>Дата последнего изменения</Наименование>
				<Множественное>false</Множественное>
			</Свойство>
		</Свойства>
		<Группы>
			<Группа>
				<Ид>groupOboi</Ид>
				<Наименование>Обои и настенные покрытия</Наименование>
				<Группы>
					<Группа>
						<Ид>groupDecorativnye</Ид>
						<Наименование>Декоративные обои</Наименование>
						<Группы />
					</Группа>
				</Группы>
			</Группа>
			<Группа>
				<Ид>groupPlitka</Ид>
				<Наименование>Плитка и керамогранит</Наименование>
				<Группы>
					<Группа>
						<Ид>groupKeramicheskaya</Ид>
						<Наименование>Керамическая плитка</Наименование>
						<Группы />
					</Группа>
					<Группа>
						<Ид>groupKeramogranit</Ид>
						<Наименование>Керамогранит</Наименование>
						<Группы />
					</Группа>
					<Группа>
						<Ид>groupMozaika</Ид>
						<Наименование>Мозаика</Наименование>
						<Группы />
					</Группа>
					<Группа>
						<Ид>groupNaturalKamen</Ид>
						<Наименование>Натуральный камень</Наименование>
						<Группы />
					</Группа>
				</Группы>
			</Группа>
			<Группа>
				<Ид>groupSantehnika</Ид>
				<Наименование>Сантехника</Наименование>
				<Группы>
					<Группа>
						<Ид>groupRackoviny</Ид>
						<Наименование>Раковины</Наименование>
						<Группы />
					</Группа>
					<Группа>
						<Ид>groupVanny</Ид>
						<Наименование>Ванны</Наименование>
						<Группы />
					</Группа>
					<Группа>
						<Ид>groupSantMebel</Ид>
						<Наименование>Мебель для ванной комнаты</Наименование>
						<Группы />
					</Группа>
					<Группа>
						<Ид>groupPolotencesuchiteli</Ид>
						<Наименование>Полотенцесушители</Наименование>
						<Группы />
					</Группа>
					<Группа>
						<Ид>groupSmesiteli</Ид>
						<Наименование>Смесители</Наименование>
						<Группы />
					</Группа>
					<Группа>
						<Ид>groupUnitazy</Ид>
						<Наименование>Унитазы, писсуары и биде</Наименование>
						<Группы />
					</Группа>
					<Группа>
						<Ид>groupComplectuyuschie</Ид>
						<Наименование>Комплектующие</Наименование>
						<Группы />
					</Группа>
					<Группа>
						<Ид>groupDushevye</Ид>
						<Наименование>Душевые</Наименование>
						<Группы />
					</Группа>
					<Группа>
						<Ид>groupAksessuary</Ид>
						<Наименование>Аксессуары для ванной комнаты</Наименование>
						<Группы />
					</Группа>
				</Группы>
			</Группа>
		</Группы>
	</Классификатор>
</КоммерческаяИнформация>';
        header("Content-Type: application/xml");
        echo $xmlString;
    }

    static function mode_query_package_goods($goods, int $next_pack_num)
    {
        $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
<КоммерческаяИнформация ВерсияСхемы="2.021" ПараметрПакета="' . $next_pack_num . '" ДатаФормирования="2023-10-20T23:57:16">
	<Каталог>
		<Ид>catalog-masterdom</Ид>
		<ИдКлассификатора>1c-classificator-masterdom</ИдКлассификатора>
		<Наименование>Мастердом</Наименование>
		<Товары>';
		foreach ($goods as $good) {
		    $xmlString .= '<Товар>
				<Ид>goodI' . $good['id'] . '</Ид>
				<Наименование><![CDATA[' . $good['title'] . ']]></Наименование>
				<Артикул><![CDATA[' . $good['articul'] . ']]></Артикул>
				<Группы>
				<ИД>' . Data_1c::getGroups()[$good['subcategory']] . '</ИД>
				</Группы>
				<Описание></Описание>
				<Картинка></Картинка>
				<ЗначенияСвойств>
					<ЗначенияСвойства>
						<Ид>CML2_BASE_UNIT</Ид>
						<Значение><![CDATA[' . $good['edizm'] . ']]></Значение>
					</ЗначенияСвойства>
					<ЗначенияСвойства>
						<Ид>CML2_STOCK</Ид>
						<Значение><![CDATA[' . $good['stock'] . ']]></Значение>
					</ЗначенияСвойства>
					<ЗначенияСвойства>
						<Ид>CML2_PRODUCT_LINK</Ид>
						<Значение><![CDATA[' . $good['link'] . ']]></Значение>
					</ЗначенияСвойства>
					<ЗначенияСвойства>
						<Ид>CML2_LENGTH</Ид>
						<Значение>' . $good['length'] . '</Значение>
					</ЗначенияСвойства>
					<ЗначенияСвойства>
						<Ид>CML2_WIDTH</Ид>
						<Значение>' . $good['width'] . '</Значение>
					</ЗначенияСвойства>
					<ЗначенияСвойства>
						<Ид>CML2_HEIGHT</Ид>
						<Значение>' . $good['height'] . '</Значение>
					</ЗначенияСвойства>
					<ЗначенияСвойства>
						<Ид>CML2_THICKNESS</Ид>
						<Значение>' . $good['thickness'] . '</Значение>
					</ЗначенияСвойства>
					<ЗначенияСвойства>
						<Ид>CML2_DEPTH</Ид>
						<Значение>' . $good['depth'] . '</Значение>
					</ЗначенияСвойства>
					<ЗначенияСвойства>
						<Ид>CML2_FORMAT</Ид>
						<Значение><![CDATA[' . $good['format'] . ']]></Значение>
					</ЗначенияСвойства>
					<ЗначенияСвойства>
						<Ид>CML2_MANUFACTURER</Ид>
						<Значение><![CDATA[' . $good['producer'] . ']]></Значение>
					</ЗначенияСвойства>
					<ЗначенияСвойства>
						<Ид>CML2_COLLECTION</Ид>
						<Значение><![CDATA[' . $good['collection'] . ']]></Значение>
					</ЗначенияСвойства>
					<ЗначенияСвойства>
						<Ид>CML2_COUNTRY</Ид>
						<Значение><![CDATA[' . $good['country'] . ']]></Значение>
					</ЗначенияСвойства>
					<ЗначенияСвойства>
						<Ид>CML2_MATERIAL</Ид>
						<Значение><![CDATA[' . $good['material'] . ']]></Значение>
					</ЗначенияСвойства>
					<ЗначенияСвойства>
						<Ид>CML2_PRODUCT_USAGE</Ид>
						<Значение><![CDATA[' . $good['product_usage'] . ']]></Значение>
					</ЗначенияСвойства>
					<ЗначенияСвойства>
						<Ид>CML2_IMAGES</Ид>
						<Значение><![CDATA[' . $good['images'] . ']]></Значение>
					</ЗначенияСвойства>
					<ЗначенияСвойства>
						<Ид>CML2_VARIANTS</Ид>
						<Значение><![CDATA[' . $good['variants'] . ']]></Значение>
					</ЗначенияСвойства>
					<ЗначенияСвойства>
						<Ид>CML2_DATE_EDIT</Ид>
						<Значение><![CDATA[' . $good['date_edit'] . ']]></Значение>
					</ЗначенияСвойства>
				</ЗначенияСвойств>
			</Товар>';
		}
		$xmlString .= '</Товары>
	</Каталог>
</КоммерческаяИнформация>';
        header("Content-Type: application/xml");
        echo $xmlString;
    }
    
        static function mode_query_package_offers($goods, int $next_pack_num)
    {
        $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
<КоммерческаяИнформация ПараметрПакета="' . $next_pack_num . '" ВерсияСхемы="2.021" ДатаФормирования="2023-10-20T23:57:29">
	<ПакетПредложений>
		<Ид>catalog-masterdom</Ид>
		<ИдКлассификатора>1c-classificator-masterdom</ИдКлассификатора>
		<Наименование>Мастердом</Наименование>
		<ТипыЦен>
			<ТипЦены>
				<Ид>BASE</Ид>
				<Наименование>BASE</Наименование>
			</ТипЦены>
		</ТипыЦен>
		<Предложения>';
		foreach ($goods as $good) {
		    $xmlString .= '<Предложение>
				<Ид>goodI' . $good['id'] . '</Ид>
				<Наименование><![CDATA[' . $good['title'] . ']]></Наименование>
				<Артикул><![CDATA[' . $good['articul'] . ']]></Артикул>
				<Группы>
					<ИД>' . Data_1c::getGroups()[$good['subcategory']] . '</ИД>
				</Группы>
				<Описание></Описание>
				<Картинка></Картинка>
				<ХарактеристикиТовара>
					<ХарактеристикаТовара>
						<Наименование>Базовая единица</Наименование>
						<Значение><![CDATA[' . $good['edizm'] . ']]></Значение>
					</ХарактеристикаТовара>
					<ХарактеристикаТовара>
						<Наименование>Остатки</Наименование>
						<Значение><![CDATA[' . $good['stock'] . ']]></Значение>
					</ХарактеристикаТовара>
					<ХарактеристикаТовара>
						<Наименование>Ссылка на товар</Наименование>
						<Значение><![CDATA[' . $good['link'] . ']]></Значение>
					</ХарактеристикаТовара>
					<ХарактеристикаТовара>
						<Наименование>Длина</Наименование>
						<Значение>' . $good['length'] . '</Значение>
					</ХарактеристикаТовара>
					<ХарактеристикаТовара>
						<Наименование>Ширина</Наименование>
						<Значение>' . $good['width'] . '</Значение>
					</ХарактеристикаТовара>
					<ХарактеристикаТовара>
						<Наименование>Высота</Наименование>
						<Значение>' . $good['height'] . '</Значение>
					</ХарактеристикаТовара>
					<ХарактеристикаТовара>
						<Наименование>Толщина</Наименование>
						<Значение>' . $good['thickness'] . '</Значение>
					</ХарактеристикаТовара>
					<ХарактеристикаТовара>
						<Наименование>Глубина</Наименование>
						<Значение>' . $good['depth'] . '</Значение>
					</ХарактеристикаТовара>
					<ХарактеристикаТовара>
						<Наименование>Формат</Наименование>
						<Значение><![CDATA[' . $good['format'] . ']]></Значение>
					</ХарактеристикаТовара>
					<ХарактеристикаТовара>
						<Наименование>Производитель</Наименование>
						<Значение><![CDATA[' . $good['producer'] . ']]></Значение>
					</ХарактеристикаТовара>
					<ХарактеристикаТовара>
						<Наименование>Коллекция</Наименование>
						<Значение><![CDATA[' . $good['collection'] . ']]></Значение>
					</ХарактеристикаТовара>
					<ХарактеристикаТовара>
						<Наименование>Страна</Наименование>
						<Значение><![CDATA[' . $good['country'] . ']]></Значение>
					</ХарактеристикаТовара>
					<ХарактеристикаТовара>
						<Наименование>Материал</Наименование>
						<Значение><![CDATA[' . $good['material'] . ']]></Значение>
					</ХарактеристикаТовара>
					<ХарактеристикаТовара>
						<Наименование>Назначение</Наименование>
						<Значение><![CDATA[' . $good['product_usage'] . ']]></Значение>
					</ХарактеристикаТовара>
					<ХарактеристикаТовара>
						<Наименование>Картинки</Наименование>
						<Значение><![CDATA[' . $good['images'] . ']]></Значение>
					</ХарактеристикаТовара>
					<ХарактеристикаТовара>
						<Наименование>Варианты исполнения</Наименование>
						<Значение><![CDATA[' . $good['variants'] . ']]></Значение>
					</ХарактеристикаТовара>
					<ХарактеристикаТовара>
						<Наименование>Дата последнего изменения</Наименование>
						<Значение><![CDATA[' . $good['date_edit'] . ']]></Значение>
					</ХарактеристикаТовара>
				</ХарактеристикиТовара>
				<Цены>
					<Цена>
						<ИдТипаЦены>BASE</ИдТипаЦены>
						<ЦенаЗаЕдиницу>' . $good['price'] . '</ЦенаЗаЕдиницу>
						<Валюта>RUB</Валюта>
						<КоличествоОт></КоличествоОт>
						<КоличествоДо></КоличествоДо>
					</Цена>
				</Цены>
			</Предложение>';
		}
		$xmlString .= '</Предложения>
	</ПакетПредложений>
</КоммерческаяИнформация>';
        header("Content-Type: application/xml");
        echo $xmlString;
    }
}
