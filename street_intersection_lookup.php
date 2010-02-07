<?php
require_once 'boot.php';
require_once 'utils/html.php';
$street_nodes = mysql_query('SELECT node.id FROM node LEFT JOIN way_nodes ON node.id = way_nodes.node_id LEFT JOIN way ON way_nodes.way_id = way.id LEFT JOIN street ON way.street_id = street.id WHERE street.id = "' . mysql_real_escape_string($_REQUEST['street_id']) . '"') or die(mysql_error());
$street_nodes_id = array();
while ($street_node = mysql_fetch_assoc($street_nodes))
	$street_nodes_id[] = $street_node['id'];
$streets = mysql_query('SELECT street.id, street.full_name FROM node LEFT JOIN way_nodes ON node.id = way_nodes.node_id LEFT JOIN way ON way_nodes.way_id = way.id LEFT JOIN street ON way.street_id = street.id WHERE node.id IN (' . implode(',', $street_nodes_id) . ') AND street.id != "' . mysql_real_escape_string($_REQUEST['street_id']) . '" AND street.full_name LIKE "%' . str_replace(array('%', '?'), array('\\%', '\\?'), mysql_real_escape_string($_REQUEST['street_intersection'])) . '%" GROUP BY street.id LIMIT 20');
?>
<ul>
<?php while ($street = mysql_fetch_assoc($streets)) { ?>
<li id="street_<?php echo $street['id']; ?>"><?php echo html_utf8($street['full_name']); ?></li>
<?php } ?>
</ul>
