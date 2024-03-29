<?php

namespace functions;

require __DIR__ . "/../vendor/autoload.php";

use DiDom\Document;
use DOMDocument;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;

class Categories
{
    static function finalSubcategory($provider, $providerCategory = 'none', $providerSubcategory = null, $title = null, $product_link = null, $chars = null)
    {
        try {
            $providerCategory = $providerCategory ?? 'null';
            $providerSubcategory = $providerSubcategory ?? 'null';
            $title = $title ?? 'null';
            $all_subcategories = Parser::getSubcategoriesList();
            $chars = json_decode($chars, 1);

            // if (in_array($providerSubcategory, $all_subcategories) and !($provider == 'domix' and preg_match("#(Мебель для ванной)#", $providerCategory)) and !($provider == 'ampir' and (preg_match("#(Другое)#", $providerSubcategory)) or $chars['Назначение'] == 'Клей для лепнины')) return $providerSubcategory;

            $keys = [
                5 => [ //'Мебель для ванной',
                    ($provider == 'dplintus' and preg_match("#(Полки для ванной и душа)#", $providerCategory)),
                    ($provider == 'domix' and preg_match("#(Мебель для ванной)#", $providerCategory)),
                    ($provider == 'laparet' and preg_match("#(Пенал|Зеркал|Тумб)#", $title)),
                    ($provider == 'ntceramic' and preg_match("#(Мебель для ванной)#", $providerSubcategory)),
                    ($provider == 'mosplitka' and (preg_match("#(Зеркал|Панель с полками|Полка|Шкаф|Пенал|Тумба|Мебель для ванной)#", $title))),
                ],
                4 => [ //'Смесители',
                    (preg_match("#(Смесител)#", $title)),
                    (preg_match("#(Смесител)#", $providerSubcategory)),
                    ($provider == 'mosplitka' and preg_match("#(Смесител)#", $title)),
                    ($provider == 'laparet' and preg_match("#(Смесител)#", $title)),
                    ($provider == 'ntceramic' and (preg_match("#(Смесител)#", $providerSubcategory) or preg_match("#(Смеситель)#", $title))),
                    ($provider == 'domix' and preg_match("#(Смесител|смеситель|Набор смесител|Гигиенический набор|Гигиеническая лейка|Комплект смесител)#", $title)),
                    ($provider == 'domix' and preg_match("#(Смесител)#", $providerSubcategory)),
                ],
                0 => [ //'Раковины'
                    (preg_match("#(Раковин|Рукомойник|раковин)#", $title) and !preg_match("#(для раковин|для слива|под раковину)#", $title)),
                    // ($provider == 'laparet' and preg_match("#(Раковин)#", $title)),
                    // ($provider == 'ntceramic' and (preg_match("#(Раковин)#", $providerSubcategory) or preg_match("#(Раковин)#", $title))),
                    // ($provider == 'mosplitka' and preg_match("#(Раковина|Рукомойник)#", $title)),
                    // ($provider == 'domix' and preg_match("#(Раковина)#", $title)),
                ],
                1 => [ //'Унитазы, писсуары и биде',
                    ($provider == 'laparet' and preg_match("#(Унитаз|Писсуар|Сиденье|Бачок)#", $title)),
                    ($provider == 'mosplitka' and preg_match("#(Унитаз|Биде|Писсуар)#", $title)),
                    ($provider == 'domix' and preg_match("#(Унитаз)#", $title)),
                ],
                2 => [ //'Ванны',
                    (preg_match("#(Ванны|ванна|Ванна)#", $title)
                        or preg_match("#(Ванны|ванна|Ванна)#", $providerSubcategory)
                        and !preg_match("#(для ванны)#", $title)),
                    // ($provider == 'ntceramic' and (preg_match("#(Ванны)#", $providerSubcategory) or preg_match("#(Ванн)#", $title))),
                    // ($provider == 'mosplitka' and preg_match("#(ванна|Ванны)#", $title)),
                    // ($provider == 'domix' and preg_match("#(Ванна)#", $title)),
                ],
                3 => [ //'Душевые',
                    (preg_match("#(гигиенический душ|Гигиенический душ)#", $title)),
                    ($provider == 'laparet' and preg_match("#(Душ|душ)#", $title)),
                    ($provider == 'domix' and preg_match("#(Душ|душ)#", $title) and $providerCategory != 'Кварц-винил'),
                    ($provider == 'mosplitka' and preg_match("#(Душ|Боковая стенка|Поддоны)#", $title) and $providerCategory == "Сантехника"),
                    ($provider == 'mosplitka' and preg_match("#(Душевые)#", $providerSubcategory)),
                ],
                6 => [ //'Аксессуары для ванной комнаты',
                    ($provider == 'mosplitka' and (preg_match("#(Стакан|Держатель|аксессуар|Аксессуар|Шторка|Мыльница|Корзина|для писсуаров|Крючок|Ершик|Дозатор)#", $title))),
                    ($provider == 'domix' and (preg_match("#(Хранение)#", $providerSubcategory or preg_match("#(Стакан|Держатель|аксессуар|Аксессуар|Шторка|Мыльница|Корзина|для писсуаров|Крючок|Ерши)#", $title)) and preg_match("(Сантехника)", $providerCategory))),
                ],
                8 => [ //'Полотенцесушители',                    
                    (preg_match("#(Полотенцесушител|Полотенцедержател)#", $providerSubcategory)),
                    (preg_match("#(Полотенцесушител|Полотенцедержател)#", $title) and !preg_match("#(под раковину)#", $title)),
                    (preg_match("#(Полотенцесушител|Полотенцедержател)#", $providerCategory)),
                    ($provider == 'mosplitka' and preg_match("#(Полотенцесушител)#", $providerSubcategory)),
                    ($provider == 'domix' and preg_match("#(Полотенцесушител)#", $title)),
                ],
                17 => [ //'Фотообои и фрески',
                    (preg_match("#(фотообои|Фотообои)#", $providerCategory)),
                    (preg_match("#(Фотообои|фотообои)#", $providerSubcategory)),
                    // ($provider == 'domix' and preg_match("#(фотообои)#", $providerCategory)),
                ],
                9 => [ //'Декоративные обои',
                    ($provider == 'domix' and preg_match("#(Обои)#", $providerCategory)),
                    ($provider == 'mosplitka' and preg_match("#(Декоративные обои)#", $title))
                ],
                10 => [ //'Керамогранит',
                    ($provider == 'tdgalion' and preg_match("#(Керамогранит)#", $providerCategory)),
                    ($provider == 'laparet' and preg_match("#(Керамогранит|Керамопаркет)#", $providerCategory)),
                    ($provider == 'ntceramic' and preg_match("#(Керамогранит)#", $providerSubcategory)),
                    ($provider == 'artkera' and preg_match("#(Керамогранит)#", $providerSubcategory)),
                    ($provider == 'mosplitka' and preg_match("#(Керамогранит)#", $title)),
                    ($provider == 'domix' and preg_match("#(Керамогранит)#", $title)),
                    ($provider == 'domix' and preg_match("#(Вставка)#", $title) and preg_match("#(Плитка и керамогранит)#", $providerCategory)),
                    ($provider == 'artkera' and preg_match("#(Настенная|Декор|Панно|Бордюр)#", $providerSubcategory)),
                    ($provider == 'domix' and (preg_match("#(Плитка настенная|Декор настенный|Панно)#", $title) or (preg_match("#(Бордюр для обоев)#", $providerSubcategory)))),
                ],
                52 => [ //'Настенная плитка', перенесли в керамогранит
                ],
                11 => [ //'Керамическая плитка',
                    ($provider == 'mosplitka' and preg_match("#(Керамическая плитка)#", $title)),
                    ($provider == 'tdgalion' and preg_match("#(Керамическая плитка)#", $providerCategory)),
                    ($provider == 'artkera' and preg_match("#(Напольная)#", $providerSubcategory)),
                    ($provider == 'domix' and preg_match("#(Плитка)#", $title) and !preg_match("#(Плитка настенная)#", $title)),
                ],
                12 => [ //'Натуральный камень',
                ],
                13 => [ //'Мозаика',
                    ($provider == 'laparet' and preg_match("#(Мозаика)#", $providerCategory)),
                    ($provider == 'artkera' and (preg_match("#(Мозаика)#", $providerCategory) or (preg_match("#(Мозаика)#", $providerSubcategory)))),
                    ($provider == 'domix' and preg_match("#(Мозаика)#", $providerSubcategory)),
                    ($provider == 'tdgalion' and preg_match("#(Мозаика)#", $providerCategory)),
                    ($provider == 'mosplitka' and preg_match("#(Мозаика)#", $title) and !preg_match("#(Душевые)#", $providerSubcategory)),
                ],
                14 => [ //'Кухонные мойки',
                ],
                15 => [ //'Ступени и клинкер',
                ],
                16 => [ //'SPC-плитка', это кварцвиниловые полы
                ],
                18 => [ //'Обои под покраску',
                ],
                19 => [ //'Штукатурка',
                    ($provider == 'centerkrasok' and preg_match("#(Штукатурки)#", $providerCategory)),
                ],
                20 => [ //'Розетки',
                    ($provider == 'olimp' and preg_match("#(Розетки)#", $providerSubcategory)),
                    ($provider == 'ampir' and preg_match("#(розетк)#", $title)),
                    ($provider == 'evroplast' and preg_match("#(розетка)#", $title)),
                ],
                21 => [ //'Карнизы',
                    ($provider == 'dplintus' and preg_match("#(Карниз)#", $providerCategory)),
                    ($provider == 'evroplast' and preg_match("#(карниз|сандрики)#", $providerCategory)),
                    ($provider == 'domix' and preg_match("#(Карниз)#", $providerSubcategory)),
                    ($provider == 'olimp' and preg_match("#(Карниз)#", $providerSubcategory)),
                    ($provider == 'ampir' and ($chars['Назначение'] == "Сандрики" or $chars['Назначение'] == "Карнизы"))
                ],
                22 => [ //'Молдинги',
                    ($provider == 'evroplast' and preg_match("#(молдинг|угловые элементы)#", $providerCategory)),
                    ($provider == 'domix' and preg_match("#(Молдинг)#", $providerSubcategory)),
                    ($provider == 'alpinefloor' and preg_match("#(Молдинг)#", $providerSubcategory)),
                    ($provider == 'olimp' and preg_match("#(Молдинг)#", $providerSubcategory)),
                    ($provider == 'ampir' and $chars['Назначение'] == 'Молдинги')
                ],
                23 => [ //'null',

                ],
                24 => [ //'Дверное обрамление',
                    ($provider == 'ampir' and $chars['Назначение'] == 'Дверное обрамление'),
                    ($provider == 'evroplast' and preg_match("#(обрамление дверей)#", $providerCategory)),
                    ($provider == 'olimp' and preg_match("#(Дверной наличник)#", $providerSubcategory)),
                ],
                25 => [ //'Потолочный декор',
                ],
                27 => [ //'Ламинат',
                    ($provider == 'domix' and preg_match("#(Ламинат)#", $providerCategory)),
                    ($provider == 'alpinefloor' and preg_match("#(Ламинат)#", $providerCategory)),
                    ($provider == 'olimp' and preg_match("#(Ламинат|ламинат)#", $providerSubcategory) and !preg_match("#(Кварц-винил)#", $providerSubcategory)),
                ],
                28 => [ //'Инженерная доска',
                    ($provider == 'domix' and preg_match("#(Инженерная доска)#", $providerCategory)),
                    ($provider == 'olimp' and preg_match("#(Инженерная доска)#", $providerSubcategory)),
                    ($provider == 'alpinefloor' and preg_match("#(Инженерная доска)#", $providerCategory)),
                ],
                29 => [ //'Паркетная доска',
                    ($provider == 'domix' and preg_match("#(Паркетная доска)#", $providerCategory)),
                    ($provider == 'olimp' and preg_match("#(паркет|Паркет)#", $providerSubcategory) and !preg_match("#(для паркета|Штучный паркет)#", $providerSubcategory)),
                ],
                30 => [ //'Штучный паркет',
                    ($provider == 'olimp' and preg_match("#(Штучный паркет)#", $providerSubcategory)),
                ],
                31 => [ //'Виниловые полы',
                ],
                32 => [ //'Подложка под напольные покрытия',
                    ($provider == 'domix' and preg_match("#(Подложк)#", $providerCategory)),
                    ($provider == 'finefloor' and preg_match("#(Подложк)#", $providerSubcategory)),
                    ($provider == 'alpinefloor' and preg_match("#(Подложк)#", $providerSubcategory)),
                    ($provider == 'olimp' and preg_match("#(Подложка под напольные покрытия)#", $providerSubcategory)),
                    ($provider == 'fargo' and preg_match("#(Подложка)#", $providerSubcategory)),

                ],
                33 => [ //'Плинтус',
                    ($provider == 'dplintus' and preg_match("#(Плинтус)#", $providerCategory)),
                    ($provider == 'domix' and preg_match("#(Плинтус)#", $providerCategory)),
                    ($provider == 'finefloor' and preg_match("#(Плинтус)#", $providerSubcategory)),
                    ($provider == 'alpinefloor' and preg_match("#(плинтус)#", $providerSubcategory)),
                    ($provider == 'evroplast' and preg_match("#(плинтус)#", $providerCategory)),
                    ($provider == 'olimp' and preg_match("#(Плинтус напольный)#", $providerSubcategory)),
                    ($provider == 'fargo' and preg_match("#(Плинтус)#", $providerSubcategory)),
                    ($provider == 'ampir' and ($chars['Назначение'] == 'Плинтусы' or preg_match("#(плинтус|Плинтус)#", $title)))
                ],
                34 => [ //'Массивная доска',
                    ($provider == 'olimp' and preg_match("#(Массивная доска)#", $providerSubcategory)),
                ],
                35 => [ //'Пробковое покрытие',
                    ($provider == 'olimp' and preg_match("#(Пробковый пол)#", $providerSubcategory)),
                ],
                36 => [ //'Линолеум',
                    ($provider == 'domix' and preg_match("#(Линолеум)#", $providerCategory))
                ],
                37 => [ //'Кварцвиниловые полы',
                    ($provider == 'domix' and preg_match("#(Кварц-винил)#", $providerCategory)),
                    ($provider == 'alpinefloor' and preg_match("#(Кварцвинил)#", $providerCategory)),
                    ($provider == 'finefloor' and !preg_match("#(Плинтус|Уборка|Клеевые|Подложк)#", $providerSubcategory)),
                    ($provider == 'mosplitka' and preg_match("#(SPC-плитка)#", $title)),
                    ($provider == 'alpinefloor' and (preg_match("#(spc-ламинат)#", $providerCategory) or preg_match("#(SPC ламинат)#", $title))),
                    ($provider == 'olimp' and preg_match("#(Кварц-виниловый ламинат)#", $providerSubcategory)),
                    ($provider == 'fargo' and preg_match("#(Кварцевый ламинат)#", $providerSubcategory)),
                ],
                38 => [ //'Кварциниловые панели',
                    ($provider == 'alpinefloor' and preg_match("#(Кварц-виниловые самоклеящиеся стеновые панели)#", $providerCategory)),
                ],
                39 => [ //'Сопутствующие',
                    ($provider == 'domix' and preg_match("#(Сопутствующие)#", $providerCategory) and !preg_match("#(клей)#", $providerSubcategory)),
                    ($provider == 'centerkrasok' and preg_match("#(Инструменты)#", $providerCategory)),
                    ($provider == 'alpinefloor' and str_contains($product_link, 'https://alpinefloor.su/catalog/related-products/')),
                ],
                40 => [ //'Порожки',
                    ($provider == 'dplintus' and preg_match("#(Порожки)#", $providerCategory))
                ],
                41 => [ //'Профили',
                    ($provider == 'dplintus' and preg_match("#(Профили|профили)#", $providerCategory))
                ],
                42 => [ //'Ковры',
                    ($provider == 'domix' and preg_match("#(Ковры)#", $providerCategory))
                ],
                43 => [ //'Ковровые покрытия',
                    ($provider == 'domix' and preg_match("#(Ковровые покрытия)#", $providerCategory))
                ],
                44 => [ //'Панели',
                    ($provider == 'dplintus' and preg_match("#(Панели)#", $providerCategory)),
                    ($provider == 'domix' and preg_match("#(Панели)#", $providerCategory)),
                    ($provider == 'evroplast' and preg_match("#(панел)#", $providerCategory)),
                    ($provider == 'olimp' and preg_match("#(Панель стеновая)#", $providerSubcategory)),
                    ($provider == 'ampir' and preg_match("#(панель)#", $title))
                ],
                45 => [ //'Краски, эмали',
                    ($provider == 'domix' and preg_match("#(Краски)#", $providerCategory)),
                    ($provider == 'centerkrasok' and preg_match("#(Краски)#", $providerCategory)),
                ],
                46 => [ //'Грунтовки',
                    (preg_match("#(грунктовк|Грунтовк)#", $providerSubcategory)),
                    ($provider == 'centerkrasok' and preg_match("#(Грунтовки)#", $providerCategory)),
                    ($provider == 'domix' and preg_match("#(Грунтовк)#", $providerCategory)),
                    ($provider == 'olimp' and preg_match("#(Грунтовк)#", $providerSubcategory)),
                ],
                47 => [ //'Лаки и масла', 
                    (preg_match("#(Лак|Масло|лак|масло)#", $providerSubcategory)),
                    ($provider == 'centerkrasok' and preg_match("#(Лаки и масла)#", $providerCategory)),
                    ($provider == 'domix' and preg_match("#(Лак)#", $providerSubcategory)),
                    ($provider == 'olimp' and preg_match("#(Лак для|Масло для)#", $providerSubcategory)),

                ],
                48 => [ //'Антисептики и пропитки', 
                    ($provider == 'centerkrasok' and preg_match("#(Антисептики)#", $providerCategory)),
                ],
                49 => [ //'Затирки и клей',
                    (preg_match("#(клей|Клей)#", $providerSubcategory)),
                    ($provider == 'laparet' and preg_match("#(Клеевые|Клей|Затирк)#", $providerCategory)),
                    ($provider == 'domix' and ((preg_match("#(Сопуствующие)#", $providerCategory) and (preg_match("#(Клей)#", $title)) or preg_match("#Клей#", $providerSubcategory)))),
                    ($provider == 'evroplast' and preg_match("#клей#", $providerCategory)),
                    ($provider == 'centerkrasok' and preg_match("#(Клеи|затирки)#", $providerCategory)),
                    ($provider == 'finefloor' and preg_match("#(Клеевые)#", $providerSubcategory)),
                    ($provider == 'alpinefloor' and preg_match("#(Клей)#", $providerSubcategory)),
                    ($provider == 'olimp' and preg_match("#(Клей)#", $providerSubcategory)),
                    ($provider == 'ampir' and preg_match("#(Клей для лепнины)#", $chars['Назначение'])),
                ],
                50 => [ //'Камины',
                    ($provider == 'evroplast' and preg_match("#(камин)#", $providerCategory)),
                    ($provider == 'ampir' and $chars['Назначение'] == 'Камины')
                ],
                51 => [ //'Декоративные элементы',
                    ($provider == 'evroplast' and preg_match("#(декоративные элементы)#", $providerCategory)),
                    ($provider == 'ampir' and ($chars['Назначение'] == "Декоративные элементы" or $chars['Назначение'] == "Фасадный декор")),
                    ($provider == 'ampir' and $chars['Назначение'] == "Потолочный декор"),
                ],
                53 => [ //'Кронштейны, ниши',
                    ($provider == 'evroplast' and preg_match("#(кронштейн|ниши)#", $providerCategory)),
                    ($provider == 'ampir' and ($chars['Назначение'] == "Ниши" or $chars['Назначение'] == "Полки и кронштейны" or $chars['Назначение'] == "Кронштейны")),
                ],
                54 => [ //'Колонны',
                    ($provider == 'evroplast' and (preg_match("#(колонн|база|верхний фрагмент ствола|капитель|кольцо)#", $providerCategory) or preg_match("#(база)#", $title))),
                    ($provider == 'olimp' and preg_match("#(Колонны)#", $providerSubcategory)),
                    ($provider == 'ampir' and $chars['Назначение'] == 'Колонны и полуколонны'),
                ],
                55 => [ //'Шпатлевки',
                    (preg_match("#(Шпатлевка|шпатлевка)#", $providerSubcategory)),
                    ($provider == 'centerkrasok' and preg_match("#(Шпатлевки|шпатлевки)#", $providerCategory)),
                ],
                56 => [ //'Декоративное покрытие',
                    ($provider == 'centerkrasok' and preg_match("#(Декоративные дизайнерские покрытия)#", $providerCategory)),
                    ($provider == 'tdgalion' and preg_match("#(Декоративный камень)#", $providerCategory)),
                    ($provider == 'domix'
                        and preg_match("#(Декор|Бордюр)#", $title)
                        and !preg_match("#(Декор настенный)#", $title)),
                ],
                57 => [ //'Люстры, лампы, уличное освещение',
                    ($provider == 'mosplitka' and (preg_match("#(подсветк|Люстры|лампы|Светильник|светового|Торшеры|освещение|Спот)#", $providerCategory) or preg_match("#(светильник)#", $title))),
                    ($provider == 'olimp' and (preg_match("(#Скрытое освещение#)", $providerSubcategory))),
                ],
                58 => [ // 'Багет'
                    ($provider == 'ampir' and $chars['Назначение'] == 'Багет')
                ],
                // 59 => [ // 'Потолочный декор'
                // ],
                60 => [ // 'Пилястры"                
                    ($provider == 'olimp' and preg_match("#(Пилястры)#", $providerSubcategory)),
                    ($provider == 'ampir' and $chars['Назначение'] == "Пилястры"),
                    ($provider == 'domix' and preg_match("#(Пьедестал|Полупьедестал)#", $title)),
                    ($provider == 'evroplast' and preg_match("#(ствол|пьедестал|пилястр)#", $title)),
                ],
                7 => [ //'Комплектующие',
                    (preg_match("#(для раковин)#", $title)),
                    (preg_match("#(для ванны)#", $title)),
                    ($provider == 'mosplitka' and (preg_match("#(Клапан|клапан|Слив|Cифон|Инсталляци|труба|Насадка|Гофра|Гибкое соединение|Муфта|Отвод|инсталляци|Поручень для ванны|Вентиль|вентиль|Термостат|Кнопка смыва|Копки смыва|Излив|заглушка|Подголовник для ванны|Комплектующ|Шланг для|под слив)#", $title))),
                    ($provider == 'mosplitka' and (preg_match("#(Поддоны)#", $providerSubcategory))),
                    ($provider == 'domix' and preg_match("#(Инсталляц|инсталляц|Каркас|Экран для ванн|Термостат|Базовый модуль|Смывное устройство|Крышка-сидушка|Скрытая часть для|Излив|Уголок для|Слив|Крышка-сиденье|Комплект подключения|Клавиша)#", $title)),
                    ($provider == 'domix' and preg_match("#(Сиденья для унитаза)#", $providerSubcategory)),
                ],
                26 => [ //'Другое',
                    ($provider == 'dplintus' and preg_match("#(Аксессуары)#", $providerCategory)),
                    ($provider == 'evroplast'
                        and preg_match("#(арочн|архитрав|балясин|гибкие аналоги|дополнительные элементы|камни|кессон|крышки столба|монтажный комплект|накладные элементы|наличники|фризы|русты|подоконные элементы|основания|поручни|обрамление арок|орнаменты|откосы|столбы|составные элементы)#", $providerCategory)
                        and !preg_match("#(плинтус)", $providerCategory)),
                    ($provider == 'centerkrasok' and preg_match("#(Монтажные пены|Промышленные покрытия|Растворители|Стеклохолст|Эпоксидные)#", $providerCategory)),
                    ($provider == 'alpinefloor' and str_contains($product_link, 'https://alpinefloor.su/catalog/quartz-tiles-vinyl-for-walls/')),
                    ($provider == 'domix' and preg_match("#(Сопуствующие)#", $providerCategory) and !preg_match("#(Клей|Грутновк)#", $title)),
                    ($provider == 'finefloor' and preg_match("#(Сопутствующие)#", $providerCategory) and !preg_match("#(Клеевые|Плинтус|Подложк)#", $providerSubcategory)),
                    ($provider == 'mosplitka' and (preg_match("#(Водонагреватель|Финская сауна)#", $title))),
                    ($provider == 'olimp' and preg_match("#(Фанера пиленная|Средство по уходу|Смесь для ремонта|Дверной наличник|Распродажа)#", $providerSubcategory)),
                    ($provider == 'ampir' and ($chars['Назначение'] == 'Багет' or $chars['Назначение'] == 'Библиотечные системы')),
                    (($provider == 'domix' or $provider == 'centerkrasok') and preg_match("#(гель|герметик|Герметик|Гель)#", $title)),

                ],
            ];

            foreach ($keys as $key => $stmnts) {

                foreach ($stmnts as $stmnt) {
                    if ($stmnt) {
                        return $all_subcategories[$key];
                    }
                }
            }

            return $providerSubcategory = 'null' ? null : $providerSubcategory;
        } catch (\Throwable $e) {
            var_dump($e);
        }
    }

