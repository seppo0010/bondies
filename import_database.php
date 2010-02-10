<?php
require_once 'boot.php';

$keep_data = FALSE;
$suburbs = array();
if ($keep_data === FALSE) {
	mysql_query('DROP TABLE node');
	mysql_query('DROP TABLE suburb');
	//mysql_query('DROP TABLE node_tags');
	mysql_query('CREATE TABLE node ( id BIGINT(20) unsigned not null PRIMARY KEY, lat FLOAT(9,7), lon FLOAT(9,7) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci' ) or die(mysql_error());
	//mysql_query('CREATE TABLE node_tags ( node_id BIGINT(20) not null, field VARCHAR(255), value VARCHAR(255), UNIQUE KEY node_key (node_id, field), INDEX field (field), INDEX node_id (node_id) )');
	mysql_query('CREATE TABLE suburb ( node_id BIGINT(20) PRIMARY KEY, name VARCHAR(255), is_in VARCHAR(255), INDEX name (name), INDEX is_in (is_in) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci') or die(mysql_error());

	mysql_query('DROP TABLE way');
	mysql_query('DROP TABLE way_nodes');
	mysql_query('DROP TABLE railway');
	mysql_query('DROP TABLE railway_halts');
	//mysql_query('DROP TABLE way_tags');
	mysql_query('CREATE TABLE way ( id BIGINT(20) unsigned not null PRIMARY KEY, name VARCHAR(255), street_id BIGINT(20), INDEX name (name), INDEX street_id (street_id) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci') or die(mysql_error());
	mysql_query('CREATE TABLE way_nodes ( way_id BIGINT(20) UNSIGNED NOT NULL, node_id BIGINT(20) UNSIGNED NOT NULL, INDEX way_id (way_id), INDEX node_id (node_id) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci') or die(mysql_error());
	mysql_query('CREATE TABLE railway ( id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, way_id BIGINT(20) UNSIGNED NOT NULL, operator VARCHAR(255), UNIQUE KEY way_id (way_id) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci') or die(mysql_error());
	mysql_query('CREATE TABLE railway_halts ( node_id BIGINT(20) PRIMARY KEY, name VARCHAR(255), INDEX node_id (node_id) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci') or die(mysql_error());
	//mysql_query('CREATE TABLE way_tags ( way_id BIGINT(20) UNSIGNED NOT NULL, field VARCHAR(255), value VARCHAR(255), UNIQUE KEY way_key (way_id, field), INDEX field (field), INDEX way_id (way_id)  )');

	mysql_query('DROP TABLE train');
	mysql_query('DROP TABLE train_stations');
	mysql_query('DROP TABLE bus');
	mysql_query('DROP TABLE relation_nodes');
	mysql_query('DROP TABLE relation_ways');
	//mysql_query('DROP TABLE relation_tags');
	mysql_query('CREATE TABLE train ( id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, way_id BIGINT(20) UNSIGNED NOT NULL, operator VARCHAR(255), UNIQUE KEY way_id (way_id) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci') or die(mysql_error());
	mysql_query('CREATE TABLE train_stations ( node_id BIGINT(20) PRIMARY KEY, name VARCHAR(255), INDEX node_id (node_id) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci') or die(mysql_error());
	mysql_query('CREATE TABLE bus ( relation_id BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY, name VARCHAR(255), ref VARCHAR(255), operator VARCHAR(255), INDEX relation_id (relation_id) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci') or die(mysql_error()); 
	mysql_query('CREATE TABLE relation_nodes ( relation_id BIGINT(20) UNSIGNED NOT NULL, node_id BIGINT(20) UNSIGNED NOT NULL, INDEX relation_id (relation_id), INDEX node_id (node_id) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci') or die(mysql_error());
	mysql_query('CREATE TABLE relation_ways ( relation_id BIGINT(20) UNSIGNED NOT NULL, way_id BIGINT(20) UNSIGNED NOT NULL, ordering INT(1) UNSIGNED, INDEX relation_id (relation_id), INDEX way_id (way_id) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci') or die(mysql_error());
	//mysql_query('CREATE TABLE relation_tags ( relation_id BIGINT(20) UNSIGNED NOT NULL, field VARCHAR(255), value VARCHAR(255), UNIQUE KEY relation_key (relation_id, field, INDEX field (field), INDEX relation_id (relation_id)  )');

	mysql_query('DROP TABLE street');
	mysql_query('DROP TABLE street_suburbs');
	mysql_query('CREATE TABLE street ( id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255), full_name VARCHAR(255) )'); // street is an entity we create, so it must be autoincrement unlike ways, nodes and relations
	mysql_query('CREATE TABLE street_suburbs ( street_id BIGINT(20), suburb_id BIGINT(20), INDEX street_id (street_id), INDEX suburb_id (suburb_id) )');
} else {
	$suburbs_query = mysql_query('SELECT node.id, node.lat, node.lon FROM suburb JOIN node ON suburb.node_id = node.id');
	while ($suburb = mysql_fetch_assoc($suburbs_query)) {
		$suburbs[$suburb['id']] = array($suburb['lat'], $suburb['lon']);
	}
}

