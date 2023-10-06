<?php

namespace functions;

class Logs
{
    public static function writeLog(\Throwable $e)
    {
        $location = "файл: " . $e->getFile() . ", строка: " . $e->getLine();
        $description = $e->getMessage();
        $date = date('Y-m-d H:i:s', time());
        MySQL::sql("INSERT INTO logs (description, location, date) VALUES ('$description', '$location', '$date')");
    }

    static function writeCustomLog($description)
    {
        $date = date('Y-m-d H:i:s', time());
        MySQL::sql("INSERT INTO logs(description, date) VALUES ('$description', '$date')");
    }
}