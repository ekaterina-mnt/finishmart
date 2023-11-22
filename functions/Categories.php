<?php

namespace functions;

require __DIR__ . "/../vendor/autoload.php";

use DiDom\Document;
use DOMDocument;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;

class Categories
{
    static function finalSubcategory($provider, $providerCategory = 'none', $providerSubcategory = null, $title = null, $product_link = null)
    {
        $providerCategory = $providerCategory ?? 'null';
        $providerSubcategory = $providerSubcategory ?? 'null';
        $title = $title ?? 'null';
        $all_subcategories = Parser::getSubcategoriesList();

        if (in_array($providerSubcategory, $all_subcategories) and !($provider == 'domix' and preg_match("#(Мебель для ванной)#", $providerCategory))) return $providerSubcategory;

        $keys = [
            0 => [ //'Раковины'
                ($provider == 'laparet' and preg_match("#(Раковин)#", $title)),
                ($provider == 'ntceramic' and (preg_match("#(Раковин)#", $providerSubcategory) or preg_match("#(Раковин)#", $title))),
                ($provider == 'mosplitka' and preg_match("#(Раковина|Рукомойник)#", $title)),
                ($provider == 'domix' and preg_match("#(Раковина)#", $title)),
            ],
            1 => [ //'Унитазы, писсуары и биде',
                ($provider == 'laparet' and preg_match("#(Унитаз|Писсуар|Сиденье|Бачок)#", $title)),
                ($provider == 'mosplitka' and preg_match("#(Унитаз|Биде|Писсуар)#", $title)),
                ($provider == 'domix' and preg_match("#(Унитаз)#", $title)),
            ],
            2 => [ //'Ванны',
                ($provider == 'ntceramic' and (preg_match("#(Ванны)#", $providerSubcategory) or preg_match("#(Ванн)#", $title))),
                ($provider == 'mosplitka' and preg_match("#(ванна)#", $title)),
                ($provider == 'domix' and preg_match("#(Ванна)#", $title)),
            ],
            3 => [ //'Душевые',
                ($provider == 'laparet' and preg_match("#(Душ|душ)#", $title)),
                ($provider == 'domix' and preg_match("#(Душ|душ)#", $title)),
                ($provider == 'mosplitka' and preg_match("#(Душ|Боковая стенка)#", $title) and $providerCategory == "Сантехника"),
            ],
            4 => [ //'Смесители',
                ($provider == 'mosplitka' and preg_match("#(Cмеситель)#", $title)),
                ($provider == 'laparet' and preg_match("#(Смесител)#", $title)),
                ($provider == 'ntceramic' and preg_match("#(Смесител)#", $providerSubcategory) or preg_match("#(Cмесител)#", $title)),
                ($provider == 'domix' and preg_match("#(Смесител|Набор смесител|Гигиенический набор|Гигиеническая лейка|Комплект смесител)#", $title)),
            ],
            5 => [ //'Мебель для ванной',
                ($provider == 'dplintus' and preg_match("#(Полки для ванной и душа)#", $providerCategory)),
                ($provider == 'domix' and preg_match("#(Мебель для ванной)#", $providerCategory)),
                ($provider == 'laparet' and preg_match("#(Пенал|Зеркал|Тумб)#", $title)),
                ($provider == 'ntceramic' and preg_match("#(Мебель для ванной)#", $providerSubcategory)),
                ($provider == 'mosplitka' and (preg_match("#(Зеркал|Панель с полками|Полка|Шкаф|Пенал|Тумба)#", $title))),
            ],
            6 => [ //'Аксессуары для ванной комнаты',
                ($provider == 'mosplitka' and (preg_match("#(Стакан|Держатель|аксессуар|Шторка|Мыльница|Корзина|для писсуаров|Крючок|Ершик)#", $title))),
            ],
            7 => [ //'Комплектующие',
                ($provider == 'mosplitka' and (preg_match("#(Клапан|клапан|Слив|Cифон|Инсталляци|труба|Насадка|Гофра|Гибкое соединение|Муфта|Отвод|инсталляци|Поручень для ванны|Вентиль|вентиль|Термостат|Кнопка смыва|Излив|заглушка|Подголовник для ванны)#", $title))),
                ($provider == 'domix' and preg_match("#(Инсталляц|Каркас|Экран для ванн|Термостат|Базовый модуль|Пьедестал|Крышка-сидушка|Скрытая часть для|Излив|Уголок для|Слив|Крышка-сиденье)#", $title)),
            ],
            8 => [ //'Полотенцесушители',
                ($provider == 'mosplitka' and preg_match("#(Полотенцесушител)#", $providerSubcategory)),
                ($provider == 'domix' and preg_match("#(Полотенцесушител)#", $title)),
            ],
            9 => [ //'Декоративные обои',
                ($provider == 'domix' and preg_match("#(Обои)#", $providerCategory))
            ],
            10 => [ //'Керамогранит',
                ($provider == 'tdgalion' and preg_match("#(Керамогранит)#", $providerCategory)),
                ($provider == 'laparet' and preg_match("#(Керамогранит|Керамопаркет)#", $providerCategory)),
                ($provider == 'ntceramic' and preg_match("#(Керамогранит)#", $providerSubcategory)),
                ($provider == 'artkera' and preg_match("#(Керамогранит)#", $providerSubcategory)),
                ($provider == 'mosplitka' and preg_match("#(Керамогранит)#", $title)),
                ($provider == 'domix' and preg_match("#(Керамогранит)#", $title)),
            ],
            11 => [ //'Керамическая плитка',
                ($provider == 'mosplitka' and preg_match("#(Керамическая плитка)#", $title)),
                ($provider == 'tdgalion' and preg_match("#(Керамическая плитка)#", $providerCategory)),
                ($provider == 'artkera' and preg_match("#(Напольная)#", $providerSubcategory)),
                ($provider == 'domix' and preg_match("#(Плитка)#", $title)),
            ],
            12 => [ //'Натуральный камень',
            ],
            13 => [ //'Мозаика',
                ($provider == 'laparet' and preg_match("#(Мозаика)#", $providerCategory)),
                ($provider == 'artkera' and (preg_match("#(Мозаика)#", $providerCategory) or (preg_match("#(Мозаика)#", $providerSubcategory)))),
                ($provider == 'domix' and preg_match("#(Мозаика)#", $providerSubcategory)),
                ($provider == 'tdgalion' and preg_match("#(Мозаика)#", $providerCategory)),
                ($provider == 'mosplitka' and preg_match("#(Мозаика)#", $title)),
            ],
            14 => [ //'Кухонные мойки',
            ],
            15 => [ //'Ступени и клинкер',
            ],
            16 => [ //'SPC-плитка',
                ($provider == 'mosplitka' and preg_match("#(SPC-плитка)#", $title)),
                $provider == 'alpinefloor' and (preg_match("#(spc-ламинат)#", $providerCategory) or preg_match("#(SPC ламинат)#", $title)),
            ],
            17 => [ //'Фотообои и фрески',
                ($provider == 'domix' and preg_match("#(фотообои)#", $providerCategory)),
            ],
            18 => [ //'Обои под покраску',
            ],
            19 => [ //'Штукатурка',
                ($provider == 'centerkrasok' and preg_match("#(Штукатурки)#", $providerCategory)),
            ],
            20 => [ //'Розетки',
            ],
            21 => [ //'Карнизы',
                ($provider == 'dplintus' and preg_match("#(Карниз)#", $providerCategory)),
                ($provider == 'evroplast' and preg_match("#(карниз)#", $providerCategory)),
                ($provider == 'domix' and preg_match("#(Карниз)#", $providerSubcategory)),
            ],
            22 => [ //'Молдинги',
                ($provider == 'evroplast' and preg_match("#(молдинг)#", $providerCategory)),
                ($provider == 'domix' and preg_match("#(Молдинг)#", $providerSubcategory)),
                ($provider == 'alpinefloor' and preg_match("#(Молдинг)#", $providerSubcategory)),
            ],
            23 => [ //'null',
                
            ],
            24 => [ //'Дверное обрамление',
            ],
            25 => [ //'Потолочный декор',
            ],
            26 => [ //'Другое',
                ($provider == 'dplintus' and preg_match("#(Аксессуары)#", $providerCategory)),
                ($provider == 'evroplast' and preg_match("#(арочн|архитрав|балясин|гибкие аналоги|)#", $providerCategory)),
                ($provider == 'evroplast' and preg_match("#(дополнительные элементы|камни|кессон|крышки столба|монтажный комплект|накладные элементы|наличники)#", $providerCategory)),
                ($provider == 'centerkrasok' and preg_match("#(Монтажные пены|Промышленные покрытия|Растворители|Стеклохолст|Эпоксидные)#", $providerCategory)),
                $provider == 'alpinefloor' and str_contains($product_link, 'https://alpinefloor.su/catalog/quartz-tiles-vinyl-for-walls/'),
                ($provider == 'domix' and preg_match("#(Сопуствующие)#", $providerCategory) and !preg_match("#(Клей|Грутновк)#", $title)),
                ($provider == 'finefloor' and preg_match("#(Сопутствующие)#", $providerCategory) and !preg_match("#(Клеевые|Плинтус|Подложк)#", $providerSubcategory)),
                ($provider == 'mosplitka' and (preg_match("#(Водонагреватель|Финская сауна)#", $title))),

            ],
            27 => [ //'Ламинат',
                ($provider == 'domix' and preg_match("#(Ламинат)#", $providerCategory)),
                $provider == 'alpinefloor' and preg_match("#(Ламинат)#", $providerCategory),
            ],
            28 => [ //'Инженерная доска',
                ($provider == 'domix' and preg_match("#(Инженерная доска)#", $providerCategory)),
                $provider == 'alpinefloor' and preg_match("#(Инженерная доска)#", $providerCategory),
            ],
            29 => [ //'Паркетная доска',
                ($provider == 'domix' and preg_match("#(Паркетная доска)#", $providerCategory))
            ],
            30 => [ //'Штучный паркет',
            ],
            31 => [ //'Виниловые полы',
            ],
            32 => [ //'Подложка под напольные покрытия',
                ($provider == 'domix' and preg_match("#(Подложк)#", $providerCategory)),
                ($provider == 'finefloor' and preg_match("#(Подложк)#", $providerSubcategory)),
                ($provider == 'alpinefloor' and preg_match("#(Подложк)#", $providerSubcategory)),

            ],
            33 => [ //'Плинтус напольный',
                ($provider == 'dplintus' and preg_match("#(Плинтус)#", $providerCategory)),
                ($provider == 'domix' and preg_match("#(Плинтус)#", $providerCategory)),
                ($provider == 'finefloor' and preg_match("#(Плинтус)#", $providerSubcategory)),
                ($provider == 'alpinefloor' and preg_match("#(плинтус)#", $providerSubcategory)),
                ($provider == 'evroplast' and preg_match("#(плинтус)#", $providerCategory)),
            ],
            34 => [ //'Массивная доска',
            ],
            35 => [ //'Пробковое покрытие',
            ],
            36 => [ //'Линолеум',
                ($provider == 'domix' and preg_match("#(Линолеум)#", $providerCategory))
            ],
            37 => [ //'Кварцвиниловые полы',
                ($provider == 'domix' and preg_match("#(Кварц-винил)#", $providerCategory)),
                ($provider == 'alpinefloor' and preg_match("#(Кварцвинил)#", $providerCategory)),
                ($provider == 'finefloor' and !preg_match("#(Плинтус|Уборка|Клеевые|Подложк)#", $providerSubcategory)),
            ],
            38 => [ //'Кварциниловые панели',
                ($provider == 'alpinefloor' and preg_match("#(Кварц-виниловые самоклеящиеся стеновые панели)#", $providerCategory)),
            ],
            39 => [ //'Сопутствующие',
                ($provider == 'domix' and preg_match("#(Сопутствующие)#", $providerCategory)),
                ($provider == 'centerkrasok' and preg_match("#(Инструменты)#", $providerCategory)),
                $provider == 'alpinefloor' and str_contains($product_link, 'https://alpinefloor.su/catalog/related-products/'),
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
            ],
            45 => [ //'Краски, эмали',
                ($provider == 'domix' and preg_match("#(Краски)#", $providerCategory)),
                ($provider == 'centerkrasok' and preg_match("#(Краски)#", $providerCategory)),
            ],
            46 => [ //'Грунтовки',
                ($provider == 'centerkrasok' and preg_match("#(Грунтовки)#", $providerCategory)),
                ($provider == 'domix' and preg_match("#(Грунтовк)#", $providerCategory)),
            ],
            47 => [ //'Лаки и масла', 
                ($provider == 'centerkrasok' and preg_match("#(Лаки и масла)#", $providerCategory)),
                ($provider == 'domix' and preg_match("#(Лак)#", $providerSubcategory)),

            ],
            48 => [ //'Антисептики и пропитки', 
                ($provider == 'centerkrasok' and preg_match("#(Антисептики)#", $providerCategory)),
            ],
            49 => [ //'Затирки и клей',
                ($provider == 'laparet' and preg_match("#(Клеевые|Клей|Затирк)#", $providerCategory)),
                ($provider == 'domix' and ((preg_match("#(Сопуствующие)#", $providerCategory) and preg_match("#(Клей)#", $title)) or preg_match("#Клей#", $providerSubcategory))),
                ($provider == 'evroplast' and preg_match("#клей#", $providerCategory)),
                ($provider == 'centerkrasok' and preg_match("#(Клеи|затирки)#", $providerCategory)),
                ($provider == 'finefloor' and preg_match("#(Клеевые)#", $providerSubcategory)),
                ($provider == 'alpinefloor' and preg_match("#(Клей)#", $providerSubcategory)),
            ],
            50 => [ //'Камины',
                ($provider == 'evroplast' and preg_match("#(камин)#", $providerCategory)),
            ],
            51 => [ //'Декоративные элементы',
                ($provider == 'evroplast' and preg_match("#(декоративные элементы)#", $providerCategory)),
            ],
            52 => [ //'Настенная плитка',
                ($provider == 'artkera' and preg_match("#(Настенная|Декор|Панно|Бордюр)#", $providerSubcategory)),
                ($provider == 'domix' and preg_match("#(Плитка настенная|Декор настенный|Панно)#", $title)),
            ],
            53 => [ //'Кронштейны, ниши',
                ($provider == 'evroplast' and preg_match("#(кронштейн)#", $providerCategory)),
            ],
            54 => [ //'Колонны',
                ($provider == 'evroplast' and preg_match("#(колонн)#", $providerCategory)),
            ],
            55 => [ //'Шпатлевки',
                ($provider == 'centerkrasok' and preg_match("#(Шпатлевки|шпатлевки)#", $providerCategory)),
            ],
            56 => [ //'Декоративное покрытие',
                ($provider == 'centerkrasok' and preg_match("#(Декоративные дизайнерские покрытия)#", $providerCategory)),
                ($provider == 'tdgalion' and preg_match("#(Декоративный камень)#", $providerCategory)),
                ($provider == 'domix' and preg_match("#(Декор|Бордюр)#", $title) and !preg_match("#(Декор настенный)#", $title)),
            ],
            57 => [ //'Люстры, лампы, уличное освещение',
                ($provider == 'mosplitka' and (preg_match("#(подсветк|Люстры|лампы|Светильник|светового|Торшеры|освещение|Спот)#", $providerCategory) or preg_match("#(светильник)#", $title))),
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
    }

    static function finalCategory($provider, $providerCategory = null, $providerSubcategory = null, $title = null, $product_link = null)
    {
        $providerCategory = $providerCategory ?? 'null';
        $providerSubcategory = $providerSubcategory ?? 'null';
        $title = $title ?? 'null';
        $all_categories = Parser::getCategoriesList();

        if (in_array($providerCategory, $all_categories) and !($provider == 'domix' and preg_match("#(Плитка и керамогранит)#", $providerCategory))) return $providerCategory;

        $keys = [
            0 => [ //'Обои и настенные покрытия'
                ($provider == 'domix' and preg_match("#(Обои|фотообои)#", $providerCategory)),
                ($provider == 'domix' and preg_match("#(Плитка настенная|Декор настенный|Панно)#", $title)),
                ($provider == 'artkera' and preg_match("#(Настенная)#", $providerSubcategory)),
                ($provider == 'alpinefloor' and preg_match("#(Кварц-виниловые самоклеящиеся стеновые панели)#", $providerCategory)),

            ],
            1 => [ //'Напольные покрытия',
                ($provider == 'domix' and preg_match("#(Инженерная доска|Плитнус|Кварц-винил|Линолеум|Ламинат|Ковровые|Ковры|Паркет|Подложка)#", $providerCategory)),
                $provider == 'alpinefloor' and (preg_match("#(Инженерная доска|Кварцвинил|spc-ламинат|Ламинат)#", $providerCategory) or preg_match("#(Подложка|плинтус)#", $providerSubcategory) or preg_match("#(ламинат)#", $title)),
                ($provider == 'artkera' and preg_match("#(Декор|Панно|Бордюр)#", $providerSubcategory)),
                ($provider == 'finefloor' and !preg_match("#(Уборка|Клеевые|Подложк)#", $providerSubcategory)),
                ($provider == 'domix' and preg_match("#(Бордюр)#", $title)),
            ],
            2 => [ //'Плитка и керамогранит',
                ($provider == 'dplintus' and preg_match("#(Аксессуары|Плинтус)#", $providerCategory)),
                ($provider == 'domix' and preg_match("#(Плитка напольная|Керамогранит|Мозаика)#", $title)),
                ($provider == 'tdgalion' and preg_match("#(Керамическая плитка|Керамогранит|Мозаика|Декоративный камень)#", $providerCategory)),
                ($provider == 'laparet' and preg_match("#(Керамопаркет|Мозаика)#", $providerCategory)),
                ($provider == 'artkera' and preg_match("#(Керамогранит|Напольная|Мозаика)#", $providerSubcategory)),
                ($provider == 'domix' and preg_match("#(Декор)#", $title) and !preg_match("#(Декор настенный)#", $title)),
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
                ($provider == 'finefloor' and preg_match("#(Уборка|Клеевые|Подложк)#", $providerSubcategory)),
            ],
            5 => [ //'Лепнина',
                ($provider == 'domix' and preg_match("#(Панели и декор|Плинтус)#", $providerCategory)),
                ($provider == 'dplintus' and preg_match("#(Карнизы|профили|Панели для|Профили для|Порожки|Углы и профили)#", $providerCategory)),
                ($provider == 'evroplast' and !preg_match("#клей#", $providerCategory)),
                ($provider == 'alpinefloor' and preg_match("#(Молдинг)#", $providerSubcategory)),
            ],
            6 => [ //'Свет',
                ($provider == 'mosplitka' and (preg_match("#(подсветк|Люстры|лампы|Светильник|светового|Торшеры|освещение|Спот)#", $providerCategory) or preg_match("#(светильник)#", $title))),
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
            if (!str_contains(mb_strtolower($value->text()), 'каталог') && !str_contains(mb_strtolower($value->text()), 'главная')) {
                $path[] = $value;
            }
        }

        return $path;
    }

    static function getCategoryAmpir($url_parser): string|null
    {
        $all_categories = Parser::getCategoriesList();

        if (preg_match("#https://www.ampir.ru/catalog/oboi/.*#", $url_parser)) {
            $category = $all_categories[0];
        } elseif (preg_match("#https://www.ampir.ru/catalog/lepnina/.*#", $url_parser)) {
            $category = $all_categories[5];
        } elseif (preg_match("#https://www.ampir.ru/catalog/kraski/.*#", $url_parser)) {
            $category = $all_categories[4];
        } elseif (preg_match("#https://www.ampir.ru/catalog/shtukaturka/.*#", $url_parser)) {
            $category = $all_categories[4];
        } elseif (preg_match("#https://www.ampir.ru/catalog/rozetki/.*#", $url_parser)) {
            $category = $all_categories[5];
        }

        return $category ?? null;
    }

    static function getSubcategoryAmpir(string $url_parser, string $title = null, string $product_usages = null): string|null
    {
        $all_subcategories = Parser::getSubcategoriesList();

        if (preg_match("#https://www.ampir.ru/catalog/shtukaturka/.*#", $url_parser)) {
            $subcategory = $all_subcategories[19];
        } elseif (preg_match("#https://www.ampir.ru/catalog/rozetki/.*#", $url_parser)) {
            $subcategory = $all_subcategories[20];
        } elseif (preg_match("#https://www.ampir.ru/catalog/kraski/.*#", $url_parser)) {
            $subcategory = $all_subcategories[45];
        } elseif (preg_match("#https://www.ampir.ru/catalog/oboi/.*#", $url_parser) and str_contains(mb_strtolower($title), 'обои под покраску')) {
            $subcategory = $all_subcategories[18];
        } elseif (preg_match("#https://www.ampir.ru/catalog/oboi/.*#", $url_parser) and str_contains(mb_strtolower($title), 'фотообои')) {
            $subcategory = $all_subcategories[17];
        } elseif (preg_match("#https://www.ampir.ru/catalog/oboi/.*#", $url_parser)) {
            $subcategory = $all_subcategories[9];
        } elseif (preg_match("#https://www.ampir.ru/catalog/lepnina/.*#", $url_parser) and str_contains(mb_strtolower($product_usages), 'карниз')) {
            $subcategory = $all_subcategories[21];
        } elseif (preg_match("#https://www.ampir.ru/catalog/lepnina/.*#", $url_parser) and str_contains(mb_strtolower($product_usages), 'дверное обрамление')) {
            $subcategory = $all_subcategories[24];
        } elseif (preg_match("#https://www.ampir.ru/catalog/lepnina/.*#", $url_parser) and str_contains(mb_strtolower($product_usages), 'молдинг')) {
            $subcategory = $all_subcategories[22];
        } elseif (preg_match("#https://www.ampir.ru/catalog/lepnina/.*#", $url_parser) and str_contains(mb_strtolower($product_usages), 'плинтус')) {
            $subcategory = $all_subcategories[23];
        } elseif (preg_match("#https://www.ampir.ru/catalog/lepnina/.*#", $url_parser) and str_contains(mb_strtolower($product_usages), 'потолочный декор')) {
            $subcategory = $all_subcategories[25];
        } elseif (preg_match("#https://www.ampir.ru/catalog/lepnina/.*#", $url_parser)) {
            $subcategory = $all_subcategories[26];
        }

        return $subcategory ?? null;
    }
}
