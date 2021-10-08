<?php
namespace morelang;
/* The management of posts. */

/* Includes the management of Wordpress related options, taxonomy-terms and navigation menus. */
require_once 'ml_wp_option.php';
require_once 'ml_term.php';
require_once 'ml_menu.php';

/* Includes the generation of Post inputs */
add_action( 'current_screen', function () {
	if ( ! apply_filters('morelang_disable_postinp', FALSE) ) { // Can be disabled
		require_once 'ml_editor_postinp.php';
	}
} );


/* Adds RTL class if the default language is RTL */
add_filter( 'admin_body_class', function( $body_classes  ) {
	global $ml_registered_langs;
	/* In the Wordpress 4.6- versions, 'is_rtl()' is not available in the 'plugins_loaded' action stage */
	if ( is_rtl() ) return;
	if ( isset($ml_registered_langs[0]->moreOpt->isRTL) && $ml_registered_langs[0]->moreOpt->isRTL === TRUE ) {
		$body_classes = $body_classes . ($body_classes ? ' ':'') . 'ml-rtl-lang';
	}
	return $body_classes;
} );


/* Reference:"wp-includes/post.php#wp_insert_post(...)" */
function ml_encode_emoji( $text, $fld_name ) {
	global $wpdb;
	if ( ! method_exists($wpdb, 'get_col_charset') || ! function_exists('wp_encode_emoji') ) {
		return $text; // Introduced in WP 4.2.0
	}
	$charset = $wpdb->get_col_charset( $wpdb->posts, $fld_name );
	if ( 'utf8' === $charset ) return wp_encode_emoji( $text );
	return $text;
}

/* Saves the localized fields when saving post.
   The older versions only pass 2 parameters: $post_ID, $post */
add_action( 'save_post', 'morelang\ml_save_post', 10, 3 ); // "/wp-includes/post.php"
/* If a revision will be created, the function must run before 'post_updated', and 'edit_post' is suitable */
add_action( 'edit_post', 'morelang\ml_save_post', 10, 2 );
function ml_save_post($post_ID, $post, $update = TRUE) {
	global $ml_registered_mlocales;
	if ( defined('ML_POST_SAVED') && ML_POST_SAVED ) return;
	define('ML_POST_SAVED', TRUE);

	if ( ! is_int( $post_ID ) ) return;

	if (! current_user_can('edit_post', $post_ID)) return;

	if ( ! empty($_POST['data']['wp_autosave']) ) {
		return;
	}
	if ( isset( $_POST['autosave'] ) && $_POST['autosave'] === 'true' ) { // the olders
		return;
	}

	foreach ( $ml_registered_mlocales as $mlocale ) {
		/* if the submitted value is empty, remove the record to reduce table size */
		if ( isset( $_POST["post_title_$mlocale"] ) ) {
			$p_title = $_POST["post_title_$mlocale"];
			if ( ! is_string( $p_title ) ) return; // Abnormal, most likely to be hacked.
			if ( $p_title === '' ) {
				delete_post_meta( $post_ID, ml_postmeta_name('post_title', $mlocale) );
			}
			else {
				$p_title = sanitize_post_field( 'post_title', $p_title, $post_ID, 'db' ); // wp-includes/kses.php#kses_init()
				$p_title = ml_encode_emoji( $p_title, 'post_title' );
				update_post_meta( $post_ID, ml_postmeta_name('post_title', $mlocale), $p_title );
			}
		}
		if ( isset( $_POST["content_$mlocale"] ) ) {
			$p_content = $_POST["content_$mlocale"];
			if ( ! is_string( $p_content ) ) return;
			if ( $p_content === '' ) {
				delete_post_meta( $post_ID, ml_postmeta_name('post_content', $mlocale) );
			}
			else {
				$p_content = sanitize_post_field( 'post_content', $p_content, $post_ID, 'db' );
				$p_content = ml_encode_emoji( $p_content, 'post_content' );
				update_post_meta( $post_ID, ml_postmeta_name('post_content', $mlocale), $p_content );
			}
		}
		if ( isset( $_POST["excerpt_$mlocale"] ) ) {
			$p_excerpt = $_POST["excerpt_$mlocale"];
			if ( ! is_string( $p_excerpt ) ) return;
			if ( $p_excerpt === '' ) {
				delete_post_meta( $post_ID, ml_postmeta_name('post_excerpt', $mlocale) );
			}
			else {
				$p_excerpt = sanitize_post_field( 'post_excerpt', $p_excerpt, $post_ID, 'db' );
				$p_excerpt = ml_encode_emoji( $p_excerpt, 'post_excerpt' );
				update_post_meta( $post_ID, ml_postmeta_name('post_excerpt', $mlocale), $p_excerpt );
			}
		}
	}
}


