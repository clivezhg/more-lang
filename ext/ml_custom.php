<?php
namespace morelang;
/* The localization of the custom meta fields in a post */
/* Array value(same meta_key for multiple metas in a post) is not supported */


/* The max length of meta_key column is 256 */
global $ml_registered_mlocales;
$max_locale_len = 0;
foreach ( $ml_registered_mlocales as $mlocale) $max_locale_len = max( $max_locale_len, strlen($mlocale) );
define( 'ML_MAX_METALEN', 256 - $max_locale_len - 2 - strlen(ML_UID) );
define( 'ML_METAKEY_OVERFLOW',
		sprintf( __('The "Name" should have less than %d characters', ML_TDOMAIN), ML_MAX_METALEN ) );

/* Skip specific metas to avoid infinite loop */
function ml_skip_meta( $meta_key ) {
	if ( ! is_string( $meta_key ) ) return true;
	if ( strpos( $meta_key, ML_UID ) > 0 ) return true;
	if ( is_protected_meta( $meta_key, 'post' ) ) return true; // '_' as first character, by default
	return false;
}

/* Validate strings from the $_POST */
function ml_validate_str( $val, $blank_allowed = false, $not_string_die = true ) {
	if ( $not_string_die && ! is_string( $val ) ) {
		wp_die(-1);
	}
	if ( $val === '' ) return false;
	if ( ! $blank_allowed && trim($val) === '' ) return false;
	return true;
}


/* Get localized post metas for a given meta */
add_action( 'wp_ajax_ml-get-meta', 'morelang\ml_get_meta' );
function ml_get_meta() {
	check_ajax_referer('change-meta');

	global $ml_registered_mlocales, $wpdb;
	$ret_obj = new \stdClass();
	if ( isset( $_POST['post_id'] ) && ml_validate_str( $_POST['post_id'] )
			&& isset( $_POST['metaname'] ) && ml_validate_str( $_POST['metaname'] ) ) {
		$p_post_id = (int) $_POST['post_id'];
		if ( $p_post_id < 1 ) wp_die(-1);
		foreach ( $ml_registered_mlocales as $mlocale ) {
			$meta_key = ml_postmeta_name( wp_unslash( $_POST['metaname'] ), $mlocale );
			$meta_val = get_post_meta( $p_post_id, $meta_key, true );
			$mid = $wpdb->get_var( $wpdb->prepare("SELECT meta_id FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s"
					, $p_post_id, $meta_key) );
			$ret_obj->$mlocale = array('value' => $meta_val, 'meta_id' => $mid,
					'meta_key' => $meta_key, 'del_nonce' => wp_create_nonce('delete-meta_' . $mid));
		}
	}

	wp_send_json( $ret_obj );
}


/* Get all the localized post metas for a Post */
add_action( 'wp_ajax_ml-get-all-metas', 'morelang\ml_get_all_metas' );
function ml_get_all_metas() {
	check_ajax_referer('change-meta');

	global $ml_registered_mlocales, $wpdb;
	$locale_metas = array();
	if ( isset( $_POST['post_id'] ) && ml_validate_str( $_POST['post_id'] ) ) {
		$p_post_id = (int) $_POST['post_id'];
		if ( $p_post_id < 1 ) wp_die(-1);
		$metas = get_post_meta( $p_post_id, '', TRUE ); // TRUE is skipped for ''.
		$skip_metas = ['post_title', 'post_excerpt', 'post_content'];
		foreach ( $ml_registered_mlocales as $mlocale ) {
			$cur_metas = array();
			foreach ($metas as $key=>$val) {
				if ( is_array($val) ) $val = count($val) > 0 ? $val[0] : NULL;
				if ( $val === NULL ) continue;
				if ( string_ends_with($key, '_' . ML_UID . $mlocale) ) {
					$len1 = strlen($key);
					$len2 = strlen(ML_UID . $mlocale);
					$key_o = substr($key, 1, $len1-$len2-2);
					if ( in_array($key_o, $skip_metas) ) {
						continue;
					}
					$mid = $wpdb->get_var( $wpdb->prepare("SELECT meta_id FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s"
							, $p_post_id, $key) );
					$cur_metas[$key_o] = array('meta_id' => $mid, 'value' => $val
							, 'meta_key' => $key, 'del_nonce' => wp_create_nonce('delete-meta_' . $mid));
				}
			}
			if ( count($cur_metas) > 0 ) $locale_metas[$mlocale] = $cur_metas;
		}
	}

	wp_send_json( $locale_metas );
}


/* Invoked by: 1."Add Custom Field"; 2."Publish" or "Update" the whole post */
add_action( 'added_post_meta', 'morelang\ml_added_post_meta', 10, 4 );
function ml_added_post_meta( $mid, $object_id, $meta_key, $_meta_value ) {
	if ( ml_skip_meta( $meta_key ) ) return;

	if ( strlen($meta_key) > ML_MAX_METALEN ) {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) wp_die( ML_METAKEY_OVERFLOW );
		else return;
	}

	$meta_key = wp_slash( $meta_key );
	global $ml_registered_mlocales;
	foreach ( $ml_registered_mlocales as $mlocale ) {
		$val_name = 'morelang_' . $mlocale . '_metavalue';
		if ( isset( $_POST[$val_name] ) && ml_validate_str( $_POST[$val_name] ) ) {
			$loc_meta_key = ml_postmeta_name( $meta_key, $mlocale );
			add_post_meta( $object_id, $loc_meta_key, $_POST[$val_name] );
		}
	}
}


