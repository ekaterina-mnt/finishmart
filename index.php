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

$napolnye = ParseCharacteristics::getSubcategoriesNapolnye();

?>

<H6>Вставить данные в Гугл таблицы</H6>
<form action="google_sheets/data/insert.php" , method="POST">
    <select name="napolnye">
        <?php
        foreach ($napolnye as $subcategory) {
            echo '<option value="' . $subcategory . '">$subcategory</option>';
        }
        ?>
    </select></p>
    <p><input type="submit" value="Отправить"></p>
</form>