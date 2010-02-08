<?php
function html_street_lookup($name, $intersection = FALSE) {
	return '<input type="text" name="' . $name . '" id="' . $name . '" />
		<input type="hidden" name="' . $name . '_id" id="' . $name . '_id" />
                <div id="' . $name . '_options"></div>
                <script type="text/javascript">
                        new Ajax.Autocompleter("' . $name . '", "' . $name . '_options", "street_lookup.php", {"callback": function(element, params) { return params + "&zone=" + encodeURI($F("zones")) + "&subzone=" + encodeURI($F("subzones")); }, "paramName": "street", "afterUpdateElement": function(element, selected) { $("' . $name . '_id").value = selected.id.substr(7); } });
                </script>' .
		($intersection === FALSE ? '' : 
                '<input type="text" name="' . $name . '_intersection" id="' . $name . '_intersection" />
		<input type="hidden" name="' . $name . '_intersection_id" id="' . $name . '_intersection_id" />
                <div id="' . $name . '_intersection_options"></div>
                <script type="text/javascript">
                        new Ajax.Autocompleter("' . $name . '_intersection", "' . $name . '_intersection_options", "street_intersection_lookup.php", { "callback": function(element, params) { return params + "&street_id=" + encodeURI($F("' . $name . '_id")) + "&zone=" + encodeURI($F("zones")) + "&subzone=" + encodeURI($F("subzones")); }, "paramName": "street_intersection", "afterUpdateElement": function(element, selected) { $("' . $name . '_intersection_id").value = selected.id.substr(7); if ($("' . $name . '_intersection_id").value.length == 0) $("' . $name . '_intersection").value = ""; } });
                </script>');
}

function html_utf8($str) {
	return htmlentities($str, ENT_COMPAT, 'UTF-8');
}
