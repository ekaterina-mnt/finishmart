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
    $provider = 'mosplitka';
    // $provider = Parser::getProvider($url_parser); 

    for ($i = 1; $i < 21; $i++) {

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
        TechInfo::whichLinkPass($url_parser);

        //Увеличиваем просмотры ссылки
        $views = $res['product_views'] + 1;
        $date_edit = MySQL::get_mysql_datetime();
        MySQL::sql("UPDATE " . $provider . "_links SET product_views=$views, date_edit='$date_edit' WHERE link='$url_parser'"); 

        //Получаем html страницы
        try {
            $document = Parser::guzzleConnect($url_parser);
            $all_product_data = [];
            
            $all_product_data['link'] = [$url_parser, 's'];

            $check_if_complect = ParserMosplitka::check_if_complect($document);

            if ($check_if_complect) {
                $all_product_data = ParserMosplitka::getComplectData($document, $url_parser);
            } else {

                //название товара
                $title_res = $document->find('.single-product___page-header__h1, .tile__title');
                $all_product_data['title'] = ($title_res and !isset($all_product_data['title'])) ? [trim($title_res[0]->text()), 's'] : [null, 's'];
                
                if (!$all_product_data['title'][0]) {
                    MySQL::decreaseViews($views, $url_parser, $provider);
                    Logs::writeCustomLog("не определено название товара, не добавлен в БД", $provider, $url_parser);
                    echo "<b>ошибка:</b> не определено название товара, не добавлен в БД";
                    continue;
                }

                //цена
                $price_res = $document->find('.single-product___main-info--price span, .tile-shop__price');
                preg_match("#([0-9 ]+)([^0-9]+)#", $price_res[0]->text(), $carm);
                $all_product_data['price'] = ($carm and !isset($all_product_data['price'])) ? [(int) str_replace(' ', '', trim($carm[1])), 'i'] : [null, 'i'];

                //категория
                $categories = ParserMosplitka::getCategorySubcategory($document, $all_product_data['title'][0]);
                $all_product_data['category'] = $categories['category'] ? [$categories['category'], 's'] :[null, 's'];

                if (!isset($all_product_data['category'][0])) {
                    MySQL::decreaseViews($views, $url_parser, $provider);
                    Logs::writeCustomLog("не определена категория товара, не добавлен в БД", $provider, $url_parser);
                    echo "<b>ошибка:</b> не определена категория товара, не добавлен в БД";
                    continue;
                }

                //подкатегория
                $all_product_data['subcategory'] = isset($categories['subcategory']) ? [$categories['subcategory'], 's'] : [null, 's'];

                //единица измерения
                $edizm = Parser::getEdizm($all_product_data['category'][0]);
                $all_product_data['edizm'] = [$edizm, 's'];

                //остатки товара
                $stock_res = $document->find('.single-product___main-info--tag-item.is-green.e__flex.e__aic.e__jcc, .tile-shop-plashki-item.tile-shop-plashki-item__green'); //остатки на складе
                $all_product_data['stock'] = ($stock_res) ? [str_replace('М', 'м', str_replace(" • ", ", ", trim($stock_res[0]->text()))), 's'] : [null, 's'];

                //все характеристики
                $characteristics_res = $document->find('.single-product___atts-att.e__flex.e__aic, .tile-prop-tabs__item');
                if ($characteristics_res) {
                    $characteristics = array();

                    foreach ($characteristics_res as $charact) {

                        $name = trim($charact->find('.q_prop__name, .tile-prop-tabs__name')[0]->text());
                        $value = trim($charact->find('.q_prop__value, .tile-prop-tabs__value-name')[0]->text());

                        $characteristics[$name] = $value;

                        //для значений в массиве
                        $arr_value = $charact->find('.tile-prop-tabs__value-name .tile-prop-tabs__row');
                        if (count($arr_value)) {
                            $str = '';
                            foreach ($arr_value as $val) {
                                $str .= $val . ', ';
                            }
                            $value = substr($str, 0, -2);
                        }
                        //

                        //артикул
                        if ($name == 'Артикул' and !isset($all_product_data['articul'])) {
                            $all_product_data['articul'] = [$value, 's'];
                        }

                        //производитель
                        if ($name == 'Производитель' and !isset($all_product_data['producer'])) {
                            $all_product_data['producer'] = [$value, 's'];
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
                        if (str_contains($name, 'Стиль') and !isset($all_product_data['design'])) {
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

                        //подкатегория(для плитки)
                        if (str_contains($name, 'Категория') and !isset($all_product_data['subcategory'])) {
                            $subcategory = ParserMosplitka::validateSubcategory($category, $value);
                            $all_product_data['subcategory'] = $subcategory ? [$subcategory, 's'] : null;
                        }

                        //тип
                        if ($name == 'Тип' and !isset($all_product_data['type'])) {
                            $all_product_data['type'] = [$value, 's'];
                        } elseif (str_contains($all_product_data['title'][0], "Биде") or str_contains($all_product_data['title'][0], "биде") and !isset($all_product_data['type'])) {
                            $all_product_data['type'] = ['Биде', 's'];
                        } elseif (str_contains($all_product_data['title'][0], "Кнопка смыва") or str_contains($all_product_data['title'][0], "Клавиша смыва") or str_contains($all_product_data['title'][0], 'Панель смыва') and !isset($all_product_data['type'])) {
                            $all_product_data['type'] = ['Кнопка смыва', 's'];
                        } elseif (str_contains($all_product_data['title'][0], "Инсталляция") or str_contains($all_product_data['title'][0], "инсталляции") or str_contains($all_product_data['title'][0], "Installiation") and !isset($all_product_data['type'])) {
                            $all_product_data['type'] = ['Инсталляция', 's'];
                        }
                    }

                    if (!$all_product_data['subcategory']){
                        MySQL::decreaseViews($views, $url_parser, $provider);
                        Logs::writeCustomLog("не определена подкатегория товара, не добавлен в БД", $provider, $url_parser);
                        echo "<b>ошибка:</b> не определена подкатегория товара, не добавлен в БД";
                        continue;
                    }

                    if (!$all_product_data['articul']) {
                        MySQL::decreaseViews($views, $url_parser, $provider);
                        Logs::writeCustomLog("не определен артикул товара, не добавлен в БД", $provider, $url_parser);
                        echo "<b>ошибка:</b> не определен артикул товара, не добавлен в БД";
                        continue;
                    }

                    $characteristics = json_encode($characteristics, JSON_UNESCAPED_UNICODE);
                }
                $characteristics = $characteristics ?? null;
                $all_product_data['characteristics'] = [$characteristics, 's'];

                //картинки
                $images = ParserMosplitka::getImages($document, $all_product_data['type'][0]) ?? null;
                $all_product_data['images'] = [$images, 's'];

                //варианты исполнения
                // $variants = ParserMosplitka::getVariants($document);
                // $variants = $variants ?? null;
                // $all_product_data['variants'] = [$variants, 's'];

            }
            echo "<b>итоговые данные, которые мы спарсили:</b><br><br>";
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
            MySQL::decreaseViews($views, $url_parser, $provider);
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
