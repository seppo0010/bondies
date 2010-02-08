<?php
function list_zones() {
	$zones_query = mysql_query('SELECT node_id, name, is_in FROM suburb WHERE is_in IS NOT NULL AND is_in != ""');
	$zones = array();
	while ($zone = mysql_fetch_assoc($zones_query)) {
		if (empty($zone['is_in'])) $zone['is_in'] = 'Other';
		if (!isset($zones[$zone['is_in']])) $zones[$zone['is_in']] = array();
		$zones[$zone['is_in']][] = $zone;
	}
	return $zones;
}
