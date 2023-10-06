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
  }
} catch (Throwable $e) {
  Logs::writeLog($e);
}
