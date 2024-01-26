<?php
$providers = ['alpinefloor', 'ampir', 'artkera', 'centerkrasok', 'domix', 'dplintus', 'evroplast', 'finefloor', 'laparet', 'masterdom', 'mosplitka', 'ntceramic', 'olimp', 'surgaz', 'tdgalion'];
foreach ($providers as $provider) {
    echo '<a href="http://penzevrv.beget.tech/cron_products_scripts/products_' . $provider . '.php">products_' . $provider . '</a>';
    echo "<br>";
    echo '<a href="http://penzevrv.beget.tech/cron_links_scripts/links_' . $provider . '.php">links_' . $provider . '</a>';
    echo "<br><br>";
}

echo '<a href="http://penzevrv.beget.tech/cron_dop_scripts/check_invalide_links.php">check_invalide_links</a>';
echo "<br><br>";
echo '<a href="http://penzevrv.beget.tech/cron_dop_scripts/masterdomDopData.php">masterdomDopData</a>';