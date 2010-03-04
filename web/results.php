<?php
require_once 'boot.php';
require_once 'utils/db_search.php';
require_once 'utils/grammar.php';

include 'index.php';

/**
 * This function is useful only when there's a railway with the same ordering for two points.
 **/
function is_way_inverted($railway_id, $type, $from_railway, $to_railway) {
    $railway_ways_func = "get_" . $type . "_ways";
    $railway_by_ordering_func = "get_" . $type . "_way_by_ordering";
    
    $actual_way = $railway_by_ordering_func($railway_id, $from_railway['ordering']);
    
    // Looks for the previous way if it exists. Otherwise, finds the next one and change de comparision order.
    if($from_railway['ordering'] > RAILWAY_MIN_ORDERING) {
        $previous_way = $railway_by_ordering_func($railway_id, $from_railway['ordering'] - 1);
        $reference_node = get_node_from_way_ids($actual_way, $previous_way);
        $invert_comparision = false;
    } else {
        $next_way = $railway_by_ordering_func($railway_id, $from_railway['ordering'] + 1);
        $reference_node = get_node_from_way_ids($actual_way, $next_way);
        $invert_comparision = true;
    }
    
    $from2reference_distance = calculate_distance($from_railway["node"]["lat"], $from_railway["node"]["lon"], $reference_node["lat"], $reference_node["lon"]);
    $to2reference_distance = calculate_distance($to_railway["node"]["lat"], $to_railway["node"]["lon"], $reference_node["lat"], $reference_node["lon"]);
    
    if (($from2reference_distance > $to2reference_distance) ^ $invert_comparision)
        return true;
    
    return false;
}

function filter_useful_railways($from_railways, $to_railways, $type) {
    $result = array();
    
	if (count($from_railways) * count($to_railways) > 0) {
    	$from_railways_id = array_keys($from_railways);
    	$to_railways_id   = array_keys($to_railways  );
    	$common_railways_id = array_intersect($from_railways_id, $to_railways_id);
    	foreach($common_railways_id as $railway_id) {
    	    // Validate ordering-dependant railways (like buses)
    	    if(isset($from_railways[$railway_id]['ordering']) && isset($to_railways[$railway_id]['ordering'])) {
    	        if($from_railways[$railway_id]['ordering'] > $to_railways[$railway_id]['ordering'])
    	            continue;
    	        
    	        // When two railways have the same ordering we calculate distances between a reference point and
    	        // the "to" and "from" node, to find out if this railway is useful (refer to bug xxxxx)
    	        if($from_railways[$railway_id]['ordering'] == $to_railways[$railway_id]['ordering'] && 
    	           is_way_inverted($railway_id, $type, $from_railways[$railway_id], $to_railways[$railway_id]))
    	            continue;
    	    }
    	    
    	    $result[] = $railway_id;
    	}
    }
    
    return $result;
}

$from_node = get_node_from_street_ids($_REQUEST['from_id'], $_REQUEST['from_intersection_id']);
if ($from_node == null) die('<span style="color:red">Unable to find origin intersection</span>');
$to_node = get_node_from_street_ids($_REQUEST['to_id'], $_REQUEST['to_intersection_id']);
if ($to_node == null) die('<span style="color:red">Unable to find destination intersection</span>');

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
	
	foreach (filter_useful_railways($from_railways, $to_railways, $type) as $railway_id) {
		$temp = 'get_' . $type . '_info';
		$railway_info = $temp($railway_id);
		$temp = 'get_' . $type . '_ways';
		$results[] = array(
			'name'          => $railway_info['name'],
			'operator'      => $railway_info['operator'],
			'ways'          => $temp($railway_id),
			'from_node'     => $from_railways[$railway_id]['node'],
			'from_ways'     => list_way_names_for_node_id($from_railways[$railway_id]['node']['id']),
			'to_node'       => $to_railways[$railway_id]['node'],
			'to_ways'       => list_way_names_for_node_id($to_railways  [$railway_id]['node']['id']),
			'walk_distance' => $from_railways[$railway_id]['distance'] + $to_railways[$railway_id]['distance'],
			'type'          => $type
		);
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

if (count($results) == 0) die('Unable to find any route matching both directions');
echo '<table border="1" width="100%">';
foreach ($results as $result) {
	echo '<tr><td>' . html_utf8($result['type']) . '</td><td>'. html_utf8($result['name']) .'</td><td>' . round($result['walk_distance'] * 1000) . ' m</td><td>' . html_utf8(implode(', ', $result['from_ways'])) . '</td><td>' . html_utf8(implode(', ', $result['to_ways'])) . '</td></tr>';
}
echo '</table>';
