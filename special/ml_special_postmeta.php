<?php
namespace morelang;
/* Set the selected Post metas to be unprotected (then they can be translated) */


add_filter('is_protected_meta', 'morelang\ml_special_is_protected_meta', 1000001, 3);
function ml_special_is_protected_meta($protected, $meta_key, $meta_type) {
	global $special_opt;
	if ( in_array($meta_key, $special_opt->ml_special_post_meta_keys) ) return FALSE;

	return $protected;
}