    static function finalCategory($provider, $providerCategory = null, $providerSubcategory = null, $title = null, $product_link = null, $chars = null)
    {
        $providerCategory = $providerCategory ?? 'null';
        $providerSubcategory = $providerSubcategory ?? 'null';
        $title = $title ?? 'null';
        $all_categories = Parser::getCategoriesList();
        $chars = json_decode($chars, 1);
        var_dump($provider == 'ampir' and ($chars['Назначение'] == 'Плинтусы' or preg_match("#(плинтус|Плинтус)#)", $title)));

        // if (
        //     in_array($providerCategory, $all_categories)
        //     and !($provider == 'domix' and preg_match("#(Плитка и керамогранит)#", $providerCategory))
        //     and !($provider == 'ampir' and ($chars['Назначение'] == 'Плинтусы' or preg_match("#(плинтус|Плинтус)#", $title)))
        // ) {
        //     return $providerCategory;
        // }

        $keys = [
            0 => [ //'Обои и настенные покрытия'
                ($provider == 'domix' and preg_match("#(Обои|фотообои)#", $providerCategory)),
                ($provider == 'domix' and (preg_match("#(Плитка настенная|Декор настенный|Панно)#", $title) or (preg_match("#(Бордюр для обоев)#", $providerSubcategory)))),
                ($provider == 'alpinefloor' and preg_match("#(Кварц-виниловые самоклеящиеся стеновые панели)#", $providerCategory)),
                ($provider == 'olimp' and preg_match("#(Стеновые панели)#", $providerSubcategory)),

            ],
            1 => [ //'Напольные покрытия',
                ($provider == 'domix' and preg_match("#(Инженерная доска|Плитнус|Кварц-винил|Линолеум|Ламинат|Ковровые|Ковры|Паркет|Подложка)#", $providerCategory)),
                ($provider == 'alpinefloor' and (preg_match("#(Инженерная доска|Кварцвинил|spc-ламинат|Ламинат)#", $providerCategory) or preg_match("#(Подложка|плинтус)#", $providerSubcategory) or preg_match("#(ламинат)#", $title))),
                ($provider == 'finefloor' and !preg_match("#(Уборка|Клеевые|Подложк)#", $providerSubcategory)),
                ($provider == 'dplintus' and preg_match("#(Плинтус)#", $providerCategory)),
                ($provider == 'olimp' and preg_match("#(Ламинат|ламинат|Инженерная доска|Массивная доска|паркет|Паркет|Плинтус|Подложка|Пробковый пол|Фанера пиленная)#", $providerSubcategory) and !preg_match("#(для паркета)#", $providerSubcategory)),
                ($provider == 'fargo' and preg_match("#(Плинтус|Подложка|Кварцевый ламинат)#", $providerCategory)),
                ($provider == 'mosplitka' and preg_match("#SPC-плитка", $title)),
                ($provider == 'evroplast' and preg_match("#(плинтус)#", $providerCategory)),
                ($provider == 'ampir' and ($chars['Назначение'] == 'Плинтусы' or preg_match("#(плинтус|Плинтус)#", $title))),
            ],
            2 => [ //'Плитка и керамогранит',
                ($provider == 'dplintus' and preg_match("#(Аксессуары)#", $providerCategory)),
                ($provider == 'domix' and preg_match("#(Плитка напольная|Керамогранит|Мозаика)#", $title)),
                ($provider == 'tdgalion' and preg_match("#(Керамическая плитка|Керамогранит|Мозаика|Декоративный камень)#", $providerCategory)),
                ($provider == 'laparet' and preg_match("#(Керамопаркет|Мозаика)#", $providerCategory)),
                ($provider == 'artkera' and preg_match("#(Керамогранит|Напольная|Мозаика)#", $providerSubcategory)),
                ($provider == 'artkera' and preg_match("#(Настенная|Декор|Панно|Бордюр)#", $providerSubcategory)),
                ($provider == 'domix' and preg_match("#(Декор)#", $title) and !preg_match("#(Декор настенный)#", $title)),
                ($provider == 'domix' and preg_match("#(Плитка и керамогранит)#", $providerCategory)),
                ($provider == 'domix' and preg_match("#(Бордюр)#", $title)),
            ],
            3 => [ //'Сантехника',
                ($provider == 'dplintus' and preg_match("#(Полки для ванной и душа)#", $providerCategory)),
                ($provider == 'domix' and preg_match("#(Мебель для ванной)#", $providerCategory)),
            ],
            4 => [ //'Краски',
                ($provider == 'domix' and (preg_match("#(Краски)#", $providerCategory) or preg_match("#(Сопутствующие)#", $providerCategory))),
                ($provider == 'laparet' and preg_match("#(Клеевые|Клей|Затирк)#", $providerCategory)),
                ($provider == 'centerkrasok'),
                ($provider == 'alpinefloor' and preg_match("#(Клей)#", $providerSubcategory)),
                ($provider == 'evroplast' and preg_match("#клей#", $providerCategory)),
                ($provider == 'finefloor' and preg_match("#(Уборка|Клеевые)#", $providerSubcategory)),
                ($provider == 'ampir' and $chars['Назначение'] == 'Клей для лепнины'),
                ($provider == 'olimp' and preg_match("#(Грунтовк|Клей|Лак для|Масло для|Смесь для ремонта|Средство по уходу)#", $providerSubcategory)),
            ],
            5 => [ //'Лепнина',
                ($provider == 'domix' and preg_match("#(Панели и декор)#", $providerCategory)),
                ($provider == 'dplintus' and preg_match("#(Карнизы|профили|Панели для|Профили для|Порожки|Углы и профили)#", $providerCategory)),
                ($provider == 'evroplast' and !preg_match("#(клей|плинтус)#", $providerCategory)),
                ($provider == 'alpinefloor' and preg_match("#(Молдинг)#", $providerSubcategory)),
                ($provider == 'olimp' and preg_match("#(Молдинг|Карниз|Колонн|Розетк|Пилястр|Дверной наличник)#", $providerSubcategory)),
            ],
            6 => [ //'Свет',
                ($provider == 'mosplitka' and (preg_match("#(подсветк|Люстры|лампы|Светильник|светового|Торшеры|освещение|Спот)#", $providerCategory) or preg_match("#(светильник)#", $title))),
                ($provider == 'olimp' and preg_match("#(Скрытое освещение)#", $providerSubcategory)),
            ],
        ];

        foreach ($keys as $key => $stmnts) {
            foreach ($stmnts as $stmnt) {
                if ($stmnt) {
                    return $all_categories[$key];
                }
            }
        }

        return $providerCategory = 'null' ? null : $providerCategory;
    }

