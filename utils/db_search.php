<?php
function get_node_from_street_names($street1, $street2) {
	$from_node_query = mysql_query('SELECT node.id, node.lat, node.lon FROM node LEFT JOIN way_nodes ON node.id = way_nodes.node_id LEFT JOIN way ON way_nodes.way_id = way.id LEFT JOIN street ON way.street_id = street.id WHERE street.full_name = "' . mysql_real_escape_string($street1) . '" AND node.id IN (SELECT node.id FROM node LEFT JOIN way_nodes ON node.id = way_nodes.node_id LEFT JOIN way ON way_nodes.way_id = way.id LEFT JOIN street ON way.street_id = street.id WHERE street.full_name = "' . mysql_real_escape_string($street2) . '")');
	if (mysql_num_rows($from_node_query) == 0) return null;
	return mysql_fetch_assoc($from_node_query);
}

function get_node_from_street_ids($street1, $street2) {
	$from_node_query = mysql_query('SELECT node.id, node.lat, node.lon FROM node LEFT JOIN way_nodes ON node.id = way_nodes.node_id LEFT JOIN way ON way_nodes.way_id = way.id LEFT JOIN street ON way.street_id = street.id WHERE street.id = "' . mysql_real_escape_string($street1) . '" AND node.id IN (SELECT node.id FROM node LEFT JOIN way_nodes ON node.id = way_nodes.node_id LEFT JOIN way ON way_nodes.way_id = way.id LEFT JOIN street ON way.street_id = street.id WHERE street.id = "' . mysql_real_escape_string($street2) . '")');
	if (mysql_num_rows($from_node_query) == 0) return null;
	return mysql_fetch_assoc($from_node_query);
}

function list_way_names_for_node_id($node_id) {
	$ways_query = mysql_query('SELECT DISTINCT way.name FROM node JOIN way_nodes ON node.id = way_nodes.node_id JOIN way ON way_nodes.way_id = way.id WHERE node.id = ' . $node_id);
	$ways = array();
	while (list($way) = mysql_fetch_row($ways_query)) {
		$ways[] = $way;
	}
	return $ways;
}

function get_thing_near_node($lat, $lon, $max_distance, $q1, $q2) {
	$square = calculate_box($lat, $lon, $max_distance * 2);
	$halts = mysql_query(sprintf($q1, $square[0], $square[2], $square[1], $square[3]));
	$railways = array();
	while ($halt = mysql_fetch_assoc($halts)) {
		$distance = calculate_distance($lat, $lon, $halt['lat'], $halt['lon']);
		if ($distance > $max_distance) continue;
		$railway_ids_query = mysql_query(sprintf($q2, $halt['id']));
		while (list($railway_id) = mysql_fetch_row($railway_ids_query)) {
			if (!isset($railways[$railway_id]) || $distance < $railways[$railway_id]['distance']) {
				$railways[$railway_id] = array('distance' => $distance, 'node' => $halt);
			}
		}
	}
	return $railways;
}


function get_railways_near_node($lat, $lon, $max_distance) {
	return get_thing_near_node($lat, $lon, $max_distance, 'SELECT node.id, node.lat, node.lon, railway_halts.name FROM railway_halts JOIN node ON railway_halts.node_id = node.id AND node.lat > %f AND node.lat < %f AND node.lon > %f AND node.lon < %f', 'SELECT way_id FROM way_nodes WHERE node_id = %d');
}

function get_railway_info($railway_id) {
	$info = mysql_query('SELECT way.name, railway.operator FROM railway LEFT JOIN way ON railway.way_id = way.id WHERE railway.way_id = ' . (int)$railway_id);
	if (mysql_num_rows($info) == 0) return array('name' => 'unknown', 'operator' => 'unknown');
	return mysql_fetch_assoc($info);
}

function get_railway_ways($railway_id) {
	// TODO: implement this, if necesary...
	return array();
}

function get_trains_near_node($lat, $lon, $max_distance) {
	return get_thing_near_node($lat, $lon, $max_distance, 'SELECT node.id, node.lat, node.lon, train_stations.name FROM train_stations JOIN node ON train_stations.node_id = node.id AND node.lat > %f AND node.lat < %f AND node.lon > %f AND node.lon < %f', 'SELECT way_id FROM way_nodes WHERE node_id = %d');
}

function get_train_info($railway_id) {
	$info = mysql_query('SELECT way.name, train.operator FROM train LEFT JOIN way ON train.way_id = way.id WHERE train.way_id = ' . (int)$railway_id);
	if (mysql_num_rows($info) == 0) return array('name' => 'unknown', 'operator' => 'unknown');
	return mysql_fetch_assoc($info);
}

function get_train_ways($railway_id) {
	// TODO: implement this, if necesary...
	return array();
}

function get_buses_near_node($lat, $lon, $max_distance) {
	return get_thing_near_node($lat, $lon, $max_distance, 'SELECT node.id, node.lat, node.lon, relation_ways.ordering FROM relation_ways JOIN way_nodes ON way_nodes.way_id = relation_ways.way_id JOIN node ON node.id = way_nodes.node_id  WHERE node.lat > %f AND node.lat < %f AND node.lon > %f AND node.lon < %f', 'SELECT relation_ways.relation_id FROM relation_ways JOIN way ON relation_ways.way_id = way.id JOIN way_nodes ON way.id = way_nodes.way_id WHERE way_nodes.node_id = %d');
}

function get_bus_info($bus_id) {
        $info = mysql_query('SELECT bus.name, bus.operator FROM bus WHERE bus.relation_id = ' . (int)$bus_id) or die(mysql_error());
        if (mysql_num_rows($info) == 0) return array('name' => 'unknown', 'operator' => 'unknown');
        return mysql_fetch_assoc($info);
}

function get_bus_ways($railway_id) {
        // TODO: implement this, if necesary...
        return array();
}
