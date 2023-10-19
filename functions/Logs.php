<?php

namespace functions;

class Logs
{
    public static function writeLog(\Throwable $e): void
    {
        $location = "файл: " . $e->getFile() . ", строка: " . $e->getLine();
        $description = $e->getMessage();
        $date = date('Y-m-d H:i:s', time());
        MySQL::sql("INSERT INTO logs (description, location, date) VALUES (`$description`, `$location`, `$date`)");
    }

    static function writeCustomLog(string $description): void
    {
        $date = date('Y-m-d H:i:s', time());
        MySQL::sql("INSERT INTO logs(description, date) VALUES (`$description`, `$date`)");
    }

    static function writeLinkLog(string $description, string $articul, string $provider, string $parser_link): void
    {
        MySQL::sql("INSERT INTO link_logs(description, articul, provider, parser_link) 
                    VALUES (`$description`, `$articul`, `$provider`, `$parser_link`)");
    }
}
