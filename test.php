<?php
require __DIR__ . "/vendor/autoload.php";

use functions\MySQL;
use functions\Logs;
use functions\TechInfo;
use functions\Categories;
use functions\Parser;
use functions\Connect;

            $document = Connect::guzzleConnect("https://moscow.petrovich.ru/product/1025831/");
            echo $document;