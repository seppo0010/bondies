<?php
require_once 'boot.php';
$streets = mysql_query('SELECT name FROM street WHERE name LIKE "%' . str_replace(array('%', '?'), array('\\%', '\\?'), mysql_real_escape_string($_REQUEST['street'])) . '%" LIMIT 20');
?>
<ul>
<?php while ($street = mysql_fetch_assoc($streets)) { ?>
<li><?php echo $street['name']; ?></li>
<?php } ?>
</ul>
