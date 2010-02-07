<?php
require dirname(__FILE__) . '/config/database.php';
require dirname(__FILE__) . '/utils.php';
mysql_pconnect($db['hostname'], $db['username'], $db['password']) or die(mysql_error());
mysql_select_db($db['database']) or die(mysql_error());
