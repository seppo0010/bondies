<?php
function is_node_near_nodes($node, $nodes) {
	foreach ($nodes as $_node) {
		$distance = calculate_distance($node['lat'], $node['lon'], $_node['lat'], $_node['lon']);
		if ($distance < 0.5) return TRUE;
	}
	return FALSE;
}

function calculate_distance($lat_from, $long_from, $lat_to, $long_to) {
	$unit = 6371;
	$degreeRadius = deg2rad(1);


	$lat_from  *= $degreeRadius;
	$long_from *= $degreeRadius;
	$lat_to    *= $degreeRadius;
	$long_to   *= $degreeRadius;

	$dist = sin($lat_from) * sin($lat_to) + cos($lat_from) * cos($lat_to) * cos($long_from - $long_to);

	return ($unit * acos($dist));
}

function calculate_box($lat, $lon, $wide) {
	$lat_unit_distance = calculate_distance($lat, $lon, $lat + 1, $lon);
	$lat_delta = $wide / (2*$lat_unit_distance);
	$lon_unit_distance = calculate_distance($lat, $lon, $lat, $lon + 1);
	$lon_delta = $wide / (2*$lon_unit_distance);
	return array($lat - $lat_delta, $lon - $lon_delta, $lat + $lat_delta, $lon + $lon_delta);
}
