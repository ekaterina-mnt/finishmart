<?php

namespace functions;

class Logs
{
    public static function writeLog(\Throwable $e): void
    {
        $location = "файл: " . $e->getFile() . ", строка: " . $e->getLine();
        $description = $e->getMessage();
        $date = date('Y-m-d H:i:s', time());
        $query = "INSERT INTO logs (description, location, date) VALUES (?, ?, ?)";
        $types = "sss";
        $values = [$description, $location, $date];
        MySQL::bind_sql($query, $types, $values);
    }

    static function writeCustomLog(string $description): void
    {
        $query = "INSERT INTO logs (description, date) VALUES (?, ?)";
        $date = date('Y-m-d H:i:s', time());
        $types = "ss";
        $values = [$description, $date];
        MySQL::bind_sql($query, $types, $values);
    }

    static function writeLinkLog(string $description, string $articul, string $provider, string $url_parser): void
    {
        $query = "INSERT INTO link_logs (description, articul, provider, parser_link) 
        VALUES ('$description', '$articul', '$provider', '$url_parser')";
        MySQL::sql($query);
    }
}
