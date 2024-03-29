<?php
require __DIR__ . "/vendor/autoload.php";

use functions\Parser;
use functions\GoogleSheets\ParseCharacteristics\Categories\Napolnye;
use functions\GoogleSheets\ParseCharacteristics\Categories\Plitka;
use functions\GoogleSheets\ParseCharacteristics\ConnectedSubcategories;

try {

    $providers = [
        'alpinefloor', 'ampir', 'artkera', 'centerkrasok', 'domix', 'dplintus', 'evroplast', 'finefloor',
        'laparet', 'masterdom', 'mosplitka', 'ntceramic', 'olimp', 'surgaz', 'tdgalion', 'fargo'
    ];

    foreach ($providers as $provider) {
        echo '<a href="http://penzevrv.beget.tech/cron_products_scripts/products_' . $provider . '.php">products_' . $provider . '</a>';
        echo "<br>";
        echo '<a href="http://penzevrv.beget.tech/cron_links_scripts/links_' . $provider . '.php">links_' . $provider . '</a>';
        echo "<br><br>";
    }

    echo '<a href="http://penzevrv.beget.tech/cron_dop_scripts/check_invalide_links.php">check_invalide_links</a>';
    echo "<br><br>";
    echo '<a href="http://penzevrv.beget.tech/cron_dop_scripts/masterdomDopData.php">masterdomDopData</a>';
    echo "<br><br>";

    $categories = Parser::getCategoriesList();
    $subcategories = ConnectedSubcategories::getList();

?>

    <?php
    foreach ($subcategories as $category => $subcategoriesList) {
    ?>
        <H2>Вставить данные в Гугл таблицы - <?= $category ?></H2>
        <form action="google_sheets/data/merge_similar_chars.php" , method="POST">
            <input type="hidden" name="category" value="<?= $category ?>">
            <select name="subcategory">
                <?php
                foreach ($subcategoriesList as $subcategory) {
                    echo '<option value="' . $subcategory . '">' . $subcategory . '</option>';
                }
                ?>
            </select></p>
            <p><input type="submit" value="Отправить"></p>
        </form>
    <?php
    }
    ?>



    <br><br>
    <H2>Вставить листы в Гугл таблицы</H2>
    <form action="google_sheets/data/create_subcategory_pages.php" , method="POST">
        <select name="category">
            <?php
            foreach ($categories as $category) {
                echo '<option value="' . $category . '">' . $category . '</option>';
            }
            ?>
        </select>
        <p><input type="submit" value="Отправить"></p>
    </form>
    <br><br>



    <H2>Спарсить характеристики товаров в таблицу characteristics</H2>
    <form action="/alter_mysql_table/parse_characteristics/index.php" , method="POST">
        <select name="category">
            <?php
            foreach ($categories as $category) {
                echo '<option value="' . $category . '">' . $category . '</option>';
            }
            ?>
        </select></p>
        <p><input type="submit" value="Отправить"></p>
    </form>


    <?php
    foreach ($subcategories as $category => $subcategoriesList) {
    ?>
        <H2>Перенести данные в final_products из Гугл Таблиц - <?= $category ?></H2>
        <form action="google_sheets/data/update_final_table.php" , method="POST">
            <input type="hidden" name="category" value="<?= $category ?>">
            <select name="subcategory">
                <?php
                foreach ($subcategoriesList as $subcategory) {
                    echo '<option value="' . $subcategory . '">' . $subcategory . '</option>';
                }
                ?>
            </select></p>
            <p><input type="submit" value="Отправить"></p>
        </form>
    <?php
    }
    ?>


<?php

} catch (Throwable $e) {
    var_dump($e);
}
