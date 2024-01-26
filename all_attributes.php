<?php

use functions\TechInfo;
use functions\Parser;
use functions\ParserMosplitka;
use functions\Logs;
use functions\Categories;
use functions\MySQL;

try {

    $attributes_classes = [
        "title" => [
            "h1.head", //olimpparket
            "h1.productCard__title", //laparet
            "h1.good__name", //ntceramic
            "h1.product-title", //domix
            ".container.main__container h1", //finefloor
            "h1.sproduct-title", //tdgalion
            ".product-detailed h1", //dplintus
            ".item h1", //centerkrasok
            "h1.item-detail__header", //alpinefloor
            ".product_title", //lkrn
            ".dititle", //ampir
            ".single-product___page-header__h1", //mosplitka
            ".tile__title", //mosplitka
            ".prod__top", //olimp
        ],

        "price" => [
            ".single-price", //domix (нужен ли вообще)
            ".product", //domix
            ".product-page.product_details .price span", //olimpparket
            ".productCard__priceValue", //laparet
            ".good-price__value", //ntceramic
            ".catalog__product__info__price .price", //finefloor
            ".sproduct-price__value", //tdgalion
            ".product-item-detail-price-current", //dplintus
            ".sku-info .price", //centerkrasok
            ".item-detail-price__value", //alpinefloor
            ".price .woocommerce-Price-amount bdi",
            ".newprice", //ampir
            ".single-product___main-info--price span", //mosplitka
            ".tile-shop__price", //mosplitka
            ".total_price", //olimp
        ],

        "stock" => [
            // olimpparket (в скрипте links)
            ".productCard__availability", //laparet
            ".good-available__text", //ntceramic
            "#product-stocks-container tr", //domix
            ".product-item-rest-list .amount", //dplintus
            ".nalichieVNalichii", //tdaglion
            ".single-product___main-info--tag-item.is-green.e__flex.e__aic.e__jcc", //mosplitka
            ".tile-shop-plashki-item.tile-shop-plashki-item__green", //mosplitka
        ],

        "articul" => [
            //larapet (есть только id)
            //olimpparket (в характеристиках)
            ".good-code-and-series .good-block__value", //ntceramic
            ".product-article", //domix
            ".product-detailed .art", //dplintus

        ],

        "char_double_count" => [
            "#chars-table tr", //alpinefloor
        ],

        "char_double" => [ //где нет четкого различия в классах между значением и ключом
            "#chars-table.table td", //alpinefloor
            ".woocommerce-product-details__short-description p", //lkrn
        ],

        "characteristics_count" => [
            ".good-char__title", //ntceramic
            ".properties__itemName", //laparet
            // "th", //olimpparket
            ".sinle-character", //domix
            ".specifications__table__name", //finefloor
            ".sproduct-charact__name", //tdgalion
            ".sproduct-info__name", //tdgalion
            ".product-item-detail-properties dt", //dplintus
            // ".item-detail-classes .item-detail-class", //alpinefloor
            "td.chars__title", //alpinefloor
            ".tile-card__prop-title", //artkera
            ".dfparams tr .dfplabel", //ampir
            "#atts .q_prop__name", //mosplitka
            ".tile-prop-tabs__name", //mosplitka
            "table.table-band tr", //olimp"
        ],

        "char_name" => [
            ".good-char__title", //ntceramic
            ".properties__itemName", //laparet
            // "th", //olimpparket
            ".sinle-character div", //domix
            ".specifications__table__name", //finefloor
            ".sproduct-charact__name", //tdgalion
            ".sproduct-info__name", //tdgalion
            ".product-item-detail-properties dt", //dplintus
            // ".item-detail-class__title", //alpinefloor
            ".tile-card__prop-title", //artkera
            ".dfparams tr .dfplabel", //ampir
            "#atts .q_prop__name", //mosplitka
            ".tile-prop-tabs__name", //mosplitka
            "table.table-band th", //olimp"
        ],

        "char_value" => [
            ".good-char__value", //ntceramic
            ".properties__itemDesc", //laparet
            // ".product-params td", //olimpparket
            ".sinle-character div", //domix
            ".specifications__table__value", //finefloor
            ".sproduct-charact__value", //tdgalion
            ".sproduct-info__value", //tdgalion
            ".product-item-detail-properties dd", //dplintus
            // ".item-detail-class__name", //alpinefloor
            ".tile-card__prop-desc", //artkera
            ".dfparams tr .dfpval", //ampir
            "#atts .q_prop__value", //mosplitka
            ".tile-prop-tabs__value-name", //mosplitka
            "table.table-band td", //olimp"
        ],

        "path" => [
            ".breadcrumbs-list .breadcrumbs-item", //ntceramic
            ".breadcrumbs__list .breadcrumbs__item", //laparet
            ".breadcrumbs-list li", //domix
            ".bx-breadcrumb .bx-breadcrumb-item", //dplintus
            ".breadcrumbs .breadcrumbs__item", //finefloor
            ".breadcrumbs .breadcrumbs__before", //alpinefloor
            ".product-breadcrumb a", //mosplitka
            ".breadcrumb_cont a", //mosplitka
            ".bc__list", //olimp
        ],

        "images" => [ //маленькие 
            ".swiper-wrapper a.good-slide__link", //ntceramic
            ".single-vertical meta", //domix
            ".gallery__previews a.gallery__previewsItem", //laparet + в коде ниже ("a.productCard__thumbnail")
            ".more-photo-container a.fancybox-gallery", //olimpparket
            ".main-img a.fancybox-gallery", //olimpparket главное фото
            "a.catalog__image__big__slide", //finefloor
            "img.product-item-detail-slider-img", //dplintus
            ".imageItemBig .innerImGItem", //tdgalion
            ".item-detail-slide a", //alpinefloor
            ".woocommerce-product-gallery__image a", //lkrn
            ".tile-picture-prev li img", //mosplitka
            ".single-product___main-info--thumbnail img", //mosplitka
            "a.fullimgitem", //ampir
            ".prod__slider .slide a", //olimp
        ],

        "good_id_from_provider" => [
            // ".prod__article", //olimp есть в характеристиках
        ],

        "category" => [
            ".sproduct-info__value",
            ".posted_in a", //lkrn
        ],

        "error" => [
            ".b-cat-description", //tdgalion
            ".not-provides", //centerkrasok (в другом скрипте в итоге)
            ".error404__number", //alpinefloor
        ],

        "in_pack" => [
            ".prod__in-block", //olimp
        ]
    ];

    $check_if_complect = false;

    if ($provider == 'ampir') {
        $check_if_archieve = Parser::check_if_archive($document);

        if ($check_if_archieve) {
            $all_product_data['status'] = ['archived', 's'];
        }
    } elseif ($provider == 'mosplitka') {
        $check_if_complect = Parser::check_if_complect($document);
    }

    // if ($check_if_complect) {
    //     $all_product_data = ParserMosplitka::getComplectData($document, $url_parser);
    // } else {

    //ошибка
    $error_res = $document->find(implode(', ', $attributes_classes['error']));
    if ($error_res) {
        MySQL::sql("UPDATE all_products SET status='invalide', date_edit='$date_edit' WHERE link='$url_parser'");
        TechInfo::errorExit("Страница вернула ошибку");
    }

    //название товара
    $title_res = $document->find(implode(', ', $attributes_classes['title']));
    if ($title_res) {
        $all_product_data['title'] = [$title_res[0]->text(), 's'];
        while (str_contains($all_product_data['title'][0], '  ')) {
            $all_product_data['title'][0] = str_replace(["  ", "\t", "\n"], ' ', $all_product_data['title'][0]);
        }
    }

    //цена
    $price_res = $document->find(implode(', ', $attributes_classes['price']));

    if ($price_res) {
        //форматирование цены
        if ($price_res[0]->attr('data-calc')) {
            $price_arr = json_decode($price_res[0]->attr('data-calc'), 1, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $price = $price_arr['price_m2'] ?? ($price_arr['offers'][0]['price_m2'] ?? ($price_arr['price_pack'] ?? ($price_arr['price_sht'] ?? ($price_arr['price']) ?? null)));
            $all_product_data['price'] = [(int) str_replace(' ', '', $price), 'i'];
        } elseif ($provider == 'lkrn') {
            // var_dump($price_res[0]);
            $all_product_data['price'] = [(int) str_replace([",", "₽"], '', $price_res[0]->text()), 'i'];
            // var_dump($price_res);
            // echo htmlspecialchars($price_res);
        } elseif (is_array($price_res[0])) {
            foreach ($price_res[0] as $price) {
                if (is_numeric($price)) {
                    $all_product_data['price'] = [(int) str_replace(' ', '', $price), 'i'];
                }
            }
        } else {
            $all_product_data['price'] = [(int) str_replace(' ', '', $price_res[0]->text()), 'i'];
        }
    }

    //наличие
    $stock_res = $document->find(implode(', ', $attributes_classes['stock']));
    if ($stock_res) {
        if (isset($stock_res[1])) {
            $all_product_data['stock'] = ['', 's'];
            foreach ($stock_res as $stock_i) {
                $all_product_data['stock'][0] .= ' ' . trim($stock_i->text()) . ';';
            }
            $all_product_data['stock'][0] = substr($all_product_data['stock'][0], 0, -1);
        } else {
            $all_product_data['stock'] = [$stock_res[0]->text(), 's'];
        }
        if ($provider == 'mosplitka') {
            $all_product_data['stock'] = ($stock_res) ? [str_replace('М', 'м', str_replace(" • ", ", ", trim($stock_res[0]->text()))), 's'] : [null, 's'];
        }
        $all_product_data['stock'][0] = (trim($all_product_data['stock'][0]) == "Поставка от 2 дней") ? "В наличии" : $all_product_data['stock'][0];
    }

    //артикул
    $articul_res = $document->find(implode(', ', $attributes_classes['articul']));
    if ($articul_res) {
        $all_product_data['articul'] = [str_replace('Артикул ', '', str_replace('Артикул: ', '', $articul_res[0]->text())), 's'];

        //форматирование артикула
        // while (str_contains($all_product_data['stock'][0], '  ')) {
        //     $all_product_data['stock'][0] = str_replace(["  ", "\t", "\n"], ' ', $all_product_data['stock'][0]);
        // }
        //
    }


    //категории из пути
    $path_res = $document->find(implode(', ', $attributes_classes['path']));
    if ($path_res) {

        $path = Categories::getPath($path_res, $provider);

        $categories = Categories::getCategoriesByPath($path, $provider);
        $all_product_data['category'] = [$categories['category'], 's'];
        if ($categories['subcategory']) {
            $all_product_data['subcategory'] = [$categories['subcategory'], 's'];
        } elseif (isset($categories['category_key']) and !in_array($provider, ['laparet'])) {
            $all_product_data['subcategory'] = [Categories::getSubcategoryByPath($path, $provider, $categories['category_key']), 's'];
        }
    }

    //категории из названия/ссылки товара/провайдера
    // if ($provider == 'olimpparket' and isset($all_product_data['title'][0])) {
    //     $categories = Categories::getCategoriesByTitle($all_product_data['title'][0], $provider);
    //     $all_product_data['category'] = isset($categories['category']) ? [$categories['category'], 's'] : [Parser::getCategoriesList()[1], 's'];
    //     $all_product_data['subcategory'] = isset($categories['subcategory']) ? [$categories['subcategory'], 's'] : [Parser::getSubcategoriesList()[26], 's'];
    // }

    //категория
    if ($provider == 'lkrn') {
        $categories_res = $document->find(implode(', ', $attributes_classes['category']));
        $all_product_data['category'] = !empty($categories_res) ? [$categories_res[0]->text(), 's'] : [null, 's'];
    } elseif ($provider == 'ampir') {
        $category = Categories::getCategoryAmpir($url_parser);
        $all_product_data['category'] = $category ? [$category, 's'] : [null, 's'];
        $all_product_data['subcategory'] = [Categories::getSubcategoryAmpir($url_parser, $all_product_data['title'][0], $all_product_data['product_usages'][0] ?? null), 's'];
    }

    //подкатегория

    //единица измерения
    if ($all_product_data['category'][0] and $provider == 'mosplitka') {
        $edizm = Parser::getEdizm($all_product_data['category'][0]);
        $all_product_data['edizm'] = [$edizm, 's'];
    }

    //все характеристики
    $characteristics_count = count($document->find(implode(', ', $attributes_classes['characteristics_count']))) - 1;
    $char_names = $document->find(implode(', ', $attributes_classes['char_name']));
    $char_values = $document->find(implode(', ', $attributes_classes['char_value']));
    $char_double_count = count($document->find(implode(', ', $attributes_classes['char_double_count']))) - 1;
    $char_double = $document->find(implode(', ', $attributes_classes['char_double']));

    if ($provider == 'dplintus') $characteristics_count++;
    if ($provider == 'lkrn') {
        $char_res = "";
        $html_char_res = "";

        foreach ($char_double as $m) {
            $html_char_res .= htmlspecialchars($m);
            $char_res .= ' ' . $m->text() . ' ';
        }

        while (str_contains($char_res, '  ') or str_contains($char_res, "\t") or str_contains($char_res, "\n") or str_contains($char_res, " \/ ") or str_contains($html_char_res, '  ') or str_contains($html_char_res, "\t") or str_contains($html_char_res, "\n") or str_contains($html_char_res, " \/ ")) {
            $html_char_res = str_replace(["  ", "\t", "\n", " \/ "], ' ', $html_char_res);
            $char_res = str_replace(["  ", "\t", "\n", " \/ "], ' ', $char_res);
        }

        $characteristics = json_encode(['text' => $char_res, 'html' => $html_char_res], JSON_UNESCAPED_UNICODE);
        $all_product_data['characteristics'] = [$characteristics, 's'];
    } elseif ($characteristics_count > 0) {

        if ($char_double_count > 0) {
            foreach (range(0, count($char_double) - 1) as $charact) {

                if ($charact % 2) continue;
                // echo $charact . "\r". $char_double[$charact] . "<br><br>";
                // echo $charact . "\r". $char_double[$charact +1] . "<br><br>";
                $char_names[] = $char_double[$charact];
                $char_values[] = $char_double[$charact + 1];
            }
        }
        // foreach (range(0, count($char_names) - 1) as $charact) {
        //     echo $charact . "\r". $char_names[$charact] . "<br><br>";
        //     echo $charact . "\r". $char_values[$charact] . "<br><br>";
        // }

        $characteristics = array();

        $array_key_flag = 0; //позже когда артикул уже второго товара из комплекта будет, начнутся его характеристики
        foreach (range(0, count($char_names) - 1) as $charact) {
            if ($provider == 'domix') {
                if ($charact % 2) continue;
                $name = $char_names[$charact]->text();
                $value = $char_names[$charact + 1]->text();
            } else {
                $name = $char_names[$charact]->text();
                $value = $char_values[$charact]->text();
            }

            //для значений в массиве (mosplitka)
            $arr_value = $char_values[$charact]->find('.tile-prop-tabs__value-name .tile-prop-tabs__row');
            if (count($arr_value)) {
                $str = '';
                foreach ($arr_value as $val) {
                    $str .= $val . ', ';
                }
                $value = substr($str, 0, -2);
            }
            //

            $name = str_replace(":", '', trim($name));
            $value = trim($value);

            while (str_contains($value, '  ') or str_contains($value, "\t") or str_contains($name, "\n") or str_contains($name, '  ') or str_contains($name, "\t") or str_contains($name, "\n")) {
                $value = str_replace(["  ", "\t", "\n"], ' ', $value);
                $name = str_replace(["  ", "\t", "\n"], ' ', $name);
            }

            // and isset($all_product_data['cha']) and !preg_match("#($value)#", $all_product_data['articul'][0])

            //артикул
            if ((str_contains(mb_strtolower($name), "артикул") and $provider != 'tdgalion') or
                (str_contains(mb_strtolower($name), 'код товара') and $provider == 'alpinefloor')
            ) {

                if ($check_if_complect and isset($all_product_data['articul']) and !preg_match("#($value)#", $all_product_data['articul'][0])) {
                    $all_product_data['articul'][0] .= ' + ' . $value;
                    $array_key_flag = 1;
                    // }
                } else {
                    $all_product_data['articul'] = [$value, 's'];
                }
            }


            if ($check_if_complect) {
                $characteristics[$array_key_flag][$name] = $value;
            } else {
                $characteristics[$name] = $value;
            }


            //код 1с
            if (
                str_contains(mb_strtolower($name), "код 1с") or
                (str_contains(mb_strtolower($name), 'код товара') and $provider == 'olimp')
            ) {
                $all_product_data['good_id_from_provider'] = [$value, 's'];
            }

            //категория
            if ((str_contains(mb_strtolower(html_entity_decode($name)), "категория") and $provider == 'domix') or
                (str_contains(mb_strtolower($name), "категория") and !isset($all_product_data['subcategory'][0])) or
                ((str_contains(mb_strtolower(html_entity_decode($name)), "тип товара") and !isset($all_product_data['subcategory'][0]) and $provider == 'tdgalion'))
            ) {
                if ($provider == 'tdgalion') {
                    $all_product_data['category'] = [$value, 's'];
                } else {
                    $all_product_data['subcategory'] = [$value, 's'];
                }
            }

            //производитель
            if ($name == 'Производитель') {
                $all_product_data['producer'] = [$value, 's'];
            }

            //бренд
            if ($name == 'Бренд') {
                $all_product_data['brand'] = [$value, 's'];
            }

            //коллекция
            if ($name == 'Коллекция') {
                $all_product_data['collection'] = [$value, 's'];
            }

            //длина          
            if (str_contains($name, 'Длина')) {
                $all_product_data['length'] = [(float) str_replace(",", ".", $value), 'd'];
            }

            //ширина          
            if (str_contains($name, 'Ширина')) {
                $all_product_data['width'] = [(float) str_replace(",", ".", $value), 'd'];
            }

            //высота          
            if (str_contains($name, 'Высота')) {
                $all_product_data['height'] = [(float) str_replace(",", ".", $value), 'd'];
            }

            //глубина          
            if (str_contains($name, 'Глубина')) {
                $all_product_data['depth'] = [(float) str_replace(",", ".", $value), 'd'];
            }

            //толщина          
            if (str_contains($name, 'Толщина')) {
                $all_product_data['thickness'] = [(float) str_replace(",", ".", $value), 'd'];
            }

            //формат          
            if (str_contains($name, 'Формат')) {
                $all_product_data['format'] = [str_replace(["X", "Х", "x", "х"], "x", $value), 's'];
            }

            //материал          
            if (str_contains($name, 'Материал') or str_contains($name, 'Тип материала')) {
                $all_product_data['material'] = [$value, 's'];
            }

            //страна          
            if (str_contains($name, 'Страна')) {
                $all_product_data['country'] = [$value, 's'];
            }

            //форма      
            if (str_contains($name, 'Форма')) {
                $all_product_data['form'] = [$value, 's'];
            }

            //цвет    
            if (str_contains($name, 'Цвет') or str_contains($name, 'цвет')) {
                $all_product_data['color'] = [$value, 's'];
            }

            //монтаж
            if (str_contains($name, 'Тип установки') or str_contains($name, 'Монтаж')) {
                $all_product_data['montage'] = [$value, 's'];
            }

            //дизайн
            if (str_contains($name, 'Стиль') or str_contains($name, 'Дизайн')) {
                $all_product_data['design'] = [$value, 's'];
            }

            //рисунок 
            if (str_contains($name, 'Рисунок')) {
                $all_product_data['pattern'] = [$value, 's'];
            }

            //ориентация
            if (str_contains($name, 'Ориентация')) {
                $all_product_data['orientation'] = [$value, 's'];
            }

            //поверхность
            if (str_contains($name, 'Поверхность')) {
                $all_product_data['surface'] = [$value, 's'];
            }

            //назначение
            if (str_contains($name, 'Назначение') or str_contains($name, 'Применение')) {
                $all_product_data['product_usages'] = [$value, 's'];
            }

            //подкатегория(для плитки, mosplitka)
            if (str_contains($name, 'Категория') and $provider == 'mosplitka' and !isset($all_product_data['subcategory'][0])) {
                $all_product_data['subcategory'][0] = $all_product_data['subcategory'][0] ? $all_product_data['subcategory'][0] : $value;
            } elseif (str_contains($name, 'Тип продукции') and $provider == 'olimp' and !isset($all_product_data['subcategory'][0])) {
                $all_product_data['subcategory'][0] = $all_product_data['subcategory'][0] ? $all_product_data['subcategory'][0] : $value;
            }

            //фактура
            if (str_contains($name, 'Фактура')) {
                $all_product_data['facture'] = [$value, 's'];
            }

            //тип
            if (($name == 'Тип' or $name == 'Тип краски') and $provider == 'ampir') {
                $all_product_data['type'] = [$value, 's'];
            } elseif ($name == 'Тип' and $provider == 'mosplitka') {
                $all_product_data['type'] = [$value, 's'];
            } elseif (str_contains($all_product_data['title'][0], "Биде") or str_contains($all_product_data['title'][0], "биде") and !isset($all_product_data['type'])) {
                $all_product_data['type'] = ['Биде', 's'];
            } elseif (str_contains($all_product_data['title'][0], "Кнопка смыва") or str_contains($all_product_data['title'][0], "Клавиша смыва") or str_contains($all_product_data['title'][0], 'Панель смыва') and !isset($all_product_data['type'])) {
                $all_product_data['type'] = ['Кнопка смыва', 's'];
            } elseif (str_contains($all_product_data['title'][0], "Инсталляция") or str_contains($all_product_data['title'][0], "инсталляции") or str_contains($all_product_data['title'][0], "Installiation") and !isset($all_product_data['type'])) {
                $all_product_data['type'] = ['Инсталляция', 's'];
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


        $characteristics = json_encode($characteristics, JSON_UNESCAPED_UNICODE);
        $all_product_data['characteristics'] = [$characteristics, 's'];
    }
    //

    //форматирование подкатегории
    foreach ($all_product_data as $data_key => $data_value) {

        if (isset($data_value[0])) {
            $all_product_data[$data_key][0] = trim($all_product_data[$data_key][0]);
            while (str_contains($all_product_data[$data_key][0], '  ') or str_contains($all_product_data[$data_key][0], "\t") or str_contains($all_product_data[$data_key][0], "\n")) {
                $all_product_data[$data_key][0] = str_replace(["  ", "\t", "\n"], ' ', $all_product_data[$data_key][0]);
            }
        }
    }

    if (isset($all_product_data['title']) and isset($all_product_data['subcategory'])) {
        $all_product_data['subcategory'][0] = (mb_strtolower($all_product_data['title'][0]) == mb_strtolower($all_product_data['subcategory'][0])) ? null : $all_product_data['subcategory'][0];
    }

    //картинки
    $images_res = $document->find(implode(', ', $attributes_classes['images']));
    if (!$images_res and $provider == 'laparet') {
        $images_res = $document->find("a.productCard__thumbnail");
    }
    if ($images_res) {
        $images = Parser::getImages($images_res, $provider) ?? null;
        $all_product_data['images'] = [$images, 's'];
    }
    // }

    //в одной упаковке
    $in_pack_res = $document->find(implode(', ', $attributes_classes['in_pack']));
    if ($in_pack_res) {

        foreach ($in_pack_res as $pack_info) {
            if (preg_match("#В упаковке:(.+)#", $pack_info->text(), $matches)) {
                var_dump($matches);
                echo "<br><br>";
                $in_pack = str_replace(["  ", "\t", "\n"], ' ', $matches[1]);
            }
        }

        $all_product_data['in_pack'] = [$in_pack, 's'];
    }
} catch (Exception $e) { //конец глобального try
    Logs::writeLog1($e,  $provider, $url_parser);
    TechInfo::errorExit($e);
    var_dump($e);
}
