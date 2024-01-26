<?php

namespace functions;

require __DIR__ . "/../vendor/autoload.php";

use DiDom\Document;
use DOMDocument;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;

class Parser
{
    static function check_if_complect(Document $document): bool //для мосплитки
    {
        $goods = $document->find('.single-product___main-info--equip-body.e__flex.e__fdc .single-product___main-info--equip-item.e__flex');
        if (empty($goods)) return false;
        return boolval(count($goods) > 1);
    }

    static function check_if_archive(Document $document): bool //вроде для ампира
    {
        $data = $document->find('.product-arhive-label');
        if (empty($goods)) return false;
        return boolval(count($data));
    }

    static function nextLink(string $link, int $limit): string|null
    {
        preg_match("#(.+offset=)(\d+)(.*)#", $link, $matches);
        if ($matches) {
            $new_offset_value = $matches[2] + $limit;
            $new_link = $matches[1] . $new_offset_value . $matches[3];

            $document = Connect::guzzleConnect($new_link);
            $api_data = self::getApiData($document);

            if (boolval(count($api_data) > 0)) {
                return $new_link;
            }
        }

        return null;
    }

    static function nextLinkSurgaz(string $url_parser)
    {
        preg_match("#(.+PAGEN_1=)(\d+)(.*)#", $url_parser, $matches);

        if ($matches) {
            $new_offset_value = $matches[2] + 1;
            $new_link = $matches[1] . $new_offset_value . $matches[3];

            if ($new_link) {
                $query = "INSERT INTO all_links (link, type, provider) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE type='product'";
                $types = "sss";
                $values = [$new_link, 'product', 'surgaz'];
                MySQL::bind_sql($query, $types, $values);
                echo "<b>Следующая ссылка: </b> $new_link (добавлена в БД)<br><br>";
            }
        }

        return null;
    }

    static function getCategoriesList(): array
    {
        //не менять порядок
        $categories = [
            0 => 'Обои и настенные покрытия',
            1 => 'Напольные покрытия',
            2 => 'Плитка и керамогранит',
            3 => 'Сантехника',
            4 => 'Краски',
            5 => 'Лепнина',
            6 => 'Свет',
        ];

        return $categories;
    }

    static function getSubcategoriesList(): array
    {
        //не менять порядок
        $subcategories = [
            0 => 'Раковины',
            1 => 'Унитазы, писсуары и биде',
            2 => 'Ванны',
            3 => 'Душевые',
            4 => 'Смесители',
            5 => 'Мебель для ванной',
            6 => 'Аксессуары для ванной комнаты',
            7 => 'Комплектующие',
            8 => 'Полотенцесушители',
            9 => 'Декоративные обои',
            10 => 'Керамогранит',
            11 => 'Керамическая плитка',
            12 => 'Натуральный камень',
            13 => 'Мозаика',
            14 => 'Кухонные мойки',
            15 => 'Ступени и клинкер',
            16 => 'SPC-плитка',
            17 => 'Фотообои и фрески',
            18 => 'Обои под покраску',
            19 => 'Штукатурка',
            20 => 'Розетки',
            21 => 'Карнизы',
            22 => 'Молдинги',
            23 => 'Плинтусы',
            24 => 'Дверное обрамление',
            25 => 'Потолочный декор',
            26 => 'Другое',
            27 => 'Ламинат',
            28 => 'Инженерная доска',
            29 => 'Паркетная доска',
            30 => 'Штучный паркет',
            31 => 'Виниловые полы',
            32 => 'Подложка под напольные покрытия',
            33 => 'Плинтус',
            34 => 'Массивная доска',
            35 => 'Пробковое покрытие',
            36 => 'Линолеум',
            37 => 'Кварцвиниловые полы',
            38 => 'Кварциниловые панели',
            39 => 'Сопутствующие',
            40 => 'Порожки',
            41 => 'Профили',
            42 => 'Ковры',
            43 => 'Ковровые покрытия',
            44 => 'Панели',
            45 => 'Краски, эмали',
            46 => 'Грунтовки',
            47 => 'Лаки и масла',
            48 => 'Антисептики и пропитки',
            49 => 'Затирки и клей',
            50 => 'Камины',
            51 => 'Декоративные элементы',
            52 => 'Настенная плитка',
            53 => 'Кронштейны, ниши',
            54 => 'Колонны',
            55 => 'Шпатлевки',
            56 => 'Декоративное покрытие',
            57 => 'Люстры, лампы, уличное освещение',
        ];

        return $subcategories;
    }

