<?php

function sql(string $sql)
{
    $db = getDB();
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

function writeLog(
    string $logFile,
    array $other = []
): string {
    $logFile = trim($logFile);
    if ($logFile == '')
        return '';

    $t = [
        'HOST' => $_SERVER['HTTP_HOST'] ?? null,
        'REQUEST' => ['GET' => $_GET, 'POST' => $_POST],
        # REFERER
        'REF' => str_replace(__DIR__, '', ($_SERVER['HTTP_REFERER'] ?? '')),
        # Текущий путь
        'URI' => $_SERVER['REQUEST_URI'] ?? null,
        'METHOD' => $_SERVER['REQUEST_METHOD'] ?? null,
        'OTHER' => $other
    ];

    $path = __DIR__ . '/';

    if (strpos(strrev($logFile), strrev('.log')) !== 0)
        $logFile .= '.log';

    // если есть папка для логов
    if (is_dir($path . 'logs'))
        $logFile = $path . 'logs/' . $logFile;
    else
        $logFile = $path . $logFile;

    $hash = sha1(json_encode($t), false);
    // если файл логов не создан, пытАЯаемся создать
    if (!is_file($logFile)) {
        $status = @file_put_contents($logFile, '');
        if ($status === false)
            return '';
    }
    // если лог файл большой, тогда больше записывать не будем
    elseif ((filesize($logFile) / 1048576) >= 1)
        return '';
    // игнорируем повторные логи
    else {
        $tmp = @file_get_contents($logFile);
        if ($tmp === false)
            return '';
        if (strpos($tmp, $hash) !== false)
            return '';
        unset($tmp);
    }

    $t['HASH'] = $hash;
    $t['TIME'] = date('d.m.Y H:i');

    @file_put_contents($logFile, json_encode($t, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
    return $hash;
}
