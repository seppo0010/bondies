<?php
require_once dirname(__FILE__) . '/config/database.php';
require_once dirname(__FILE__) . '/utils/geo.php';

mysql_pconnect($db['hostname'], $db['username'], $db['password']) or die(mysql_error());
mysql_select_db($db['database']) or die(mysql_error());

mysql_query("SET NAMES 'utf8' COLLATE 'utf8_general_ci'");
