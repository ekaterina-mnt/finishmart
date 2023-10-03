<?php

function sql(string $sql)
{
    static $db;
    if (!$db) {
        $db = mysqli_connect('localhost', 'root', '', 'parser');
    }
    mysqli_query($db, 'SET character_set_results = "utf8"');
    $query = mysqli_query($db, $sql);
    return $query;
}

function getDB()
{
    static $db;
    if (!$db) {
        $db = mysqli_connect('localhost', 'root', '', 'parser');
    }
    return $db;
}

function add_url()
{
    $data = $_SERVER['REQUEST_URI'];
    $time = date('Y-m-d H:i:s', time());

    try {
        sql("INSERT INTO data (data, time) VALUES ('$data', '$time')");
    } catch (Exception $e) {
    }
}

function writeLog(Throwable $e)
{
    $location = "файл: " . $e->getFile() . ", строка: " . $e->getLine();
    $description = $e->getMessage();
    $date = date('Y-m-d H:i:s', time());
    sql("INSERT INTO logs (description, location, date) VALUES ('$description', '$location', '$date')");
}

function writeCustomLog($description)
{
    $date = date('Y-m-d H:i:s', time());
    sql("INSERT INTO logs(description, date) VALUES ('$description', '$date')");
}
