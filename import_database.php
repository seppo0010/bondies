<?php
require 'boot.php';

mysql_query('DROP TABLE node');
mysql_query('DROP TABLE suburb');
//mysql_query('DROP TABLE node_tags');
mysql_query('CREATE TABLE node ( id BIGINT(20) unsigned not null PRIMARY KEY, lat FLOAT(9,7), lon FLOAT(9,7), suburb_id BIGINT(20) UNSIGNED DEFAULT NULL );' );
//mysql_query('CREATE TABLE node_tags ( node_id BIGINT(20) not null, field VARCHAR(255), value VARCHAR(255), UNIQUE KEY node_key (node_id, field), INDEX field (field), INDEX node_id (node_id) )');
mysql_query('CREATE TABLE suburb ( node_id BIGINT(20) PRIMARY KEY, name VARCHAR(255), is_in VARCHAR(255) )');

mysql_query('DROP TABLE way');
mysql_query('DROP TABLE way_nodes');
//mysql_query('DROP TABLE way_tags');
mysql_query('CREATE TABLE way ( id BIGINT(20) unsigned not null PRIMARY KEY, name VARCHAR(255), suburb_id BIGINT(20) UNSIGNED DEFAULT NULL, street_id BIGINT(20), INDEX name (name) )');
mysql_query('CREATE TABLE way_nodes ( way_id BIGINT(20) UNSIGNED NOT NULL, node_id BIGINT(20) UNSIGNED NOT NULL )');
//mysql_query('CREATE TABLE way_tags ( way_id BIGINT(20) UNSIGNED NOT NULL, field VARCHAR(255), value VARCHAR(255), UNIQUE KEY way_key (way_id, field), INDEX field (field), INDEX way_id (way_id)  )');

mysql_query('DROP TABLE relation');
mysql_query('DROP TABLE relation_nodes');
mysql_query('DROP TABLE relation_ways');
//mysql_query('DROP TABLE relation_tags');
mysql_query('CREATE TABLE relation ( id BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY, ref VARCHAR(255), name VARCHAR(255), type INT(2) UNSIGNED )'); 
mysql_query('CREATE TABLE relation_nodes ( relation_id BIGINT(20) UNSIGNED NOT NULL, node_id BIGINT(20) UNSIGNED NOT NULL )');
mysql_query('CREATE TABLE relation_ways ( relation_id BIGINT(20) UNSIGNED NOT NULL, way_id BIGINT(20) UNSIGNED NOT NULL )');
//mysql_query('CREATE TABLE relation_tags ( relation_id BIGINT(20) UNSIGNED NOT NULL, field VARCHAR(255), value VARCHAR(255), UNIQUE KEY relation_key (relation_id, field, INDEX field (field), INDEX relation_id (relation_id)  )');

mysql_query('DROP TABLE street');
mysql_query('DROP TABLE street_suburbs');
mysql_query('CREATE TABLE street ( id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) )') or die(mysql_error()); // street is an entity we create, so it must be autoincrement unlink ways, nodes and relations
mysql_query('CREATE TABLE street_suburbs ( street_id BIGINT(20), suburb_id BIGINT(20) )');

$data = file_get_contents('sample');
$simplexml = simplexml_load_string($data);

$suburbs = array();
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
			mysql_query('INSERT INTO node (id, lat, lon) VALUES (' . $node_id . ', '. $lat . ',' . $lon . ')');