    static function getCategoriesByPath(array $path, $provider)
    {
        $all_categories = Parser::getCategoriesList($provider);
        $all_subcategories = Parser::getSubcategoriesList($provider);

        $keys = [
            'ntceramic' => [
                'сантехника' => [
                    'category' => $all_categories[3],
                    'subcategory' => null, //в характеристиках - "тип"
                ],
                'керамогранит' => [
                    'category' => $all_categories[2],
                    'subcategory' => $all_subcategories[10],
                ],
                'мебель' => [
                    'category' => $all_categories[3],
                    'subcategory' => $all_subcategories[5],
                ],
            ],
            'laparet' => [
                'сантехника' => [
                    'category' => $all_categories[3],
                    'subcategory' => null, //в характеристиках - "категория"
                ],
                'керамогранит' => [
                    'category' => $all_categories[2],
                    'subcategory' => $all_subcategories[10],
                ],
                'керамическая плитка' => [
                    'category' => $all_categories[2],
                    'subcategory' => $all_subcategories[11],
                ],
            ],
            'domix' => [],
            'dplintus' => [],
            'centerkrasok' => [],
            'ampir' => [],
            'alpinefloor' => [],
            'mosplitka' => [],
        ];

        $result = [
            'category' => null,
            'subcategory' => null,
        ];


        foreach ($path as $path_key => $path_value) {
            $path_value = $path_value->text();
            foreach ($keys[$provider] as $category_key => $category_value) {
                if (str_contains(trim(mb_strtolower($path_value)), $category_key)) {
                    $category_value['category_key'] = $path_key;
                    return $category_value;
                }
            }

            //если категории не прописаны в моем массиве $keys - прописываются категории поставщика

            if (!str_contains(trim(mb_strtolower($path_value)), 'каталог') and !str_contains(trim(mb_strtolower($path_value)), 'главная')) {

                if (!isset($result['category'])) {
                    $result['category'] = $path_value;
                } elseif (isset($result['category']) and !isset($result['subcategory'])) {
                    $result['subcategory'] = $path_value;
                }
            };
        }

        return $result;
    }