    static function getApiData(Document $document): array
    {
        $api_data = json_decode($document->text(), 1);
        $api_data = $api_data[array_keys($api_data)[0]];
        return $api_data;
    }

    static function insertLink(string $link, string $link_type, string $provider = null): string
    {
        if ($provider) {
            try {
                $query = "INSERT INTO " . $provider . "_links (link, type) VALUES (?, ?)";
                $types = "ss";
                $values = [$link, $link_type];
                MySQL::bind_sql($query, $types, $values);
                return "success";
            } catch (\Exception $e) {
                Logs::writeLog($e, $provider, $link);
                var_dump($e);
                return "fail";
            }
        }
    }

    static function insertLink1(string $link, string $link_type, string $provider): string
    {
        try {
            $query = "INSERT INTO all_links (link, type, provider) VALUES (?, ?, ?)";
            $types = "sss";
            $values = [$link, $link_type, $provider];
            MySQL::bind_sql($query, $types, $values);
            return "success";
        } catch (\Exception $e) {
            Logs::writeLog($e, $provider, $link);
            var_dump($e);
            return "fail";
        }
    }

    static function generateLink($href, $provider, $url_parser = null)
    {
        $starts = [
            'laparet' => 'https://laparet.ru',
            'ntceramic' => 'https://ntceramic.ru',
            // 'olimpparket' => 'https://olimpparket.ru',
            'domix' => 'https://moscow.domix-club.ru',
            'finefloor' => "https://finefloor.ru",
            'tdgalion' => "https://www.tdgalion.ru",
            'dplintus' => "https://dplintus.ru",
            'surgaz' => "https://surgaz.ru",
            'centerkrasok' => "https://www.centerkrasok.ru",
            'alpinefloor' => "https://alpinefloor.su",
            'artkera' => "https://artkera.ru",
            'evroplast' => "https://evroplast.ru",
            'mosplitka' => "https://mosplitka.ru",
            'olimp' => "https://olimp-parketa.ru",
        ];

        // if ($url_parser == 'https://olimpparket.ru/catalog/plintusa_i_porogi/' and !str_contains($href, "/catalog")) {
        //     return $url_parser . $href;
        // }

        if (in_array($provider, ['lkrn', 'ampir'])) return $href;

        return $starts[$provider] . $href;
    }

    static function insertProductData(string $types, array $values, string $product_link, string $provider): void
    {
        //Получаем товар
        $product = MySQL::sql("SELECT id FROM " . $provider . "_products WHERE link='$product_link'");

        $quest = '';
        $colms = "";

        foreach ($values as $key => $value) {
            $values[$key] = isset($value) ? html_entity_decode($value) : null;
        }

        // echo count($values) . ' ' . count($columns) . '<br>';

        if ($product->num_rows) {
            $date_edit = MySQL::get_mysql_datetime();
            $types .= 's';
            $values["date_edit"] = $date_edit;
            $id = mysqli_fetch_assoc($product)['id'];

            $query = "UPDATE " . $provider . "_products SET ";
            foreach ($values as $key => $value) {
                $query .= "`" . $key . "`=?, ";
            }
            $query = substr($query, 0, -2);
            $query .= " WHERE id=$id";

            // echo $query . "<br>";
        } else {
            $query = "INSERT INTO " . $provider . "_products (";
            foreach ($values as $key => $value) {
                $colms .= $key . ", ";
                $quest .= "?, ";
            }
            $colms = substr($colms, 0, -2) . ")";
            $quest = substr($quest, 0, -2);
            $query .= $colms . " VALUES (" . $quest . ")";
            // echo $query . "<br>";
        }

        try {
            MySQL::bind_sql($query, $types, array_values($values));
            echo "<b>не возникло ошибок с добавлением продукта в БД</b><br><br>";
        } catch (\Exception $e) {
            Logs::writeLog($e, $provider);
            echo "<b>возникла ошибка с добавлением продукта в БД:</b><br>" . $e->getMessage() . '<br><br>';
        }
    }

