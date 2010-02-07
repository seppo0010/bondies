<?php
require 'boot.php';
require 'utils/db_search.php';
$from_node = get_node_from_street_names($_REQUEST['from'], $_REQUEST['from_intersection']);
if ($from_node == null) die('Unable to find origin intersection');
$to_node = get_node_from_street_names($_REQUEST['to'], $_REQUEST['to_intersection']);
if ($to_node == null) die('Unable to find destination intersection');

$results = array(); // each result MUST have: ways, name, from_node, to_node, walk_distance, type (bus,train,railway). MIGHT have: operator
$from_walk_upto = 0.5; // kilometers
$to_walk_upto = 0.5; // kilometers

if (isset($_REQUEST['railway'])) {
	$from_railways = get_railways_near_node($from_node['lat'], $from_node['lon'], $from_walk_upto);
	$to_railways   = get_railways_near_node($to_node['lat']  , $to_node['lon']  , $from_walk_upto);
	if (count($from_railways) * count($to_railways) > 0) {
		$from_railways_id = array_keys($from_railways);
		$to_railways_id   = array_keys($to_railways  );
		$common_railways_id = array_intersect($from_railways_id, $to_railways_id);
		foreach ($common_railways_id as $railway_id) {
			$railway_info = get_railway_info($railway_id);
			$results[] = array(
				'name' => $railway_info['name'],
				'operator' => $railway_info['operator'],
				'ways' => get_railway_ways($railway_id),
				'from_node' => $from_railways[$railway_id]['node'],
				'to_node'   => $to_railways[$railway_id]['node'],
				'walk_distance' => $from_railways[$railway_id]['distance'] + $to_railways[$railway_id]['distance'],
				'type' => 'railway',
			);
		}
	}
}
var_dump($results);