//$data = file_get_contents('sample');
//$simplexml = simplexml_load_string($data);
$simplexml = simplexml_load_file('sample');

foreach ($simplexml as $node) {
	if ($node->getName() == 'node') {
		$node_id = $lat = $lon = NULL;
//		$extradata = array();
		foreach ($node->attributes() as $key => $value) {
			if ($key == 'id') $node_id = (string)$value;
			elseif ($key == 'lat') $lat = (float)$value;
			elseif ($key == 'lon') $lon = (float)$value;
//			else $extradata[$key] = $value;
		}
		if ($node_id !== NULL && $lat !== NULL && $lon !== NULL) {
			if (isset($suburbs[$node_id])) unset($suburbs[$node_id]);
			mysql_query('DELETE FROM node WHERE id = ' . $node_id);
			mysql_query('DELETE FROM suburb WHERE node_id = ' . $node_id);
			mysql_query('DELETE FROM train_stations WHERE node_id = ' . $node_id);
			mysql_query('DELETE FROM railway_halts WHERE node_id = ' . $node_id);
			mysql_query('INSERT INTO node (id, lat, lon) VALUES (' . $node_id . ', '. $lat . ',' . $lon . ')');
//			foreach ($extradata as $key => $value) {
//				mysql_query('INSERT INTO node_tags (node_id, field, value) VALUES (' . $node_id . ', "' . mysql_real_escape_string($key) . '", "' . mysql_real_escape_string($value) . '")');
//			}
			$tags = array();
			foreach ($node->children() as $tag) {
				if ($tag->getName() == "tag") {
					$k = $v = NULL;
					foreach ($tag->attributes() as $key => $value)
					{
						if ($key == 'k') $k = (string)$value;
						else if ($key == 'v') $v = (string)$value;
					}
					if ($k !== NULL && $v !== NULL) $tags[$k] = $v;
				}
			}
			if (isset($tags['place']) && $tags['place'] == 'suburb' && isset($tags['name'])) {
				$suburbs[$node_id] = array($lat, $lon);
				mysql_query('INSERT INTO suburb (node_id, name, is_in) VALUES (' . $node_id . ',"' . mysql_real_escape_string($tags['name']) . '", "' . mysql_real_escape_string($tags['is_in']) . '")');
			} elseif (isset($tags['railway']) && $tags['railway'] == 'station' && isset($tags['name'])) {
				mysql_query('INSERT INTO train_stations (node_id, name) VALUES (' . $node_id . ', "' . mysql_real_escape_string($tags['name']) . '")');
			} elseif (isset($tags['railway']) && $tags['railway'] == 'halt' && isset($tags['name'])) {
				mysql_query('INSERT INTO railway_halts (node_id, name) VALUES (' . $node_id . ', "' . mysql_real_escape_string($tags['name']) . '")');
			}
		}
	} else if ($node->getName() == 'way') {
		$way_id = NULL;
		foreach ($node->attributes() as $key => $value) {
			if ($key == 'id') $way_id = $value;
		}
		if ($way_id !== NULL) {
			$is_highway = FALSE;
			$railway = NULL;
			$train = NULL;
			$operator = "";
			$name = NULL;
			$nodes = array();
			foreach ($node->children() as $child) {
				$is_subway = $is_rail = FALSE;
				if ($child->getName() == 'nd') {
					foreach ($child->attributes() as $key => $value) 
						if ($key == 'ref')
							$nodes[] = (string)$value;
				}
				else if ($child->getName() == 'tag') {
					$v = NULL;
					$is_name = FALSE;
					$is_railway = FALSE;
					$is_train = FALSE;
					$is_operator = FALSE;
					foreach ($child->attributes() as $key => $value) 
					{
						if ($key == 'k' && $value == 'operator') $is_operator = TRUE;
						if ($key == 'k' && $value == 'highway') $is_highway = TRUE;
						if ($key == 'k' && $value == 'name') $is_name = TRUE;
						if ($key == 'v') $v = $value;
						if ($key == 'k' && $value == 'railway') $is_railway = TRUE;
					}
					if ($is_railway)
						if ($v == 'subway') $is_subway = TRUE;
						else if ($v == 'rail') $is_rail = TRUE;
				}
				if ($is_operator) $operator = $v;
				if ($is_subway) $railway = $v;
				if ($is_rail) $train = $v;
				if ($is_name) $name = $v;
			}
			if (($is_highway || $railway !== NULL || $train !== NULL) && $name !== NULL && count($nodes) > 0) {
				mysql_query('DELETE FROM way WHERE id = ' . $way_id);
				mysql_query('DELETE FROM railway WHERE way_id = ' . $way_id);
				mysql_query('DELETE FROM train WHERE way_id = ' . $way_id);
				mysql_query('DELETE FROM way_nodes WHERE way_id = ' . $way_id);
				mysql_query('INSERT INTO way (id, name) VALUES (' . $way_id . ', "' . mysql_real_escape_string($name) . '")');
				if ($railway !== NULL) mysql_query('INSERT INTO railway (way_id, operator) VALUES (' . $way_id . ', "' . $operator . '")');
				if ($train !== NULL) mysql_query('INSERT INTO train (way_id, operator) VALUES (' . $way_id . ', "' . $operator . '")');
				foreach ($nodes as $node)
					mysql_query('INSERT INTO way_nodes (way_id, node_id) VALUES (' . $way_id . ', ' . $node . ')');
			}
		}
	} else if ($node->getName() == 'relation') {
		$relation_id = NULL;
                foreach ($node->attributes() as $key => $value) {
                        if ($key == 'id') $relation_id = $value;
                }
		if ($relation_id !== NULL) {
			$ways = $nodes = array();
			$route = NULL;
			$name = $operator = $from = $to = $ref = '';
                        foreach ($node->children() as $child) {
                                if ($child->getName() == 'member') {
					$ref = $type = NULL;
                                        foreach ($child->attributes() as $key => $value) {
                                                if ($key == 'ref') $ref = (string)$value;
                                                else if ($key == 'type') $type = (string)$value;
					}
					if ($ref !== NULL && $type !== NULL && in_array($type, array('way', 'node'))) ${$type . 's'}[] = $ref;
                                } else if ($child->getName() == 'tag') {
					$k = $v = NULL;
					foreach ($child->attributes() as $key => $value) {
						${$key} = (string)$value;
					}
					if ($k !== NULL && $v !== NULL && in_array($k, array('ref', 'name', 'operator', 'route', 'from', 'to'))) ${$k} = $v;
				}
			}
			if ($route == 'bus') {
				mysql_query('DELETE FROM bus WHERE relation_id = ' . $way_id);
				mysql_query('DELETE FROM relation_ways WHERE relation_id = ' . $way_id);
				mysql_query('DELETE FROM relation_nodes WHERE relation_id = ' . $way_id);
				mysql_query('INSERT INTO bus (relation_id, name, operator, ref) VALUES (' . $relation_id . ', "' . mysql_real_escape_string($name) . '", "' . mysql_real_escape_string($operator) . '", "' . mysql_real_escape_string($ref) . '")');
				$i = 0;
				foreach ($ways as $way)
					mysql_query('INSERT INTO relation_ways (relation_id, way_id, ordering) VALUES (' . $relation_id . ', ' . $way . ', ' . ++$i . ')');
				foreach ($nodes as $node)
					mysql_query('INSERT INTO relation_nodes (relation_id, node_id) VALUES (' . $relation_id . ', ' . $node . ')');
			}
		}
	}
}