    static function insertProductData1(string $types, array $values, string $product_link): void
    {
        //Получаем товар
        $product = MySQL::sql("SELECT id FROM all_products WHERE link='$product_link'");

        $quest = '';
        $colms = "";

        foreach ($values as $key => $value) {
            $values[$key] = isset($value) ? html_entity_decode($value) : null;
        }

        // echo count($values) . ' ' . count($columns) . '<br>';

        if ($product->num_rows) {
            $date_edit = MySQL::get_mysql_datetime();
            $types .= 's';
            $values["date_edit"] = $date_edit;
            $id = mysqli_fetch_assoc($product)['id'];

            $query = "UPDATE all_products SET ";
            foreach ($values as $key => $value) {
                $query .= "`" . $key . "`=?, ";
            }
            $query = substr($query, 0, -2);
            $query .= " WHERE id=$id";
            // echo $query . "<br>";
            echo "<b>товар должен обновиться</b><br><br>";
        } else {
            $query = "INSERT INTO all_products (";
            foreach ($values as $key => $value) {
                $colms .= $key . ", ";
                $quest .= "?, ";
            }
            $colms = substr($colms, 0, -2) . ")";
            $quest = substr($quest, 0, -2);
            $query .= $colms . " VALUES (" . $quest . ")";
            // echo $query . "<br>";
            echo "<b>товар должен добавиться</b><br><br>";
        }
        
        try {
            MySQL::bind_sql($query, $types, array_values($values));
            echo "<b>не возникло ошибок с добавлением продукта в БД</b><br><br>";
        } catch (\Exception $e) {
            echo "<b>возникла ошибка с добавлением продукта в БД:</b><br>" . $e->getMessage() . '<br><br>';
        }
    }

    static function getEdizmList(): array
    {
        $edizm = [
            0 => "рулон",
            1 => "м2",
            2 => "шт",
            3 => "пог.м",
            4 => "л",
        ];
        return $edizm;
    }

    static function getEdizmByUnit(string $edizm): string|null
    {
        $edizm_values = self::getEdizmList();
        switch ($edizm) {
            case "рулон":
            case "рул.":
                return $edizm_values[0];
                break;
            case "м2":
            case "кв. м":
                return $edizm_values[1];
                break;
            case "шт.":
                return $edizm_values[2];
                break;
            case "пог. м":
                return $edizm_values[3];
                break;
            case "краски":
                return $edizm_values[4];
                break;
            default:
                return $edizm_values[2];
                break;
        }
        return null;
    }

    static function getEdizm(string $category): string|null
    {
        $edizm_keys = [
            boolval($category == 'Обои и настенные покрытия'),
            boolval($category == 'Плитка и керамогранит'),
            boolval($category == 'Сантехника'),
        ];

        $edizm_values = self::getEdizmList();

        foreach ($edizm_keys as $i => $edizm_key) {
            if ($edizm_key) {

                $edizm = $edizm_values[$i];
                break;
            }
        }

        return $edizm ?? null;
    }

