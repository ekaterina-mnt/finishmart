<?php
require __DIR__ . "/vendor/autoload.php";

use functions\Parser;
use functions\GoogleSheets\ParseCharacteristics\Napolnye;


$providers = ['alpinefloor', 'ampir', 'artkera', 'centerkrasok', 'domix', 'dplintus', 'evroplast', 'finefloor', 
            'laparet', 'masterdom', 'mosplitka', 'ntceramic', 'olimp', 'surgaz', 'tdgalion', 'fargo'];
            
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
echo '<a href="http://penzevrv.beget.tech/alter_mysql_table/parse_characteristics/index.php">Спарсить характеристики в mysql</a>';
echo "<br><br>";

$categories = Parser::getCategoriesList();
$napolnye_subcategories = Napolnye::getSubcategoriesNapolnye();

?>

<H2>Вставить данные в Гугл таблицы</H2>
<form action="google_sheets/data/insert.php" , method="POST">
    <input type="hidden" name="category" value="<?= $categories[1] ?>">
    <select name="subcategory">
        <?php
        foreach ($napolnye_subcategories as $subcategory) {
            echo '<option value="' . $subcategory . '">' . $subcategory . '</option>';
        }
        ?>
    </select></p>
    <p><input type="submit" value="Отправить"></p>
</form>

<H2>Вставить данные с объединенными характеристиками в другую таблицу</H2>
<form action="google_sheets/data/merge_similar_chars.php" , method="POST">
    <input type="hidden" name="category" value="<?= $categories[1] ?>">
    <select name="subcategory">
        <?php
        foreach ($napolnye_subcategories as $subcategory) {
            echo '<option value="' . $subcategory . '">' . $subcategory . '</option>';
        }
        ?>
    </select></p>
    <p><input type="submit" value="Отправить"></p>
</form>

<br><br>
<a href="http://penzevrv.beget.tech/google_sheets/data/create_subcategory_pages.php">Вставить в таблицу пустые листы для каждой подкатегории</a>
<br><br>