mysql_query('TRUNCATE street');
mysql_query('TRUNCATE street_suburbs');
mysql_query('UPDATE way SET street_id = NULL');
while (true) {
	$ways = mysql_query('SELECT way.id, way.name FROM way LEFT JOIN railway ON way.id = railway.way_id WHERE railway.id IS NULL AND way.street_id IS NULL ORDER BY way.name ASC LIMIT 500');
	$rows = mysql_num_rows($ways);
	if ($rows == 0) break;
	$streets_ways = array();
	$i = 0;
	while ($way = mysql_fetch_assoc($ways)) {
		if (count($streets_ways) == 0 || $streets_ways[0]['name'] == $way['name'])
		{
			$streets_ways[] = $way;
		}

		if ((++$i == $rows && $rows < 500) || (count($streets_ways) > 0 && $streets_ways[0]['name'] != $way['name'])) {
			$ways_nodes = array();
			foreach ($streets_ways as $street_way) {
				$nodes = mysql_query('SELECT node.id, node.lat, node.lon FROM node LEFT JOIN way_nodes ON node.id = way_nodes.node_id WHERE way_id = ' . $street_way['id']);
				$ways_nodes[$street_way['id']] = array();
				while ($node = mysql_fetch_assoc($nodes)) {
					$ways_nodes[$street_way['id']][] = $node;
				}
			}
			$streets = $streets_nodes = array();
			while (count($streets_ways) > 0) {
				$initial_count = count($streets_ways);
				foreach ($streets_ways as $streets_ways_key => $free_way) {
					foreach ($ways_nodes[$free_way['id']] as $node) {
						foreach ($streets_nodes as $k => $street_nodes) {
							if (in_array($node, $street_nodes) || is_node_near_nodes($node, $street_nodes)) {
								$streets[$k][] = $free_way;
								foreach ($ways_nodes[$free_way['id']] as $node)
									$streets_nodes[$k][] = $node;
								unset($streets_ways[$streets_ways_key]);
								break 2;
							}
						}
					}
				}
				if ($initial_count == count($streets_ways)) {
					$streets[] = array(array_shift($streets_ways));
					$streets_nodes[] = array();
					foreach ($ways_nodes[$streets[0][0]['id']] as $node)
						$streets_nodes[0][] = $node;
				}
			}
			foreach ($streets as $street) {
				mysql_query('INSERT INTO street (name) VALUES ("' . mysql_real_escape_string($street[0]['name']) . '")');
				$street_id = (int)mysql_insert_id();
				if ($street_id == 0) die('Error inserting street');
				$street_ways_id = $street_suburbs_id = array();
				foreach ($street as $street_way) {
					$street_ways_id[] = $street_way['id'];
					$nodes = mysql_query('SELECT node.lat, node.lon FROM node JOIN way_nodes ON way_nodes.node_id = node.id WHERE way_nodes.way_id = ' . $street_way['id']);
					while ($node = mysql_fetch_assoc($nodes)) {
						$suburb_id = NULL;
						$min_distance = NULL;
						foreach ($suburbs as $node_id => $coords) {
							$distance = calculate_distance($node['lat'], $node['lon'], $coords[0], $coords[1]);
							if ($min_distance === NULL || $min_distance > $distance) {
								$suburb_id = $node_id;
								$min_distance = $distance;
							}
						}
						if (!in_array($suburb_id, $street_suburbs_id)) $street_suburbs_id[] = $suburb_id;
					}
				}
				mysql_query('UPDATE way SET street_id = ' .  $street_id . ' WHERE id IN (' . implode(',', $street_ways_id) . ')');
				foreach ($street_suburbs_id as $street_suburb_id) {
					mysql_query('INSERT INTO street_suburbs (street_id, suburb_id) VALUES (' . $street_id . ', ' . $street_suburb_id . ')');
				}
				$full_name = $street[0]['name'] . ' ';
				if (count($street_suburbs_id) > 0)
				{
					$street_suburbs = mysql_query('SELECT name, is_in FROM suburb WHERE node_id IN (' . implode(',', $street_suburbs_id) . ')');
					$suburb_names = array();
					$is_in = array();
					while ($suburb = mysql_fetch_assoc($street_suburbs)) {
						$suburb_names[] = $suburb['name'];
						if (!in_array($suburb['is_in'], $is_in)) $is_in[] = $suburb['is_in'];
					}
					if (count($street_suburbs_id) < 4)
						$full_name .= '(' . implode(', ', $suburb_names) . '), ';
					$full_name .= '(' . implode(', ', $is_in) . ')';
					mysql_query('UPDATE street SET full_name = "' . mysql_real_escape_string($full_name) . '" WHERE id = ' . $street_id);
				}

			}
			$streets_ways = array($way);
		}
	}
}
