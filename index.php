<?php
require __DIR__ . "/vendor/autoload.php";

use functions\Parser;
use functions\GoogleSheets\ParseCharacteristics;


$providers = ['alpinefloor', 'ampir', 'artkera', 'centerkrasok', 'domix', 'dplintus', 'evroplast', 'finefloor', 'laparet', 'masterdom', 'mosplitka', 'ntceramic', 'olimp', 'surgaz', 'tdgalion', 'fargo'];
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
$napolnye_subcategories = ParseCharacteristics::getSubcategoriesNapolnye();

?>

<H2>Вставить данные в Гугл таблицы</H2>
<form action="google_sheets/data/insert.php" , method="POST">
    <input type="hidden" name="category" value="<?= $categories[1] ?>">
    <select name=" <?= $categories[1] ?>">
        <?php
        foreach ($napolnye_subcategories as $subcategory) {
            echo "<option value=$subcategory>$subcategory</option>";
        }
        ?>
    </select></p>
    <p><input type="submit" value="Отправить"></p>
</form>