<?php

try {
    echo '1';
    $db = mysqli_connect('localhost', 'root', '12', 'parser');
    try {
        echo '2';
        $db = mysqli_connect('localhost', 'root', '23', 'parser');
    } catch (Throwable $e) {
        echo 'error 2';
    }
} catch (Throwable $e) {
    echo 'error 1';
}