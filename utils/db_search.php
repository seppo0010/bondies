<?php
function get_node_from_street_names($street1, $street2) {
	$from_node_query = mysql_query('SELECT node.id, node.lat, node.lon FROM node LEFT JOIN way_nodes ON node.id = way_nodes.node_id LEFT JOIN way ON way_nodes.way_id = way.id LEFT JOIN street ON way.street_id = street.id WHERE street.name = "' . mysql_real_escape_string($street1) . '" AND node.id IN (SELECT node.id FROM node LEFT JOIN way_nodes ON node.id = way_nodes.node_id LEFT JOIN way ON way_nodes.way_id = way.id LEFT JOIN street ON way.street_id = street.id WHERE street.name = "' . mysql_real_escape_string($street2) . '")') or die(mysql_error());
	if (mysql_num_rows($from_node_query) == 0) return null;
	return mysql_fetch_assoc($from_node_query);
}

function get_railways_near_node($lat, $lon, $max_distance) {
	$square = calculate_box($lat, $lon, $max_distance * 2);
	$halts = mysql_query('SELECT node.id, node.lat, node.lon, railway_halts.name FROM railway_halts JOIN node ON railway_halts.node_id = node.id AND node.lat > ' . $square[0] . ' AND node.lat < ' . $square[2] . ' AND node.lon > ' . $square[1] . ' AND node.lon < ' . $square[3] .'');
	$railways = array();
	while ($halt = mysql_fetch_assoc($halts)) {
		$railway_ids_query = mysql_query('SELECT way_id FROM way_nodes WHERE node_id = ' . $halt['id']);
		while ($railway_id = mysql_fetch_assoc($railway_ids_query)) {
			$distance = calculate_distance($lat, $lon, $halt['lat'], $halt['lon']);
			if ($distance > $max_distance) continue;
			if (!isset($railways[$railway_id['way_id']]) || $distance < $railways[$railway_id['way_id']]['distance']) {
				$railways[$railway_id['way_id']] = array('distance' => $distance, 'node' => $halt);
			}
		}
	}
	return $railways;
}

function get_railway_info($railway_id) {
	$info = mysql_query('SELECT way.name, railway.operator FROM railway LEFT JOIN way ON railway.way_id = way.id WHERE railway.way_id = ' . (int)$railway_id) or die(mysql_error());
	if (mysql_num_rows($info) == 0) return array('name' => 'unknown', 'operator' => 'unknown');
	return mysql_fetch_assoc($info);
}

function get_railway_ways($railway_id) {
	return array();
}
