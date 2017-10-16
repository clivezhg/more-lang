<?php
namespace morelang;


add_filter('get_post_metadata', 'morelang\morelang_post_metadata', 10, 4);
function morelang_post_metadata($value, $object_id, $meta_key, $single) {
	if (strpos($meta_key, '_morelang_') !== FALSE) return $value;
	$locale = ml_get_locale();
	if ( $locale ) {
		$custom_meta = get_post_meta( $object_id, ml_postmeta_name($meta_key, $locale), $single );
		if ($custom_meta) return $custom_meta;
	}
	return $value;
}

add_filter('the_meta_key', 'morelang\morelang_meta_key', 10, 3);
function morelang_meta_key($value, $key, $value) {
	// the original '$value': "<li><span class='post-meta-key'>$key:</span> $value</li>\n"
	if (strpos($key, '_morelang_') !== FALSE) return $value;
	$the_id = get_the_ID();
	$locale = ml_get_locale();
	if ( $locale ) {
		$custom_meta = get_post_meta( $the_id, ml_postmeta_name($key, $locale), TRUE );
		if ($custom_meta) {
			return "<li><span class='post-meta-key'>$key:</span> $custom_meta</li>\n";
		}
	}
	return $value;
}


@include_once 'pro/ml_pro_text.php';