//			foreach ($extradata as $key => $value) {
//				mysql_query('INSERT INTO node_tags (node_id, field, value) VALUES (' . $node_id . ', "' . mysql_real_escape_string($key) . '", "' . mysql_real_escape_string($value) . '")');
//			}
/*
  <node id="349329661" lat="-34.6213108" lon="-58.3708066" version="1" changeset="635452" user="Geogast" uid="51045" visible="true" timestamp="2009-02-22T23:12:53Z">
    <tag k="name" v="San Telmo"/>
    <tag k="place" v="suburb"/>
    <tag k="created_by" v="Potlatch 0.10f"/>
    <tag k="is_in" v="Capital Federal, Buenos Aires"/>
  </node>
*/
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
			}  
		}
	} else if ($node->getName() == 'way') {
		$way_id = NULL;
		foreach ($node->attributes() as $key => $value) {
			if ($key == 'id') $way_id = $value;
		}
		if ($way_id !== NULL) {
			$name = NULL;
			$nodes = array();
			foreach ($node->children() as $child) {
				if ($child->getName() == 'nd') {
					foreach ($child->attributes() as $key => $value) 
						if ($key == 'ref')
							$nodes[] = $value;
				}
				else if ($child->getName() == 'tag') {
					$v = NULL;
					$is_name = FALSE;
					foreach ($child->attributes() as $key => $value) 
					{
						if ($key == 'k' && $value == 'name') $is_name = TRUE;
						if ($key == 'v') $v = $value;
					}
					if ($is_name) $name = $v;
				}
			}
			if ($name !== NULL && count($nodes) > 0) {
				mysql_query('INSERT INTO way (id, name) VALUES (' . $way_id . ', "' . mysql_real_escape_string($name) . '")');
				foreach ($nodes as $node)
					mysql_query('INSERT INTO way_nodes (way_id, node_id) VALUES (' . $way_id . ', ' . $node_id . ')');
			}
		}
	}
}

while (true) {
	$nodes = mysql_query('SELECT id, lat, lon FROM node WHERE suburb_id IS NULL LIMIT 500');
	if (mysql_num_rows($nodes) == 0) break;
	while ($node = mysql_fetch_assoc($nodes)) {
		$min_distance = $node_suburb_id = -1;
		foreach ($suburbs as $suburb_id => $coords) {
			$distance = ($coords[0] - $nodes['lat']) * ($coords[0] - $nodes['lat']) + ($coords[1] - $nodes['lon']) * ($coords[1] - $nodes['lon']);
			if (($min_distance == -1 && $node_suburb_id == -1) || $distance < $min_distance) {
				$min_distance = $distance;
				$node_suburb_id = $suburb_id;
			}
		}
		mysql_query('UPDATE node SET suburb_id = ' . $node_suburb_id . ' WHERE id = ' . $node['id']) or die(mysql_error());
	}
}
while (true) {
	$ways = mysql_query('SELECT id, name, suburb_id FROM way WHERE street_id IS NULL ORDER BY name ASC LIMIT 500');
	if (mysql_num_rows($ways) == 0) break;
	$street_name = '';
	$street_ways_id = $street_suburbs_id = array();
	while ($way = mysql_fetch_assoc($ways)) {
		if ($way['suburb_id'] === NULL) {
			$nodes = mysql_query('SELECT node.suburb_id FROM way_nodes LEFT JOIN node ON way_nodes.node_id = node.id WHERE way_nodes.way_id = ' . $way['id']);
			$suburbs = array();
			while (list($suburb_id) = mysql_fetch_row($nodes)) {
				if (isset($suburbs[$suburb_id])) ++$suburbs[$suburb_id];
				else $suburbs[$suburb_id] = 1;
			}
			$way['suburb_id'] = array_search(max($suburbs), $suburbs);
			if (empty($way['suburb_id'])) die('Unable to calculate suburb');
			mysql_query('UPDATE way SET suburb_id = ' . $way['suburb_id'] . ' WHERE id = ' . $way['id']);
		}
		if ($street_name == '' || $street_name == $way['name'])
		{
			$street_name = $way['name'];
			if (!in_array($way['suburb_id'], $street_suburbs_id)) $street_suburbs_id[] = $way['suburb_id'];
			$street_ways_id[] = $way['id'];
		} else {
			mysql_query('INSERT INTO street (name) VALUES ("' . mysql_real_escape_string($street_name) . '")') or die(mysql_error());
			$street_id = (int)mysql_insert_id();
			if ($street_id == 0) die('Error inserting street');
			mysql_query('UPDATE way SET street_id = ' .  $street_id . ' WHERE id IN (' . implode(',', $street_ways_id) . ')') or die(mysql_error());
			foreach ($street_suburbs_id as $street_suburb_id) {
				mysql_query('INSERT INTO street_suburbs (street_id, suburb_id) VALUES (' . $street_id . ', ' . $street_suburb_id . ')') or die(mysql_error());
			}
			$street_name = '';
			$street_ways_id = $street_suburbs_id = array();
		}
	}
}
