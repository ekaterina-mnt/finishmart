<?php
require "../../vendor/autoload.php";

use functions\MySQL;
use functions\Logs;
use functions\Modes_1c;

try {
    MySQL::add_url();
    session_start();

    if (isset($_GET['mode'])) {
        if ($_GET['mode'] == 'checkauth') {
            Modes_1c::mode_checkauth();
        }
        if ($_GET['mode'] == 'init') {
            Modes_1c::mode_init();
        }
        if ($_GET['mode'] == 'query' and isset($_GET['package'])) {

            $pack = $_GET['package'];

            //первый запрос от 1С
            if ($pack == '0') {
                Modes_1c::mode_query_package_start();
                exit;
            }


            //Товары
            if ($pack % 2 == 1) {
                $start = ($pack + 1) / 2 * 500 - 500;
                $goods = MySQL::sql("SELECT * FROM masterdom_products LIMIT $start, 500");

                if (!$goods->num_rows) {
                    echo "finished=yes";
                    exit;
                }

                $next_pack_num = $pack + 1;
                Modes_1c::mode_query_package_goods($goods, $next_pack_num);
                exit;
            }

            //Товарные предложения
            if ($pack % 2 == 0) {
                $start = $pack / 2 * 500 - 500;
                $goods = MySQL::sql("SELECT * FROM masterdom_products LIMIT $start, 500");
                
                if (!$goods->num_rows) {
                    echo "finished=yes";
                    exit;
                }
                
                $next_pack_num = $pack + 1;
                Modes_1c::mode_query_package_offers($goods, $next_pack_num);
                exit;
            }
        }
    }
} catch (Throwable $e) {
    Logs::writeLog($e, $provider);
}