$GLOBALS['ml_revision_flds'] = ['post_title', 'post_content', 'post_excerpt'];


/* Filters the localized fields displayed in the post revision diff UI. */
add_filter('wp_get_revision_ui_diff', 'morelang\ml_get_revision_ui_diff', 10, 3);
function ml_get_revision_ui_diff( $return, $compare_from, $compare_to ) {
	global $ml_registered_mlangs, $ml_revision_flds;
	if ( ! isset($compare_from->ID) || ! isset($compare_to->ID) ) return $return;
	/* Reference: includes/revision.php#wp_get_revision_ui_diff(...) */
	$flds = array_combine( $ml_revision_flds, [__( 'Title' ), __( 'Content' ), __( 'Excerpt' )] );
	foreach ( $flds as $fld=>$fld_name ) {
		$args = array( 'show_split_view' => true );
		$args = apply_filters( 'revision_text_diff_options', $args, $fld, $compare_from, $compare_to );
		foreach ( $ml_registered_mlangs as $mlang ) {
			$ml_meta_name = ml_postmeta_name( $fld, $mlang->locale );
			$from_meta_value = get_post_meta( $compare_from->ID, $ml_meta_name, TRUE );
			$to_meta_value = get_post_meta( $compare_to->ID, $ml_meta_name, TRUE );
			if ( $from_meta_value !== $to_meta_value ) {
				$diff = wp_text_diff( $from_meta_value, $to_meta_value, $args );
				$return[] = Array( 'id'=>$ml_meta_name, 'name'=>"$fld_name ($mlang->name)", 'diff'=>$diff );
			}
		}
	}
	return $return;
}


/* Filters whether a post has changed by checking localized fields */
add_filter('wp_save_post_revision_post_has_changed', 'morelang\ml_save_post_revision_post_has_changed', 10, 3);
function ml_save_post_revision_post_has_changed( $post_has_changed, $last_revision, $post ) {
	global $ml_registered_mlocales, $ml_revision_flds;
	if ( ! isset($last_revision->ID, $post->ID) ) return $post_has_changed;
	foreach ( $ml_revision_flds as $fld ) {
		foreach ( $ml_registered_mlocales as $mlocale ) {
			$ml_meta_name = ml_postmeta_name($fld, $mlocale);
			$last_meta_value = get_post_meta($last_revision->ID, $ml_meta_name, TRUE );
			$cur_meta_value = get_post_meta($post->ID, $ml_meta_name, TRUE );
			if ( $last_meta_value !== $cur_meta_value ) return TRUE;
		}
	}
	return $post_has_changed;
}


/* Saves lozalized revision fields after a revision has been saved. */
add_action('_wp_put_post_revision', 'morelang\ml_put_post_revision', 10, 1);
function ml_put_post_revision( $revision_id ) {
	global $ml_registered_mlocales, $ml_revision_flds;
	if ( ! is_int($revision_id) ) return;
	if ( wp_is_post_autosave($revision_id) ) return; // Avoid overwriting when creating new autosave record.
	$r_post = get_post($revision_id);
	if ( ! is_int($r_post->post_parent) ) return;
	$pid = $r_post->post_parent;
	if (! current_user_can('edit_post', $pid)) return;

	foreach ( $ml_revision_flds as $fld ) {
		foreach ( $ml_registered_mlocales as $mlocale ) {
			$ml_meta_name = ml_postmeta_name($fld, $mlocale);
			$cur_meta_value = get_post_meta($pid, $ml_meta_name, TRUE );
			if ( $cur_meta_value === '' ) continue;
			$cur_meta_value = wp_slash($cur_meta_value); // since data is from db // See wp-includes/revision.php#_wp_put_post_revision(...)
			update_metadata( 'post', $revision_id, $ml_meta_name, $cur_meta_value );
		}
	}
}


/* Restores localized revision fields after a post revision has been restored. */
add_action('wp_restore_post_revision', 'morelang\ml_restore_post_revision', 10, 2);
function ml_restore_post_revision( $post_id, $revision_id ) {
	global $ml_registered_mlocales, $ml_revision_flds;
	if ( ! is_int($post_id) || ! is_int($revision_id) ) return;
	if (! current_user_can('edit_post', $post_id)) return;

	foreach ( $ml_revision_flds as $fld ) {
		foreach ( $ml_registered_mlocales as $mlocale ) {
			$ml_meta_name = ml_postmeta_name($fld, $mlocale);
			$r_meta_value = get_post_meta($revision_id, $ml_meta_name, TRUE );
			if ( $r_meta_value === '' ) {
				delete_post_meta( $post_id, $ml_meta_name );
			}
			else {
				$r_meta_value = wp_slash($r_meta_value); // since data is from db
				update_post_meta( $post_id, $ml_meta_name, $r_meta_value );
			}
		}
	}
}
