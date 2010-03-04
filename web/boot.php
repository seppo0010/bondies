<?php
require_once dirname(__FILE__) . '/config/database.php';
require_once dirname(__FILE__) . '/config/constants.php';
require_once dirname(__FILE__) . '/utils/geo.php';

mysql_pconnect($db['hostname'], $db['username'], $db['password']) or die(mysql_error());
mysql_select_db($db['database']) or die(mysql_error());

mysql_query("SET NAMES 'utf8' COLLATE 'utf8_general_ci'");

function i18n($str) {
	static $lang = NULL;
	$current_lang = 'es_AR';
	if ($lang === NULL)
		require dirname(__FILE__) . '/config/i18n.php';
	if (isset($lang[$current_lang]) && isset($lang[$current_lang][$str]))
		return $lang[$current_lang][$str];
	else
		return $str;
}