    static function getSubcategoryByPath(array $path, $provider, $category_key)
    {
        $all_subcategories = Parser::getSubcategoriesList($provider);
        if ($path[$category_key + 2]) {
            return $path[$category_key + 2]->text(); //плюс два, потому что $document->find() добавляет по две копии почему-то
        }
        return null;
    }


    static function getCategoriesByTitle($title, $provider)
    {
        $all_categories = Parser::getCategoriesList();
        $all_subcategories = Parser::getSubcategoriesList();

        if (!$title) {
            return [
                'category' => null,
                'subcategory' => null,
            ];
        }

        $title = mb_strtolower($title);

        $keys = [
            'olimpparket' => [
                $all_subcategories[27] => str_contains($title, "ламинат"),
                $all_subcategories[28] => str_contains($title, "инженерная доска"),
                $all_subcategories[29] => str_contains($title, "паркетная доска"),
                $all_subcategories[30] => str_contains($title, "штучный паркет"),
                $all_subcategories[31] => str_contains($title, "виниловый пол"),
                $all_subcategories[32] => str_contains($title, "подложка"),
                $all_subcategories[33] => str_contains($title, "плинтус"),
                $all_subcategories[34] => str_contains($title, "массивная доска"),
                $all_subcategories[35] => str_contains($title, "пробковое покрытие"),
                $all_subcategories[36] => str_contains($title, "линолиум"),
            ],
        ];

        foreach ($keys[$provider] as $key => $stmnt) {
            if ($stmnt) {
                return [
                    'category' => $all_categories[1],
                    'subcategory' => $key,
                ];
            }
        }
        return null;

        $keys = [
            'olimpparket' => [
                'сантехника' => [
                    'category' => $all_categories[3],
                    'subcategory' => null, //в характеристиках - "тип"
                ],
                'керамогранит' => [
                    'category' => $all_categories[2],
                    'subcategory' => $all_subcategories[10],
                ],
                'мебель' => [
                    'category' => $all_categories[3],
                    'subcategory' => $all_subcategories[5],
                ],
            ],
        ];

        return [
            'category' => null,
            'subcategory' => null,
        ];
    }

