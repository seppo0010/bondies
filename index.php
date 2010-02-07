<?php
require 'html_util.php';
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
	<div>
		<input type="submit" value="Buscar" />
	</div>
</form>
</body>
</html>
