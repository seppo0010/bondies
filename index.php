<?php
require 'utils/html.php';
?>
<html>
<head>
<title>Bondies</title>
<script type="text/javascript" src="javascript/prototype/prototype.js"></script>
<script type="text/javascript" src="javascript/scriptaculous/effects.js"></script>
<script type="text/javascript" src="javascript/scriptaculous/controls.js"></script>
</head>
<body>
<form method="post" action="results.php">
	<div id="corner_from">
		<?php echo html_street_lookup('from', TRUE); ?>
	</div>
	<div id="corner_to">
		<?php echo html_street_lookup('to', TRUE); ?>
	</div>
	<div id="transportation">
		<input type="checkbox" name="bus" id="bus" /><label for="bus">Bus</label>
		<input type="checkbox" name="train" id="train" /><label for="train">Train</label>
		<input type="checkbox" name="railway" id="railway" /><label for="railway">Railway</label>
	</div>
	<div>
		<input type="submit" value="Buscar" />
	</div>
</form>
</body>
</html>
