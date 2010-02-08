<?php
require_once 'boot.php';
require_once 'utils/html.php';
/*$street_nodes = mysql_query('SELECT node.id FROM node LEFT JOIN way_nodes ON node.id = way_nodes.node_id LEFT JOIN way ON way_nodes.way_id = way.id LEFT JOIN street ON way.street_id = street.id WHERE street.id = "' . mysql_real_escape_string($_REQUEST['street_id']) . '"');
$street_nodes_id = array();
while ($street_node = mysql_fetch_assoc($street_nodes))
	$street_nodes_id[] = $street_node['id'];
if (count($street_nodes_id) > 0) {
	$subzone = '';
	if (!empty($_REQUEST['subzone'])) $subzone = 'suburb.node_id = "' . mysql_real_escape_string($_REQUEST['subzone']) . '" AND ';
	$streets = mysql_query('SELECT street.id, street.full_name FROM  street JOIN way ON way.street_id = street.id JOIN street_suburbs ON street.id = street_suburbs.street_id JOIN suburb ON street_suburbs.suburb_id = suburb.node_id JOIN way_nodes ON way_nodes.way_id = way.id JOIN node ON node.id = way_nodes.node_id WHERE ' . $subzone . ' suburb.is_in = "' . mysql_real_escape_string($_REQUEST['zone']) . '" AND node.id IN (' . implode(',', $street_nodes_id) . ') AND street.id != "' . mysql_real_escape_string($_REQUEST['street_id']) . '" AND street.full_name LIKE "%' . str_replace(array('%', '?'), array('\\%', '\\?'), mysql_real_escape_string($_REQUEST['street_intersection'])) . '%" GROUP BY street.id LIMIT 20');
}*/

$subzone = '';
if (!empty($_REQUEST['subzone'])) $subzone = 'suburb.node_id = "' . mysql_real_escape_string($_REQUEST['subzone']) . '" AND ';
$streets = mysql_query('SELECT street.id, street.full_name FROM street JOIN street_suburbs ON street.id = street_suburbs.street_id JOIN suburb ON street_suburbs.suburb_id = suburb.node_id WHERE ' . $subzone . ' suburb.is_in = "' . mysql_real_escape_string($_REQUEST['zone']) . '" AND street.id != "' . mysql_real_escape_string($_REQUEST['street_id']) . '" AND street.name LIKE "%' . str_replace(array('%', '?'), array('\\%', '\\?'), mysql_real_escape_string($_REQUEST['street'])) . '%" LIMIT 20') or die(mysql_error());
if (isset($streets) && mysql_num_rows($streets) > 0) {
?>
<ul>
<?php while ($street = mysql_fetch_assoc($streets)) { ?>
<li id="street_<?php echo $street['id']; ?>"><?php echo html_utf8($street['full_name']); ?></li>
<?php } ?>
</ul>
<?php } else { ?>
<ul>
<li>Unable to find specified street</li>
</ul>
<?php } ?>
