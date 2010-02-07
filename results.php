<?php
require 'boot.php';
$from_node = mysql_query('SELECT node.id FROM node LEFT JOIN way_nodes ON node.id = way_nodes.node_id LEFT JOIN way ON way_nodes.way_id = way.id LEFT JOIN street ON way.street_id = street.id WHERE street.name = "' . mysql_real_escape_string($_REQUEST['from']) . '" AND node.id IN (SELECT node.id FROM node LEFT JOIN way_nodes ON node.id = way_nodes.node_id LEFT JOIN way ON way_nodes.way_id = way.id LEFT JOIN street ON way.street_id = street.id WHERE street.name = "' . mysql_real_escape_string($_REQUEST['from_intersection']) . '")') or die(mysql_error());
if (mysql_num_rows($from_node) == 0) die('Unable to find origin intersection');
$to_node = mysql_query('SELECT node.id FROM node LEFT JOIN way_nodes ON node.id = way_nodes.node_id LEFT JOIN way ON way_nodes.way_id = way.id LEFT JOIN street ON way.street_id = street.id WHERE street.name = "' . mysql_real_escape_string($_REQUEST['to']) . '" AND node.id IN (SELECT node.id FROM node LEFT JOIN way_nodes ON node.id = way_nodes.node_id LEFT JOIN way ON way_nodes.way_id = way.id LEFT JOIN street ON way.street_id = street.id WHERE street.name = "' . mysql_real_escape_string($_REQUEST['to_intersection']) . '")') or die(mysql_error());
if (mysql_num_rows($to_node) == 0) die('Unable to find destination intersection');


