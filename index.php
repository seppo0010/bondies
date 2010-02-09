<?php
require_once 'boot.php';
require_once 'utils/html.php';
require_once 'utils/db_list.php';
$zones = list_zones();
?>
<html>
<head>
<title>Bondies</title>
<style type="text/css">
ul, li { list-style-type: none; display: block; padding: 4px; margin:0; }
li.even {background: #999; }
li.odd {background: #666; }
</style>
<script type="text/javascript" src="javascript/prototype/prototype.js"></script>
<script type="text/javascript" src="javascript/scriptaculous/effects.js"></script>
<script type="text/javascript" src="javascript/scriptaculous/controls.js"></script>
<script type="text/javascript">
var zones = <?php echo htmlentities(json_encode($zones), ENT_NOQUOTES, 'UTF-8'); ?>;
function subzone() {
	var value = $F('zones');
	var subzones = zones[value];
	var select = $('subzones');
	select.update('');
	select.insert(new Element('option', { 'value': '' }).update('All'));
	$A(subzones).each(function(i) { select.insert(new Element('option', { "value": i.node_id }).update(i.name)); });
}
Event.observe(window,'load',function() {
	Event.observe($('zones'), 'change', subzone);
	subzone();
})
</script>
</head>
<body>
<select name="zones" id="zones">
<?php foreach ($zones as $zone => $subzones) echo '<option value="' . html_utf8($zone) . '">' . html_utf8($zone) . '</option>'; ?>
</select>
<select name="subzones" id="subzones">
<option value=""></option>
</select>
<form method="post" action="results.php" onsubmit="if ($F('from_id').length * $F('from_intersection_id').length * $F('to_id').length * $F('to_intersection_id').length == 0) { alert('a'); return false; } ">
	<div id="corner_from">
		<?php echo html_street_lookup('origin', 'from', TRUE); ?>
	</div><br />
	<div id="corner_to">
		<?php echo html_street_lookup('destination', 'to', TRUE); ?>
	</div><br />
	<div>
		Note: Only available intersections will appear
	</div><br />
	<div id="transportation">
		<input type="checkbox" name="bus" id="bus" /><label for="bus">Bus</label>
		<input type="checkbox" name="train" id="train" /><label for="train">Train</label>
		<input type="checkbox" name="railway" id="railway" /><label for="railway">Subway</label>
	</div>
	<div>
		<input type="submit" value="Buscar" />
	</div>
</form>
</body>
</html>
