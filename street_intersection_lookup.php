<?php
require 'boot.php';
$street_nodes = mysql_query('SELECT node.id FROM node LEFT JOIN way_nodes ON node.id = way_nodes.node_id LEFT JOIN way ON way_nodes.way_id = way.id LEFT JOIN street ON way.street_id = street.id WHERE street.name = "' . mysql_real_escape_string($_REQUEST['street']) . '"') or die(mysql_error());
$street_nodes_id = array();
while ($street_node = mysql_fetch_assoc($street_nodes))
	$street_nodes_id[] = $street_node['id'];
$streets = mysql_query('SELECT street.id, street.name FROM node LEFT JOIN way_nodes ON node.id = way_nodes.node_id LEFT JOIN way ON way_nodes.way_id = way.id LEFT JOIN street ON way.street_id = street.id WHERE node.id IN (' . implode(',', $street_nodes_id) . ') AND street.name != "' . mysql_real_escape_string($_REQUEST['street']) . '" AND street.name LIKE "%' . str_replace(array('%', '?'), array('\\%', '\\?'), mysql_real_escape_string($_REQUEST['street_intersection'])) . '%" GROUP BY street.id LIMIT 20');
?>
<ul>
<?php while ($street = mysql_fetch_assoc($streets)) { ?>
<li><?php echo $street['name']; ?></li>
<?php } ?>
</ul>
