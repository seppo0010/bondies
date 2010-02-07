<?php
require_once 'boot.php';
require_once 'utils/html.php';
$streets = mysql_query('SELECT street.id, street.full_name FROM street WHERE street.name LIKE "%' . str_replace(array('%', '?'), array('\\%', '\\?'), mysql_real_escape_string($_REQUEST['street'])) . '%" LIMIT 20');
?>
<ul>
<?php while ($street = mysql_fetch_assoc($streets)) { ?>
<li><?php  echo html_utf8($street['full_name']); ?></li>
<?php } ?>
</ul>
