<?php
function html_street_lookup($name, $intersection = FALSE) {
	return '<input type="text" name="' . $name . '" id="' . $name . '" />
                <div id="' . $name . '_options"></div>
                <script type="text/javascript">
                        new Ajax.Autocompleter("' . $name . '", "' . $name . '_options", "street_lookup.php", {"paramName": "street"});
                </script>' .
		($intersection === FALSE ? '' : 
                '<input type="text" name="' . $name . '_intersection" id="' . $name . '_intersection" />
                <div id="' . $name . '_intersection_options"></div>
                <script type="text/javascript">
                        new Ajax.Autocompleter("' . $name . '_intersection", "' . $name . '_intersection_options", "street_intersection_lookup.php", { "callback": function(element, params) { return params + "&street=" + encodeURI($("' . $name . '").value); }, "paramName": "street_intersection" });
                </script>');
}
