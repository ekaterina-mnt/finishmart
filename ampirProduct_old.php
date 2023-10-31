<?php
require __DIR__ . "/vendor/autoload.php";

use functions\MySQL;
use functions\Logs;
use functions\TechInfo;
use functions\Parser;
use functions\ParserMasterdom;
use functions\ParserMosplitka;

TechInfo::start();

try {
    $provider = 'ampir';
    // $provider = Parser::getProvider($url_parser); 

    for ($i = 1; $i < 2; $i++) {

        echo "<br><b>Товар $i</b><br><br>";

        //Получаем ссылку, с которой будем парсить
        $query = MySQL::sql("SELECT link, product_views FROM " . $provider . "_links WHERE type='product' ORDER BY product_views, id LIMIT 1");

        if (!$query->num_rows) {
            Logs::writeCustomLog("не получено ссылки для парсинга", $provider);
            TechInfo::errorExit("не получено ссылки для парсинга");
        }

        $res = mysqli_fetch_assoc($query);

        //Получаем ссылку
        $url_parser = $res['link'];
        $url_parser = "https://www.ampir.ru/catalog/rozetki/356948/";
        TechInfo::whichLinkPass($url_parser);

        //Увеличиваем просмотры ссылки
        $views = $res['product_views'] + 1;
        $date_edit = MySQL::get_mysql_datetime();
        MySQL::sql("UPDATE " . $provider . "_links SET product_views=$views, date_edit='$date_edit' WHERE link='$url_parser'");

        //Получаем html страницы
        try {
            $document = Parser::guzzleConnect($url_parser);
            $all_product_data = [];
            echo "<b>итоговые данные, которые мы спарсили:</b><br><br>";
            $print_result = [];

            $all_product_data['link'] = [$url_parser, 's'];

            $check_if_complect = ParserMosplitka::check_if_complect($document);
            $check_if_archieve = ParserMosplitka::check_if_archive($document);

            if ($check_if_archieve) {
                $all_product_data['status'] = ['archived', 's'];
            }

            if ($check_if_complect) {
                $all_product_data = ParserMosplitka::getComplectData($document, $url_parser);
            } else {

                //название товара
                $title_res = $document->find('.dititle');
                $all_product_data['title'] = ($title_res and !isset($all_product_data['title'])) ? [trim($title_res[0]->text()), 's'] : [null, 's'];

                //цена
                $price_res = $document->find('.newprice');
                if ($price_res) {
                    $all_product_data['price'] = (!isset($all_product_data['price'])) ? [(int) str_replace(' ', '', trim($price_res[0]->text())), 'i'] : [null, 'i'];
                }
                $all_product_data['price'] = isset($all_product_data['price']) ? $all_product_data['price'] : [null, 'i'];

                //категория
                $category = ParserMosplitka::getCategoryAmpir($url_parser);
                $all_product_data['category'] = $category ? [$category, 's'] : [null, 's'];

                if (!isset($all_product_data['category'][0])) {
                    Logs::writeCustomLog("не определена категория товара, не добавлен в БД", $provider, $url_parser);
                    echo "<b>ошибка:</b> не определена категория товара, не добавлен в БД";
                    continue;
                }

                //все характеристики
                $characteristics_res = $document->find('.dfparams tr');

                if ($characteristics_res) {
                    $characteristics = array();

                    foreach ($characteristics_res as $charact) {

                        $name = trim(str_replace(':', '', $charact->find('.dfplabel')[0]->text()));
                        $value = trim($charact->find('.dfpval')[0]->text());

                        $value = implode(', ', explode('/', $value));

                        $characteristics[$name] = $value;

                        //артикул
                        if ($name == 'Артикул' and !isset($all_product_data['articul'])) {
                            $all_product_data['articul'] = [$value, 's'];
                        }

                        //производитель
                        if ($name == 'Производитель' and !isset($all_product_data['producer'])) {
                            $all_product_data['producer'] = [$value, 's'];
                        }

                        //бренд
                        if ($name == 'Бренд' and !isset($all_product_data['brand'])) {
                            $all_product_data['brand'] = [$value, 's'];
                        }

                        //коллекция
                        if ($name == 'Коллекция' and !isset($all_product_data['collection'])) {
                            $all_product_data['collection'] = [$value, 's'];
                        }

                        //длина          
                        if (str_contains($name, 'Длина') and !isset($all_product_data['length'])) {
                            $all_product_data['length'] = [(float) str_replace(",", ".", $value), 'd'];
                        }

                        //ширина          
                        if (str_contains($name, 'Ширина') and !isset($all_product_data['width'])) {
                            $all_product_data['width'] = [(float) str_replace(",", ".", $value), 'd'];
                        }

                        //высота          
                        if (str_contains($name, 'Высота') and !isset($all_product_data['height'])) {
                            $all_product_data['height'] = [(float) str_replace(",", ".", $value), 'd'];
                        }

                        //глубина          
                        if (str_contains($name, 'Глубина') and !isset($all_product_data['depth'])) {
                            $all_product_data['depth'] = [(float) str_replace(",", ".", $value), 'd'];
                        }

                        //толщина          
                        if (str_contains($name, 'Толщина') and !isset($all_product_data['thickness'])) {
                            $all_product_data['thickness'] = [(float) str_replace(",", ".", $value), 'd'];
                        }

                        //формат          
                        if (str_contains($name, 'Формат') and !isset($all_product_data['format'])) {
                            $all_product_data['format'] = [str_replace(["X", "Х", "x", "х"], "x", $value), 's'];
                        }

                        //материал          
                        if (str_contains($name, 'Материал') or str_contains($name, 'Тип материала') and !isset($all_product_data['material'])) {
                            $all_product_data['material'] = [$value, 's'];
                        }

                        //страна          
                        if (str_contains($name, 'Страна') and !isset($all_product_data['country'])) {
                            $all_product_data['country'] = [$value, 's'];
                        }

                        //форма      
                        if (str_contains($name, 'Форма') and !isset($all_product_data['form'])) {
                            $all_product_data['form'] = [$value, 's'];
                        }

                        //цвет    
                        if (str_contains($name, 'Цвет') or str_contains($name, 'цвет') and !isset($all_product_data['color'])) {
                            $all_product_data['color'] = [$value, 's'];
                        }

                        //монтаж
                        if (str_contains($name, 'Тип установки') or str_contains($name, 'Монтаж') and !isset($all_product_data['montage'])) {
                            $all_product_data['montage'] = [$value, 's'];
                        }

                        //дизайн
                        if (str_contains($name, 'Стиль') or str_contains($name, 'Дизайн') and !isset($all_product_data['design'])) {
                            $all_product_data['design'] = [$value, 's'];
                        }

                        //рисунок 
                        if (str_contains($name, 'Рисунок') and !isset($all_product_data['pattern'])) {
                            $all_product_data['pattern'] = [$value, 's'];
                        }

                        //ориентация
                        if (str_contains($name, 'Ориентация') and !isset($all_product_data['orientation'])) {
                            $all_product_data['orientation'] = [$value, 's'];
                        }

                        //поверхность
                        if (str_contains($name, 'Поверхность') and !isset($all_product_data['surface'])) {
                            $all_product_data['surface'] = [$value, 's'];
                        }

                        //назначение
                        if (str_contains($name, 'Назначение') or str_contains($name, 'Применение') and !isset($all_product_data['product_usages'])) {
                            $all_product_data['product_usages'] = [$value, 's'];
                        }

                        //фактура
                        if (str_contains($name, 'Фактура') and !isset($all_product_data['facture'])) {
                            $all_product_data['facture'] = [$value, 's'];
                        }

                        //тип
                        if ($name == 'Тип' or $name == 'Тип краски' and !isset($all_product_data['type'])) {
                            $all_product_data['type'] = [$value, 's'];
                        }

                        //единица измерения
                        if (str_contains($name, 'Ед. изм.') and !isset($all_product_data['edizm'])) {
                            $edizm = Parser::getEdizmByUnit($value);
                            $all_product_data['edizm'] = [$edizm, 's'];
                        }

                        //разбавление
                        if (str_contains($name, 'Разбавление') and !isset($all_product_data['dilution'])) {
                            $all_product_data['dilution'] = [$value, 's'];
                        }

                        //расход
                        if ($name == 'Расход' and !isset($all_product_data['consumption'])) {
                            $all_product_data['consumption'] = [$value, 's'];
                        }

                        //область применения
                        if ($name == 'Область применения' and !isset($all_product_data['area'])) {
                            $all_product_data['usable_area'] = [$value, 's'];
                        }

                        //способ нанесения
                        if ($name == 'Способ нанесения' and !isset($all_product['method'])) {
                            $all_product_data['method'] = [$value, 's'];
                        }

                        //количество слоев 
                        if ($name == 'Количество слоев краски' and !isset($all_product_data['count_layers'])) {
                            $all_product_data['count_layers'] = [$value, 's'];
                        }

                        //колеровка
                        if ($name == 'Колеровка' and !isset($all_product_data['blending'])) {
                            $all_product_data['blending'] = [$value, 's'];
                        }

                        //объем банки
                        if (str_contains($name, 'Объем банки') and !isset($all_product_data['volume'])) {
                            $all_product_data['volume'] = [$value, 's'];
                        }
                    }
                }
            }
            
            $all_product_data['edizm'][0] = $all_product_data['edizm'][0] ? $all_product_data['edizm'][0] : 'шт';


            //НЕОБХОДИМЫЕ ПРОВЕРКИ + ГЕНЕРАЦИЯ ДАННЫХ ЕСЛИ НУЖНО

            // if (!($all_product_data['articul'][0])) {
            //     preg_match("#https://www.ampir.ru/catalog/.+/(\d+)/.*#", $url_parser, $matches);
            //     $all_product_data['articul'][0] = $matches[1];
            // }
            // $check_if_articul_exists = ParserMosplitka::check_if_ampir_articul_exists($all_product_data['articul'][0], $provider, $url_parser);

            // if ($check_if_articul_exists) {
            //     if (isset($all_product_data['volume'][0])) {
            //         preg_match("#https://www.ampir.ru/catalog/.+/(\d+)/.*#", $url_parser, $matches);
            //         $all_product_data['articul'][0] .= ' ' . $matches[1];
            //     } else {
            //         $all_product_data['articul'][0] .= ParserMosplitka::getArticulIfExists($url_parser, $provider);
            //         if (!isset($all_product_data['articul'][0])) {
            //             Logs::writeCustomLog("не определен артикул товара, не добавлен в БД (+ не удалось его сгенерировать)", $provider, $url_parser);
            //             echo "<b>ошибка:</b> не определен артикул товара, не добавлен в БД (+ не удалось его сгенерировать)";
            //             continue;
            //         }
            //     }
            //     echo "арктикул был сгенерирован";
            //     Logs::writeCustomLog("не определено название товара, было сгенерировано название: '" . $all_product_data['title'][0] . "'", $provider, $url_parser);
            // }


            //подкатегория
            $all_product_data['subcategory'] = [ParserMosplitka::getSubcategoryAmpir($url_parser, $all_product_data['title'][0], $all_product_data['product_usages'][0] ?? null), 's'];

            if (!isset($all_product_data['subcategory'][0]) and !preg_match("#https://www.ampir.ru/catalog/kraski/.*#", $url_parser)) { //только у красок нет подкатегории
                Logs::writeCustomLog("не определена единица измерения товара, не добавлен в БД", $provider, $url_parser);
                echo "<b>ошибка:</b> не определен единица измерения товара, не добавлен в БД";
                continue;
            }

            if (str_contains($category, 'Краски')) {
                $all_product_data['edizm'] = [Parser::getEdizmByUnit('краски'), 's'] ?? null;
            }

            if (!isset($all_product_data['edizm'][0])) {
                Logs::writeCustomLog("не определена единица измерения товара, не добавлен в БД", $provider, $url_parser);
                echo "<b>ошибка:</b> не определен единица измерения товара, не добавлен в БД";
                continue;
            }


            if (!($all_product_data['title'][0])) {
                $all_product_data['title'][0] = $all_product_data['subcategory'][0] ? $all_product_data['subcategory'][0] . ' ' . $all_product_data['articul'][0] : $all_product_data['category'][0] . ' ' . $all_product_data['articul'][0];
                if (!$all_product_data['title'][0]) {
                    Logs::writeCustomLog("не определено название товара, не добавлен в БД", $provider, $url_parser);
                    echo "<b>ошибка:</b> не определено название товара, не добавлен в БД";
                    continue;
                }
                echo "название было сгенерировано";
                Logs::writeCustomLog("не определено название товара, было сгенерировано название: '" . $all_product_data['title'][0] . "'", $provider, $url_parser);
            }

            if (!$all_product_data['title'][0]) {
                Logs::writeCustomLog("не определено название товара, не добавлен в БД", $provider, $url_parser);
                echo "<b>ошибка:</b> не определено название товара, не добавлен в БД";
                continue;
            }

            //итоговое сравнение всех аттрибутов
            // $all_product_data = TechInfo::allAtrr($all_product_data);
            //

            $print_result = [];
            foreach ($all_product_data as $key => $val) {
                $print_result[$key] = $val[0];
            }
            TechInfo::preArray($print_result);


            //Для передачи в MySQL

            $types = '';
            $values = array();
            foreach ($all_product_data as $key => $n) {
                $types .= $n[1];
                $values[$key] = $n[0];
            }

            Parser::insertProductData($types, $values, $url_parser, $provider);
        } catch (Throwable $e) {
            MySQL::decreaseProductViews($views, $url_parser, $provider);
            Logs::writeLog($e, $provider, $url_parser);
            echo "<b>ошибка:</b> $e";
            continue;
        }
    }
} catch (\Throwable $e) {
    Logs::writeLog($e, $provider);
    TechInfo::errorExit($e);
    var_dump($e);
}

TechInfo::end();
