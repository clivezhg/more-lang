<?php
/* Calculate the Wordpress version number for more-lang */
function ml_calc_wpvernum() {
	global $wp_version;
	$ml_wpvernum = 1;
	$vernums =  explode('.', $wp_version);
	if ( is_numeric($vernums[0]) ) $ml_wpvernum = $vernums[0] * 10000;
	if ( isset($vernums[1]) && is_numeric($vernums[1]) ) $ml_wpvernum = $ml_wpvernum + $vernums[1] * 100;
	if ( isset($vernums[2]) && is_numeric($vernums[2]) ) $ml_wpvernum = $ml_wpvernum + $vernums[2];
	// more-lang doesn't care about more minor version-number.
	return $ml_wpvernum;
}


function string_ends_with($str, $sub) {
	if ( ! is_string($str) || ! is_string($sub) ) return FALSE;
	$start = max(0, strlen($str) - strlen($sub));
	return substr($str, $start) === $sub;
}
