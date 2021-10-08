<?php
namespace morelang;


/* Parse text using the delimeter '|||' */
function ml_special_get_req_part( $text ) {
	global $ml_registered_mlocales;
	if ( is_string($text) && strpos($text, '|||') !== FALSE ) {
		$cur_locale = ml_get_locale();
		$idx = 0;
		foreach ($ml_registered_mlocales as $key=>$val) {
			if ($cur_locale === $val) $idx = $key+1;
		}

		$texts = explode('|||', $text);
		if ( count($texts) > $idx ) {
			return $texts[$idx];
		}
		else {
			return $texts[0]; // the default
		}
	}

	return $text;
}
