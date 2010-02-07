<?php
$_REQUEST = array ( 'from' => 'Av. 9 de Julio', 'from_intersection' => 'Av. San Juan', 'to' => 'Av. 9 de Julio', 'to_intersection' => 'Avenida Corrientes', 'bus' => 'on', 'railway' => 'on', );
require_once 'boot.php';
require_once 'utils/db_search.php';
require_once 'utils/grammar.php';
$from_node = get_node_from_street_names($_REQUEST['from'], $_REQUEST['from_intersection']);
if ($from_node == null) die('Unable to find origin intersection');
$to_node = get_node_from_street_names($_REQUEST['to'], $_REQUEST['to_intersection']);
if ($to_node == null) die('Unable to find destination intersection');

$results = array(); // each result MUST have: ways, name, from_node, to_node, walk_distance, type (bus,train,railway). MIGHT have: operator
$from_walk_upto = 0.5; // kilometers
$to_walk_upto = 0.5; // kilometers

$types = array();
if (isset($_REQUEST['railway'])) $types[] = 'railway';
if (isset($_REQUEST['bus'    ])) $types[] = 'bus';
if (isset($_REQUEST['train'  ])) $types[] = 'train';
foreach ($types as $type) {
	$temp = 'get_' . pluralize($type) . '_near_node';
	$from_railways = $temp($from_node['lat'], $from_node['lon'], $from_walk_upto);
	$to_railways   = $temp($to_node['lat']  , $to_node['lon']  , $from_walk_upto);
	if (count($from_railways) * count($to_railways) > 0) {
		$from_railways_id = array_keys($from_railways);
		$to_railways_id   = array_keys($to_railways  );
		$common_railways_id = array_intersect($from_railways_id, $to_railways_id);
		foreach ($common_railways_id as $railway_id) {
			$temp = 'get_' . $type . '_info';
			$railway_info = $temp($railway_id);
			$temp = 'get_' . $type . '_ways';
			$results[] = array(
				'name' => $railway_info['name'],
				'operator' => $railway_info['operator'],
				'ways' => $temp($railway_id),
				'from_node' => $from_railways[$railway_id]['node'],
				'to_node'   => $to_railways[$railway_id]['node'],
				'walk_distance' => $from_railways[$railway_id]['distance'] + $to_railways[$railway_id]['distance'],
				'type' => $type
			);
		}
	}
}

function walk_distance_sort($a, $b) {
    if ($a['walk_distance'] == $b['walk_distance']) {
        return 0;
    }
    return ($a['walk_distance'] < $b['walk_distance']) ? -1 : 1;
}
usort($results, 'walk_distance_sort');
//$results = array_reverse($results);

var_dump($results);
