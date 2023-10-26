<?php

namespace functions;

class Logs
{
    public static function writeLog(\Throwable $e, string|null $url_parser = null): void
    {
        $location = "файл: " . $e->getFile() . ", строка: " . $e->getLine();
        $description = $e->getMessage();
        $date = date('Y-m-d H:i:s', time());
        $query = "INSERT INTO logs (description, location, url_parser, date) VALUES (?, ?, ?, ?)";
        $types = "ssss";
        $values = [$description, $location, $url_parser, $date];
        MySQL::bind_sql($query, $types, $values);
    }

    static function writeCustomLog(string $description, string|null $url_parser = null): void
    {
        $query = "INSERT INTO logs (description, url_parser, date) VALUES (?, ?, ?)";
        $date = date('Y-m-d H:i:s', time());
        $types = "sss";
        $values = [$description, $url_parser, $date];
        MySQL::bind_sql($query, $types, $values);
    }
}