    static function getLinkType(string $link): string|null
    {
        $keys = [
            'product' => [
                preg_match("#https://mosplitka.ru/product.+#", $link),
                preg_match("#https://ampir.ru/catalog/.+/\d+/#", $link),
                preg_match("#https://laparet.ru/catalog/.+\.html#", $link),
                preg_match("#https://ntceramic.ru/catalog/.+/.*#", $link) and !preg_match("#https://ntceramic.ru/catalog/.+/?PAGEN_.+#", $link),
                preg_match("#https://olimpparket.ru/product/.+/#", $link),
                preg_match("#https://www.olimpparket.ru/catalog/plintusa_i_porogi/.+/.+/.+/#", $link),
                preg_match(("#https://moscow.domix-club.ru/catalog/.+/.+/.*#"), $link),
                preg_match("#https://finefloor.ru/product/.+#", $link),
                preg_match("#https://www.tdgalion.ru/catalog\/[^\/]+\/[^\/]+\/#", $link) and !preg_match("#https://www.tdgalion.ru/catalog.+PAGEN_.+#", $link),
                preg_match("#https://dplintus.ru/catalog\/[^\/]+\/[^\/]+\/#", $link),
                preg_match("#https://surgaz.ru/katalog\/[^\/]+\/#", $link),
                preg_match("#https://www.centerkrasok.ru/product\/[^\/]+\/#", $link),
                preg_match("#https://alpinefloor.su/catalog\/.+#", $link) and !preg_match("#https://alpinefloor.su/catalog\/.+PAGEN.+#", $link),
                preg_match("#https://lkrn.ru/product\/.+#", $link),
                preg_match("#https://artkera.ru/collections/.+#", $link),
                preg_match("#https://evroplast.ru\/[^\/]+\/[^\/]+\/#", $link),
                preg_match('#https://olimp-parketa.ru/catalog/[^/]+/[^/]+/$#', $link), // двойные кавычки экранируют, одинарные - нет, если я хочу, чтобы дальше не было символов - $

            ],
            'catalog' => [
                preg_match("#https://mosplitka.ru/catalog.[^?]+#", $link) and !preg_match("#.php$#", $link),
                preg_match("#https://olimpparket.ru/catalog/.+/#", $link),
                preg_match("#https://ampir.ru/catalog/.+/page\d+.*#", $link),
                preg_match("#https://ntceramic.ru/catalog/.+/?PAGEN_.+#", $link),
                preg_match("#https://laparet.ru/catalog/.+page=\d+#", $link),
                preg_match("#https://finefloor.ru/catalog/.+#", $link),
                preg_match("#https://moscow.domix-club.ru/catalog/.+/?PAGEN_.+#", $link),
                preg_match("#https://www.tdgalion.ru/catalog.+PAGEN_.+#", $link),
                preg_match("#https://dplintus.ru/catalog\/[^\/]+\/#", $link),
                preg_match("#https://alpinefloor.su/catalog\/.+PAGEN.+#", $link),
                preg_match("#https://www.centerkrasok.ru/catalog\/[^\/]+\/#", $link),
                preg_match("#https://lkrn.ru/product-category/.+#", $link),
                preg_match("#https://evroplast.ru/collection/.+#", $link),
                preg_match("#https://evroplast.ru/collection\/[^\/]+\/\#[a-zA-Z]+#", $link),
                preg_match("#https://olimp-parketa.ru/catalog\/[^\/]+\/#", $link),
                preg_match("#https://olimp-parketa.ru/catalog\/[^\/]+\/(PAGEN_).+", $link)
            ],
        ];

        foreach ($keys as $key => $statements) {
            foreach ($statements as $stmnt) {
                if ($stmnt) {
                    return $key;
                }
            }
        }
        return null;
    }

    static function getImages($images_res, string $provider): string
    {
        $keys = [
            'ntceramic' => [
                "attr" => "href",
                "start" => "https://ntceramic.ru",
            ],
            'domix' => [
                "attr" => "content",
                "start" => "https://moscow.domix-club.ru",
            ],
            "laparet" => [
                "attr" => "href",
                "start" => "https://laparet.ru",
            ],
            "olimpparket" => [
                "attr" => "href",
                "start" => "https://www.olimpparket.ru",
            ],
            "finefloor" => [
                "attr" => "href",
                "start" => "https://finefloor.ru",
            ],
            "surgaz" => [
                "attr" => "data-src",
                "start" => "https://surgaz.ru",
            ],
            "dplintus" => [
                "attr" => "src",
                "start" => "https://dplintus.ru",
            ],
            "tdgalion" => [
                "attr" => "data-src",
                "start" => "https://www.tdgalion.ru",
            ],
            "centerkrasok" => [
                "attr" => "data-image",
                "start" => "https://www.centerkrasok.ru",
            ],
            "alpinefloor" => [
                "attr" => "href",
                "start" => "https://alpinefloor.su",
            ],
            "lkrn" => [
                "attr" => "href",
                "start" => "",
            ],
            "artkera" => [
                "attr" => "href",
                "start" => "https://artkera.ru",
            ],
            "mosplitka" => [
                "attr" => "data-big",
                "start" => "https://mosplitka.ru",
            ],
            "ampir" => [
                "attr" => "href",
                "start" => "",
            ],
            "olimp" => [
                "attr" => "data-src",
                "start" => "https://olimp-parketa.ru",
            ],
        ];

        $images = array();

        $n = 1;
        foreach ($images_res as $i => $img) {
            if ($img->attr($keys[$provider]['attr']) or ($provider == 'mosplitka' and !$img->attr($keys[$provider]['attr']))) {
                $src = $keys[$provider]['start'] . $img->attr($keys[$provider]['attr']);
                if ($provider == 'mosplitka' and !$img->attr($keys[$provider]['attr'])) {
                    $src = $keys[$provider]['start'] . $img->attr('data-full');
                }
                if (array_search($src, $images) or str_contains($src, "youtube")) continue;
                $images["img$n"] = $src;
                $n += 1;
            }
        }
        $images = json_encode($images, JSON_UNESCAPED_SLASHES);
        return $images;
    }

