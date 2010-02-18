<?php
require_once 'boot.php';
require_once 'utils/html.php';

$subzone = '';
if (!empty($_REQUEST['subzone'])) $subzone = 'suburb.node_id = "' . mysql_real_escape_string($_REQUEST['subzone']) . '" AND ';
$streets = mysql_query('SELECT street.id, street.full_name FROM street JOIN street_suburbs ON street.id = street_suburbs.street_id JOIN suburb ON street_suburbs.suburb_id = suburb.node_id WHERE ' . $subzone . ' suburb.is_in = "' . mysql_real_escape_string($_REQUEST['zone']) . '" AND street.name LIKE "%' . str_replace(array('%', '?'), array('\\%', '\\?'), mysql_real_escape_string($_REQUEST['street'])) . '%" GROUP BY street.id LIMIT 20') or die(mysql_error());
?>
<ul>
<?php $i = 0; while ($street = mysql_fetch_assoc($streets)) { ?>
<li class="<?php echo $i++ & 1 == 1 ? 'odd' : 'even'; ?>" id="street_<?php echo $street['id']; ?>"><?php  echo html_utf8($street['full_name']); ?></li>
<?php } ?>
</ul>
