<?php
function pluralize($term) {
	if ($term == 'bus') return 'buses';
	return $term . 's';
}
