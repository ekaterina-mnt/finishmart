<?php
require "../../vendor/autoload.php";

use functions\MySQL;
use functions\Logs;
use functions\Modes_1c;

try {
    $provider = "1c";
    
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

                } elseif ($pack % 2 == 1) {

            //Товары
            
                $start = ($pack + 1) / 2 * 500 - 500;
                $goods = MySQL::sql("SELECT * FROM mosplitka_products LIMIT $start, 500");

                if (!$goods->num_rows) {
                    echo "finished=yes";
                    exit;
                }

                $next_pack_num = $pack + 1;
                Logs::writeCustomLog("Началась передача товаров начиная с id=$start. Package=$pack", $provider);
                Modes_1c::mode_query_package_goods($goods, $next_pack_num);
                exit;
                
            } elseif ($pack % 2 == 0) {

            //Товарные предложения
            
                $start = $pack / 2 * 500 - 500;
                $goods = MySQL::sql("SELECT * FROM mosplitka_products LIMIT $start, 500");
                
                if (!$goods->num_rows) {
                    echo "finished=yes";
                    Logs::writeCustomLog("Закончилась передача каталога, примерный ориентир - id=$start", $provider);
                    exit;
                }
                
                $next_pack_num = $pack + 1;
                Modes_1c::mode_query_package_offers($goods, $next_pack_num);
                Logs::writeCustomLog("Началась передача товарных предложений начиная с id=$start. Package=$pack", $provider);
                exit;
            }
        }
    }
} catch (Throwable $e) {
  //Logs::writeLog($e, $provider);
}
