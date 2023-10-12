<?php

require __DIR__ . "/vendor/autoload.php";

use functions\MySQL;

// MySQL::sql("UPDATE masterdom_links SET link='none' WHERE id=1");

try {
    echo '1';
    $db = mysqli_connect('localhost', 'root', '', 'parser');
    try {
        echo '2';
        $db = mysqli_connect('localhost', 'root', '23', 'parser');
    } catch (Throwable $e) {
        echo 'error 2';
    }
} catch (Throwable $e) {
    echo 'error 1';
}