    static function getVariants(Document $document): string|null //для мосплитки, сейчас нигде не используется
    {
        $var_res = $document->find('.product-sku__section a, .product-variants-table a');
        if (!$var_res) return null;

        $variants = array();
        $i = 1;
        foreach ($var_res as $var) {
            $src = 'https://mosplitka.ru' . $var->attr('href');
            $variants["var$i"] = $src;
            $i += 1;
        }
        $variants = json_encode($variants, JSON_UNESCAPED_SLASHES);

        return $variants;
    }

    static function getSurgazCatalogLinks(Document $document, $provider, $url_parser)
    {
        $dop_res = $document->find(".left_col nav li a");

        $dop_add = [];
        foreach ($dop_res as $href) {
            $link = Parser::generateLink($href->attr('href'), $provider, $url_parser);

            // избавляемся от дублей
            if (MySQL::sql("SELECT id, link FROM all_links WHERE link='$link'")->num_rows) {
                echo "$link - ссылка уже есть в БД<br>";
                continue;
            }

            //определяем это ссылка на продукт или каталог
            if (preg_match("#https://surgaz.ru/katalog\/[^\/]+\/#", $link)) {
                $link_type = "catalog";
            }

            if (!$link_type) {
                echo "$link - не определился тип ссылки<br>";
                continue;
            }

            echo "$link<br>"; //оставить для вывода

            $res = self::insertLink1($link, $link_type, $provider);
            if ($res == "success") $dop_add[] = ['link' => $link, 'comment' => $link_type];
            if ($res == "fail") $dop_add[] = ['link' => $link . ' - не получилось добавить в БД', 'comment' => $link_type];
        }

        return $dop_add;
    }

    static function getSurgazProductLinks($document)
    {
        $sectionID = $document->find("#wrap-catalogs")[0]->attr('data-section');
        $apiLink = "https://surgaz.ru/ajax.php?ajax=Y&PAGEN_1=1&SECTION_ID=$sectionID&PAGE_ELEMENT_COUNT=1000&LANGUAGE_ID=ru&act=catalogs";
        $apiDocument = Connect::guzzleConnect($apiLink, "windows-1251");
        TechInfo::whichLinkPass($apiLink, 1);
        $all_res = $apiDocument->find('.catalog a[href*=katalog]');

        return $all_res;
    }

    static function checkAlpinefloorValideLink()
    {
    }

    static function updatePrices($document, $provider)
    {
        $search_classes = [
            // ".catSection-basket__price", //mosplitka
            ".catSection__item", //mosplitka
            ".m-product", //mosplitka
        ];

        $products = $document->find(implode(", ", $search_classes));
        if (!$products) return false;


        var_dump(count($products));
        echo "<b>были обновлены цены:</b><br><br>";
        $date_edit = MySQL::get_mysql_datetime();
        foreach ($products as $i => $product) {
            echo $i + 1 . ") ";
            try {

                $link_res = $product->find(".catSection__name, .m-plitka-name--label")[0];
                $link = Parser::generateLink($link_res->attr('href'), $provider);

                if ($no_product = $product->find(".no-product")[0]) {
                    if (preg_match('#(Скоро в продаже|Снят с производства)#', $no_product->text())) {
                        echo "$link (неликвидная ссылка) -";
                        MySQL::sql("UPDATE all_products SET status='invalide', date_edit='$date_edit' WHERE link='$link'");
                        echo "статус успешно обновлен<br>";
                    }

                    continue;
                }

                $price_res = $product->find(".catSection-basket__price, .cost_value");
                $price = (int) str_replace([",", "₽", " "], '', $price_res[0]->text());

                if ($good = MySQL::sql("SELECT * FROM all_products WHERE link like '$link'")) {
                    $query = "UPDATE all_products SET price=?, date_edit=? WHERE link='$link'";
                    $types = "is";
                    $values = [$price, $date_edit];

                    echo "$link (цена: $price) - ";
                    MySQL::bind_sql($query, $types, $values);
                    echo "успешно обновлено<br>";
                }
            } catch (\Throwable $e) {
                var_dump($price_res);
                var_dump($link);
                continue;
            }
        }
        echo "<br>";
    }

    static function discardInvalideAlpinefloorLink($url_parser)
    {
        if (in_array($url_parser, ['https://alpinefloor.su/catalog/related-products/?'])) {
            return true;
        }
    }
}
