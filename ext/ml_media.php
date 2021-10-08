<?php
namespace morelang;
/* The localization of the medias */


/* Save the localized Attachment infos */
add_action( 'wp_ajax_ml-save-attachment', 'morelang\ml_save_attachment' );
function ml_save_attachment() {
	global $ml_registered_mlocales;
	if (! isset( $_REQUEST['locale'] ) || ! in_array($_REQUEST['locale'], $ml_registered_mlocales)) {
		wp_send_json_error();
	}

	if ( ! isset( $_REQUEST['id'] ) || ! isset( $_REQUEST['changes'] ) ) {
		wp_send_json_error();
	}

	$id = absint( $_REQUEST['id'] );
	if ( ! $id ) {
		wp_send_json_error();
	}

	check_ajax_referer( 'update-post_' . $id, 'nonce' );

	if ( ! current_user_can( 'edit_post', $id ) ) {
		wp_send_json_error();
	}

	$changes = $_REQUEST['changes'];
	$post = get_post( $id, ARRAY_A );

	if ( 'attachment' != $post['post_type'] ) {
		wp_send_json_error();
	}

	if ( isset( $changes['alt'] ) ) {
		$alt_meta_name = ml_postmeta_name( '_wp_attachment_image_alt',  $_REQUEST['locale'] );
		$alt = wp_unslash( $changes['alt'] );
		if ( $alt != get_post_meta( $id, $alt_meta_name, TRUE ) ) {
			$alt = wp_strip_all_tags( $alt, TRUE );
			update_post_meta( $id, $alt_meta_name, wp_slash( $alt ) );
		}
	}

	/* For the fields whose default values are not saved in the meta table */
	$chg_non_meta = FALSE;
	if ( isset( $changes['title'] ) ) {
		$_POST["post_title_" . $_REQUEST['locale']] = $changes['title'];
		$chg_non_meta = TRUE;
	}
	if ( isset( $changes['caption'] ) ) {
		$_POST["excerpt_" . $_REQUEST['locale']] = $changes['caption'];
		$chg_non_meta = TRUE;
	}
	if ( isset( $changes['description'] ) ) {
		$_POST["content_" . $_REQUEST['locale']] = $changes['description'];
		$chg_non_meta = TRUE;
	}
	if ( $chg_non_meta ) {
		ml_save_post( $id, $post );
	}

	/* For non-image infos */
	if ( isset( $changes['artist'] ) ) {
		$artist_meta_name = ml_postmeta_name( '_wp_attachment_metadata_artist',  $_REQUEST['locale'] );
		$artist = wp_unslash( $changes['artist'] );
		if ( $artist != get_post_meta( $id, $artist_meta_name, TRUE ) ) {
			$artist = wp_strip_all_tags( $artist, TRUE );
			update_post_meta( $id, $artist_meta_name, wp_slash( $artist ) );
		}
	}
	if ( isset( $changes['album'] ) ) {
		$album_meta_name = ml_postmeta_name( '_wp_attachment_metadata_album',  $_REQUEST['locale'] );
		$album = wp_unslash( $changes['album'] );
		if ( $album != get_post_meta( $id, $album_meta_name, TRUE ) ) {
			$album = wp_strip_all_tags( $album, TRUE );
			update_post_meta( $id, $album_meta_name, wp_slash( $album ) );
		}
	}

	wp_send_json_success();
}


/* Get localized Attachment infos for the given post_id|post_ids */
add_action( 'wp_ajax_ml-get-attachment-metas', 'morelang\ml_get_attachment_metas' );
function ml_get_attachment_metas() {
	if ( ! current_user_can( 'upload_files' ) ) {
		wp_send_json_error();
	}

	if ( isset( $_POST['post_id'] ) && ml_validate_str( $_POST['post_id'] ) ) {
		$ret = new \stdClass();
		$post_id = (int) $_POST['post_id'];
		if ( $post_id < 1 ) wp_send_json_error();
		$ret = ml_get_attachment_metas_byid( $post_id );
		wp_send_json_success( $ret );
	}
	else if ( isset( $_POST['post_ids'] ) && is_array( $_POST['post_ids'] ) ) {
		$ret = array();
		foreach ($_POST['post_ids'] as $p_post_id) {
			$post_id = (int) $p_post_id;
			if ( $post_id < 1 ) wp_send_json_error();
			$ret[$post_id] = ml_get_attachment_metas_byid( $post_id );
		}
		wp_send_json_success( $ret );
	}
}


/**
 * Get localized Attachment infos by post_id /
 * @param int $post_id
 * @return object
 */
function ml_get_attachment_metas_byid( $post_id ) {
	global $ml_registered_mlocales;

	$ret_obj = new \stdClass();

	foreach ( $ml_registered_mlocales as $mlocale ) {
		$ret_obj->$mlocale = [];

		$alt_meta = get_post_meta( $post_id, ml_postmeta_name('_wp_attachment_image_alt', $mlocale), TRUE );
		if ( $alt_meta ) {
			$ret_obj->$mlocale['alt'] = $alt_meta;
		}

		$title_meta = get_post_meta( $post_id, ml_postmeta_name('post_title', $mlocale), TRUE );
		if ( ml_valid_text($title_meta) ) {
			$ret_obj->$mlocale['title'] = $title_meta;
		}

		$excerpt_meta = get_post_meta( $post_id, ml_postmeta_name('post_excerpt', $mlocale), TRUE );
		if ( ml_valid_text($excerpt_meta) ) {
			$ret_obj->$mlocale['caption'] = $excerpt_meta;
		}

		$ml_content = get_post_meta( $post_id, ml_postmeta_name('post_content', $mlocale), TRUE );
		if ( ml_valid_text($ml_content) ) {
			$ret_obj->$mlocale['description'] = $ml_content;
		}

		$artist_meta = get_post_meta( $post_id, ml_postmeta_name('_wp_attachment_metadata_artist', $mlocale), TRUE );
		if ( $artist_meta ) {
			$ret_obj->$mlocale['artist'] = $artist_meta;
		}
		$album_meta = get_post_meta( $post_id, ml_postmeta_name('_wp_attachment_metadata_album', $mlocale), TRUE );
		if ( $album_meta ) {
			$ret_obj->$mlocale['album'] = $album_meta;
		}
	}

	return $ret_obj;
}
