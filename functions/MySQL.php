<?php

namespace functions;

class MySQL
{
    private static $db;

    static function sql(string $sql)
    {
        $db = self::getDB();
        mysqli_query($db, 'SET character_set_results = "utf8"');
        $query = mysqli_query($db, $sql);
        return $query;
    }

    static function getDB()
    {
        if (!self::$db) {
            self::$db = mysqli_connect('localhost', 'root', '', 'parser');
        }
        return self::$db;
    }

    static function add_url()
    {
        $data = $_SERVER['REQUEST_URI'];
        $time = date('Y-m-d H:i:s', time());

        self::sql("INSERT INTO data_1c_exchange (data, time) VALUES ('$data', '$time')");
    }
}