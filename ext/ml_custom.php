<?php
namespace morelang;
/* The localization of the custom meta fields in a post */


/* Get localized post metas for a given meta */
add_action( 'wp_ajax_ml-get-meta', 'morelang\ml_get_meta' );
function ml_get_meta() {
	global $ml_registered_mlocales, $wpdb;
	$ret_obj = new \stdClass();
	if ( !empty($_POST['post_id']) && !empty($_POST['metaname']) ) {
		foreach ( $ml_registered_mlocales as $mlocale ) {
			$meta_key = ml_postmeta_name( $_POST['metaname'], $mlocale );
			$meta_val = get_post_meta( $_POST['post_id'], $meta_key, true );
			$mid = $wpdb->get_var( $wpdb->prepare("SELECT meta_id FROM $wpdb->postmeta
					WHERE post_id = %d AND meta_key = %s", $_POST['post_id'], $meta_key) );
			$ret_obj->$mlocale = array('value' => $meta_val, 'meta_id' => $mid);
		}
	}
	echo json_encode( $ret_obj );
	wp_die(); // this is required to terminate immediately and return a proper response
}

/* Delete localized post meta */
add_action( 'wp_ajax_ml-del-meta', 'morelang\ml_del_meta' );
function ml_del_meta() {
	global $ml_registered_mlocales;
	if ( !empty($_POST['meta_id']) && !empty($_POST['post_id']) && !empty($_POST['metaname']) ) {
		$postId = $_POST['post_id'];
		check_ajax_referer( 'delete-meta_' . $_POST['meta_id'] ); // can be overridden by plugins
		foreach ( $ml_registered_mlocales as $mlocale ) {
			$meta_key = ml_postmeta_name( $_POST['metaname'], $mlocale );
			delete_post_meta( $postId, $meta_key );
		}
	}
	else {
		wp_die("(ml-del-meta) Incorrect parameters");
	}
	echo "(ml-del-meta) Success!";
	wp_die();
}

/* Add or update localized post meta */
add_action( 'wp_ajax_ml-add-update-meta', 'morelang\ml_add_update_meta' );
function ml_add_update_meta() {
	if ( empty( $_POST['_ajax_nonce-add-meta'] ) || !wp_verify_nonce( $_POST['_ajax_nonce-add-meta'], 'add-meta')) {
		wp_die("No naughty business please");
	}
	if ( !empty($_POST['post_id']) && !empty($_POST['locale']) && !empty($_POST['metaname']) ) {
		$meta_key = ml_postmeta_name( $_POST['metaname'], $_POST['locale'] );
		if ( ! empty($_POST['metavalue']) ) {
			if ( ! empty($_POST['meta_id']) ) {
				update_metadata_by_mid( 'post', $_POST['meta_id'], $_POST['metavalue'], $meta_key );
			}
			else {
				/* we are unable to know the default meta_id when "Add New Custom Field", so we don't support array value, otherwise we need to change the design */
				update_post_meta( $_POST['post_id'], $meta_key, $_POST['metavalue'] );
			}
		}
		else {
			if ( ! empty($_POST['meta_id']) ) {
				delete_metadata_by_mid( 'post', $_POST['meta_id'] );
			}
			else { // only the "Add Custom Field" button will reach here.
			}
		}
	}
	else {
		wp_die("(ml-add-update-meta) Incorrect parameters");
	}
	wp_die();
}

/* Invoked by "Publish" or "Update" the whole post */
add_action( 'save_post', 'morelang\ml_add_postmeta', 10, 3); // "/wp-includes/post.php"
function ml_add_postmeta($post_ID, $post, $update = TRUE) {
	/* The following was modelled on "/wp-admin/includes/post.php" */
	$metakeyselect = isset($_POST['metakeyselect']) ? wp_unslash( trim( $_POST['metakeyselect'] ) ) : '';
	$metakeyinput = isset($_POST['metakeyinput']) ? wp_unslash( trim( $_POST['metakeyinput'] ) ) : '';
	$metavalue = isset($_POST['metavalue']) ? $_POST['metavalue'] : '';
	if ( is_string( $metavalue ) )
		$metavalue = trim( $metavalue );
	if ( ('0' === $metavalue || ! empty ( $metavalue ) ) && ( ( ( '#NONE#' != $metakeyselect ) && !empty ( $metakeyselect) ) || !empty ( $metakeyinput ) ) ) {
 		if ( '#NONE#' != $metakeyselect )
			$metakey = $metakeyselect;
		if ( $metakeyinput )
			$metakey = $metakeyinput; // default
		if ( is_protected_meta( $metakey, 'post' ) || ! current_user_can( 'add_post_meta', $post_ID, $metakey ) )
			return;
		$metakey = wp_slash( $metakey );
		ml_add_postmeta_impl( $post_ID, $metakey );
	}
}
/* Handling new meta inputted in the "Add New Custom Field" section */
function ml_add_postmeta_impl( $post_ID, $metakey ) {
	global $ml_registered_mlocales;
	foreach ( $ml_registered_mlocales as $mlocale ) {
		$lmkey = ml_postmeta_name( $metakey, $mlocale );
		$val_name = 'morelang_' . $mlocale . '_metavalue';
		if ( ! empty( $_POST[$val_name] ) ) {
			add_post_meta( $post_ID, $lmkey, $_POST[$val_name] );
		}
	}
}

/*  Invoked by "Publish" or "Update" the whole post */
add_action( 'update_postmeta' , 'morelang\ml_update_postmeta', 10, 4 );
function ml_update_postmeta( $meta_id, $object_id, $meta_key, $_meta_value ) {
	global $ml_registered_mlocales;
	if ($meta_key === '_edit_lock') return;
	foreach ( $ml_registered_mlocales as $mlocale ) {
		$meta_arr = 'morelang_' . $mlocale . '_meta';
		if ( ! empty( $_POST[$meta_arr][$meta_id] ) ) {
			$locale_meta = $_POST[$meta_arr][$meta_id];
			$lmkey = ml_postmeta_name( $meta_key, $mlocale );
			if ( ! empty( $locale_meta['value'] ) ) {
				$value = $locale_meta['value'];
				if ( ! empty( $locale_meta['mid'] ) ) {
					update_metadata_by_mid( 'post', $locale_meta['mid'], $value, $lmkey );
				}
				else { // e.g., handling a post meta which was existing before activating More-Lang
					update_post_meta( $object_id, $lmkey, $value );
				}
			}
			else {
				if ( ! empty( $locale_meta['mid'] ) ) {
					delete_metadata_by_mid( 'post', $locale_meta['mid'] );
				}
				else { // e.g., handling a post meta which was existing before activating More-Lang
					delete_post_meta( $object_id, $lmkey );
				}
			}
		}
	}
}
