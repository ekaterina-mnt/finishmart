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
    if ($_GET['mode'] == 'query') {
      Modes_1c::mode_query();
    }
    if (isset($_GET['package'])) {
      if ($_GET['mode'] == 'query' && $_GET['package'] == '0') {
        Modes_1c::mode_query_package0();
      } elseif ($_GET['package'] == '1') {
        Modes_1c::mode_query_package1();
      } elseif ($_GET['package'] == '2') {
        Modes_1c::mode_query_package2();
      } elseif ($_GET['package'] == '3') {
        Modes_1c::mode_query_package3();
      }
    }
  }
} catch (Throwable $e) {
  Logs::writeLog($e);
}
