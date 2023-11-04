<?php

namespace functions;

class Logs
{
    public static function writeLog(\Throwable $e, string $provider, string|null $url_parser = null): void
    {
        $location = "файл: " . $e->getFile() . ", строка: " . $e->getLine();
        $description = $e->getMessage();
        $date = date('Y-m-d H:i:s', time());
        $query = "INSERT INTO " . $provider . "_logs (description, location, url_parser, date) VALUES (?, ?, ?, ?)";
        $types = "ssss";
        $values = [$description, $location, $url_parser, $date];
        MySQL::bind_sql($query, $types, $values);
    }

    public static function writeLog1(\Throwable $e, string|null $provider = null, string|null $url_parser = null): void
    {
        $location = "файл: " . $e->getFile() . ", строка: " . $e->getLine();
        $description = $e->getMessage();
        $query = "INSERT INTO all_logs (description, location, url_parser, provider) VALUES (?, ?, ?, ?)";
        $types = "ssss";
        $values = [$description, $location, $url_parser, $provider];
        MySQL::bind_sql($query, $types, $values);
    }

    static function writeCustomLog(string $description, string $provider, string|null $url_parser = null): void
    {
        $query = "INSERT INTO " . $provider . "_logs (description, url_parser, date) VALUES (?, ?, ?)";
        $date = date('Y-m-d H:i:s', time());
        $types = "sss";
        $values = [$description, $url_parser, $date];
        MySQL::bind_sql($query, $types, $values);
    }

    static function writeCustomLog1(string $description, string $provider, string|null $url_parser = null): void
    {
        $query = "INSERT INTO all_logs (description, url_parser, provider) VALUES (?, ?, ?)";
        $types = "sss";
        $values = [$description, $url_parser, $provider];
        MySQL::bind_sql($query, $types, $values);
    }
}
