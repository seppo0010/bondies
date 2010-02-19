<?php
function html_street_lookup($label, $name, $intersection = FALSE) {
	return '
		Bookmarks: <select name="' . $name . '_bookmarks" id="' . $name . '_bookmarks"></select><br />
		<label for="' . $name . '">' . ucfirst($label) . ':</label> <input type="text" name="' . $name . '" id="' . $name . '" value="' . (isset($_REQUEST[$name]) ? htmlentities($_REQUEST[$name], ENT_QUOTES) : '') . '" />
		<input type="hidden" name="' . $name . '_id" id="' . $name . '_id" value="' . (isset($_REQUEST[$name . '_id']) ? htmlentities($_REQUEST[$name . '_id'], ENT_QUOTES) : '') . '" />
                <div id="' . $name . '_options"></div>
                <script type="text/javascript">
                        new Ajax.Autocompleter("' . $name . '", "' . $name . '_options", "street_lookup.php", {"callback": function(element, params) { return params + "&zone=" + encodeURI($F("zones")) + "&subzone=" + encodeURI($F("subzones")); }, "paramName": "street", "afterUpdateElement": function(element, selected) { $("' . $name . '_id").value = selected.id.substr(7); } });
                </script>' .
		($intersection === FALSE ? '' : 
                '<br /><label for="' . $name . '_intersection">' . ucfirst($label) . ' intersection:</label> <input type="text" name="' . $name . '_intersection" id="' . $name . '_intersection" value="' . (isset($_REQUEST[$name . '_intersection']) ? htmlentities($_REQUEST[$name . '_intersection'], ENT_QUOTES) : '') . '" />
		<input type="hidden" name="' . $name . '_intersection_id" id="' . $name . '_intersection_id" value="' . (isset($_REQUEST[$name . '_intersection_id']) ? htmlentities($_REQUEST[$name . '_intersection_id'], ENT_QUOTES) : '') . '" />
                <div id="' . $name . '_intersection_options"></div>
		<input type="button" value="bookmark" id="bookmark_' . $name . '" />
                <script type="text/javascript">
                        new Ajax.Autocompleter("' . $name . '_intersection", "' . $name . '_intersection_options", "street_intersection_lookup.php", { "callback": function(element, params) { return params + "&street_id=" + encodeURI($F("' . $name . '_id")) + "&zone=" + encodeURI($F("zones")) + "&subzone=" + encodeURI($F("subzones")); }, "paramName": "street_intersection", "afterUpdateElement": function(element, selected) { $("' . $name . '_intersection_id").value = selected.id.substr(7); if ($("' . $name . '_intersection_id").value.length == 0) $("' . $name . '_intersection").value = ""; } });
			Event.observe($("bookmark_' . $name . '"), "click", function() {
				if ($F("' . $name . '").length * $F("' . $name . '_id").length * $F("' . $name . '_intersection").length * $F("' . $name . '_intersection_id").length == 0) {
					alert("Please fill in a street and an intersection");
				} else {
					var name = prompt("Name the bookmark");
					window.bookmarks.push({ "name": name, "street_id" : $F("' . $name . '_id"), "street_name": $F("' . $name . '"), "street_intersection_id" : $F("' . $name . '_intersection_id"), "street_intersection_name": $F("' . $name . '_intersection") });
					createCookie("bookmarks", window.bookmarks.toJSON());
					reloadBookmarks();
					alert("Bookmark created");
				}
			});
			Event.observe($("' . $name . '_bookmarks"), "change", function() {
				var key = $F("' . $name . '_bookmarks");
				if (key < 0) return;
				var info = window.bookmarks[$F("' . $name . '_bookmarks")];
				$("' . $name . '").value = info.street_name;
				$("' . $name . '_id").value = info.street_id;
				$("' . $name . '_intersection").value = info.street_intersection_name;
				$("' . $name . '_intersection_id").value = info.street_intersection_id;
			});

			if (!window.bookmarks_selects) window.bookmarks_selects = [];
			function reloadBookmarks() {
				for (var i = 0; i <  window.bookmarks_selects.length; i++) {
					var select = window.bookmarks_selects[i];
					$(select).update("");
					$(select).insert(new Element("option", { "value": "-1" }));
					for (var j = 0; j < window.bookmarks.length; j++)
						$(select).insert(new Element("option", { value: j } ).update(window.bookmarks[j].name));
				}
			}
			window.bookmarks_selects.push($("' . $name . '_bookmarks"));
			reloadBookmarks();
                </script>
		');
}

function html_utf8($str) {
	return htmlentities($str, ENT_COMPAT, 'UTF-8');
}
