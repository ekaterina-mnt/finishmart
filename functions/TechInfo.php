<?php

namespace functions;

class TechInfo
{
    static function start()
    {
        echo "<b>скрипт начал работу " . date("d-m-Y H:i:s", time()) . "</b><br><br>";
    }

    static function end()
    {
        echo "<br><br><b>скрипт закончил работу " . date("d-m-Y H:i:s", time()) . "</b><br><br>";
    }

    static function errorExit($e)
    {
        echo "<b>ошибка: </b>";
        var_dump($e);
        self::end();
        exit();
    }

    static function whichLinkPass($link)
    {
        echo '<b>скрипт проходил ссылку <a href="' . $link . '">' . $link . '</a></b><br><br>';
    }
}