    static function getPath(array $path_res)
    {
        $path = array();
        foreach ($path_res as $value) {
            if (!preg_match("#(каталог|главная|главную)#", mb_strtolower($value->text()))) {
                $path[] = $value;
            }
        }
        // echo "path из Categories::getPath() - ";
        // TechInfo::preArray($path);

        return $path;
    }

    static function getCategoryAmpir($url_parser): string|null
    {
        $all_categories = Parser::getCategoriesList();

        if (preg_match("#ampir.ru/catalog/oboi/.*#", $url_parser)) {
            $category = $all_categories[0];
        } elseif (preg_match("#ampir.ru/catalog/lepnina/.*#", $url_parser)) {
            $category = $all_categories[5];
        } elseif (preg_match("#ampir.ru/catalog/kraski/.*#", $url_parser)) {
            $category = $all_categories[4];
        } elseif (preg_match("#ampir.ru/catalog/shtukaturka/.*#", $url_parser)) {
            $category = $all_categories[4];
        } elseif (preg_match("#ampir.ru/catalog/rozetki/.*#", $url_parser)) {
            $category = $all_categories[5];
        }

        return $category ?? null;
    }

    static function getSubcategoryAmpir(string $url_parser, string $title = null, string $product_usages = null): string|null
    {
        $all_subcategories = Parser::getSubcategoriesList();

        if (preg_match("#ampir.ru/catalog/shtukaturka/.*#", $url_parser)) {
            $subcategory = $all_subcategories[19];
        } elseif (preg_match("#ampir.ru/catalog/rozetki/.*#", $url_parser)) {
            $subcategory = $all_subcategories[20];
        } elseif (preg_match("#ampir.ru/catalog/kraski/.*#", $url_parser)) {
            $subcategory = $all_subcategories[45];
        } elseif (preg_match("#ampir.ru/catalog/oboi/.*#", $url_parser) and str_contains(mb_strtolower($title), 'обои под покраску')) {
            $subcategory = $all_subcategories[18];
        } elseif (preg_match("#ampir.ru/catalog/oboi/.*#", $url_parser) and str_contains(mb_strtolower($title), 'фотообои')) {
            $subcategory = $all_subcategories[17];
        } elseif (preg_match("#ampir.ru/catalog/oboi/.*#", $url_parser)) {
            $subcategory = $all_subcategories[9];
        } elseif (preg_match("#ampir.ru/catalog/lepnina/.*#", $url_parser) and str_contains(mb_strtolower($product_usages), 'карниз')) {
            $subcategory = $all_subcategories[21];
        } elseif (preg_match("#ampir.ru/catalog/lepnina/.*#", $url_parser) and str_contains(mb_strtolower($product_usages), 'дверное обрамление')) {
            $subcategory = $all_subcategories[24];
        } elseif (preg_match("#ampir.ru/catalog/lepnina/.*#", $url_parser) and str_contains(mb_strtolower($product_usages), 'молдинг')) {
            $subcategory = $all_subcategories[22];
        } elseif (preg_match("#ampir.ru/catalog/lepnina/.*#", $url_parser) and str_contains(mb_strtolower($product_usages), 'плинтус')) {
            $subcategory = $all_subcategories[23];
        } elseif (preg_match("#ampir.ru/catalog/lepnina/.*#", $url_parser) and str_contains(mb_strtolower($product_usages), 'потолочный декор')) {
            $subcategory = $all_subcategories[25];
        } elseif (preg_match("#ampir.ru/catalog/lepnina/.*#", $url_parser)) {
            $subcategory = $all_subcategories[26];
        }

        return $subcategory ?? null;
    }
}