/* Invoked by: 1."Update"(Ajax) a single meta group */
add_action( 'wp_ajax_ml-upd-meta', 'morelang\ml_update_meta' );
function ml_update_meta() {
	if ( ! isset( $_POST['dflt_meta_id'] ) || get_post_meta_by_id( (int) $_POST['dflt_meta_id'] ) === false ) {
		wp_die( __('The default meta is missing', ML_TDOMAIN) );
	}

	function ml_is_protected_meta( $protected, $meta_key, $meta_type ) {
		if ( is_string($meta_key) && strpos($meta_key, ML_UID) > 0 ) return false;
		return $protected;
	}
	add_filter( 'is_protected_meta', 'morelang\ml_is_protected_meta', 10, 3 );

	if ( isset($_POST['add-new']) && $_POST['add-new'] === 'true' ) {
		check_ajax_referer('add-meta', '_ajax_nonce-add-meta');
		if ( isset($_POST['post_id']) && ml_validate_str( $_POST['post_id'] )
				&& isset($_POST['meta_key']) && ml_validate_str( $_POST['meta_key'] )
				&& isset($_POST['meta_value']) && ml_validate_str( $_POST['meta_value'], true )
				&& isset($_POST['locale']) && ml_validate_str( $_POST['locale'] ) ) {
			global $ml_registered_mlocales;
			if ( ! in_array($_POST['locale'], $ml_registered_mlocales) ) wp_die(-1);
			$p_post_id = (int) $_POST['post_id'];
			if ( $p_post_id < 1 ) wp_die(-1);
			$p_meta_key = $_POST['meta_key'];
			if ( strlen( wp_unslash($p_meta_key) ) > ML_MAX_METALEN ) wp_die( ML_METAKEY_OVERFLOW );
			if ( ! current_user_can( 'edit_post', $p_post_id ) ||
					is_protected_meta( $p_meta_key, 'post' ) ||
					! current_user_can( 'edit_post_meta', $p_post_id, $p_meta_key ) ) {
				wp_die(-1);
			}
			// Use 'update_post_meta' in case the meta_key exists ('add_post_meta' will be called if not).
			$ret = update_post_meta( $p_post_id, ml_postmeta_name($p_meta_key, $_POST['locale']), $_POST['meta_value'] );
			/* Before WP 4.5, when calling 'update_post_meta', '$meta_key' will be double 'wp_unslash'ed if 'add_metadata' is needed,
			   see "meta.php#update_metadata". Because special character is very rare for '$meta_key', this WP bug is not handled. */
			if ( $ret === false ) wp_die( __('Failure', ML_TDOMAIN) );
			wp_die();
		}
		wp_die( __('Invalid Parameters', ML_TDOMAIN) );
	}
	else if ( isset($_POST['delete-empty']) && $_POST['delete-empty'] === 'true' ) {
		wp_ajax_delete_meta(); // wp-admin/includes/ajax-actions.php
	}
	else {
		wp_ajax_add_meta();
	}

	remove_filter( 'is_protected_meta', 'morelang\ml_is_protected_meta' );
}


/* Invoked by: 1."Delete"(Ajax) a single meta group */
add_action( 'deleted_post_meta', 'morelang\ml_deleted_post_meta', 10, 4 );
function ml_deleted_post_meta( $meta_id, $object_id, $meta_key, $meta_value ) {
	if ( ml_skip_meta( $meta_key ) ) return;
	global $ml_registered_mlocales;
	$meta_key = wp_slash( $meta_key );
	foreach ( $ml_registered_mlocales as $mlocale ) {
		$ml_meta_key = ml_postmeta_name( $meta_key, $mlocale );
		if ( strlen($ml_meta_key) > 256 ) return;
		delete_post_meta( $object_id, $ml_meta_key );
	}
}

/* Invoked by 1."Publish" or "Update" the whole post; 2."Update"(Ajax) a single meta group(if the default part changed) */
/* 'updated_postmeta' is not used, because '$wpdb->update(...)' will return false if no real update, see "wp-includes/meta.php" */
add_action( 'update_post_meta' , 'morelang\ml_update_post_meta', 10, 4 );
function ml_update_post_meta( $meta_id, $object_id, $meta_key, $_meta_value ) {
	global $ml_registered_mlocales;
	if ( ml_skip_meta( $meta_key ) ) return;
	if ( strlen($meta_key) > ML_MAX_METALEN ) return;
	foreach ( $ml_registered_mlocales as $mlocale ) {
		$meta_arr = 'morelang_' . $mlocale . '_meta';
		if ( isset( $_POST[$meta_arr][$meta_id] ) ) {
			$locale_meta = $_POST[$meta_arr][$meta_id];
			$lmkey = ml_postmeta_name( $meta_key, $mlocale );
			if ( ! isset( $locale_meta['value'] ) || ! is_string( $locale_meta['value'] ) ) continue;
			$value = $locale_meta['value'];
			if ( strlen( $value ) > 0 ) {
				if ( isset( $locale_meta['mid'] ) ) {
					if ( is_numeric( $locale_meta['mid'] ) )
						update_metadata_by_mid( 'post', (int) $locale_meta['mid'], wp_unslash( $value ), $lmkey );
				}
				else { // e.g., handling a post meta which was existing before activating More-Lang
					update_post_meta( $object_id, wp_slash( $lmkey ), $value );
				}
			}
			else { // empty
				if ( isset( $locale_meta['mid'] ) && is_numeric( $locale_meta['mid'] ) ) {
					delete_metadata_by_mid( 'post', (int) $locale_meta['mid'] );
				}
			}
		}
	}
}
