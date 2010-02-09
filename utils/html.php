<?php
function html_street_lookup($label, $name, $intersection = FALSE) {
	return '<label for="' . $name . '">' . ucfirst($label) . ':</label> <input type="text" name="' . $name . '" id="' . $name . '" value="' . (isset($_REQUEST[$name]) ? htmlentities($_REQUEST[$name], ENT_QUOTES) : '') . '" />
		<input type="hidden" name="' . $name . '_id" id="' . $name . '_id" value="' . (isset($_REQUEST[$name . '_id']) ? htmlentities($_REQUEST[$name . '_id'], ENT_QUOTES) : '') . '" />
                <div id="' . $name . '_options"></div>
                <script type="text/javascript">
                        new Ajax.Autocompleter("' . $name . '", "' . $name . '_options", "street_lookup.php", {"callback": function(element, params) { return params + "&zone=" + encodeURI($F("zones")) + "&subzone=" + encodeURI($F("subzones")); }, "paramName": "street", "afterUpdateElement": function(element, selected) { $("' . $name . '_id").value = selected.id.substr(7); } });
                </script>' .
		($intersection === FALSE ? '' : 
                '<br /><label for="' . $name . '_intersection">' . ucfirst($label) . ' intersection:</label> <input type="text" name="' . $name . '_intersection" id="' . $name . '_intersection" value="' . (isset($_REQUEST[$name . '_intersection']) ? htmlentities($_REQUEST[$name . '_intersection'], ENT_QUOTES) : '') . '" />
		<input type="hidden" name="' . $name . '_intersection_id" id="' . $name . '_intersection_id" value="' . (isset($_REQUEST[$name . '_intersection_id']) ? htmlentities($_REQUEST[$name . '_intersection_id'], ENT_QUOTES) : '') . '" />
                <div id="' . $name . '_intersection_options"></div>
                <script type="text/javascript">
                        new Ajax.Autocompleter("' . $name . '_intersection", "' . $name . '_intersection_options", "street_intersection_lookup.php", { "callback": function(element, params) { return params + "&street_id=" + encodeURI($F("' . $name . '_id")) + "&zone=" + encodeURI($F("zones")) + "&subzone=" + encodeURI($F("subzones")); }, "paramName": "street_intersection", "afterUpdateElement": function(element, selected) { $("' . $name . '_intersection_id").value = selected.id.substr(7); if ($("' . $name . '_intersection_id").value.length == 0) $("' . $name . '_intersection").value = ""; } });
                </script>');
}

function html_utf8($str) {
	return htmlentities($str, ENT_COMPAT, 'UTF-8');
}
