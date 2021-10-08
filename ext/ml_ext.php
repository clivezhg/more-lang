<?php
namespace morelang;


add_action('admin_enqueue_scripts', 'morelang\morelang_ext_scripts', 10, 1);
function morelang_ext_scripts( $hook_suffix ) {
	$cur_plugin_url = plugin_dir_url( __FILE__ );
	wp_enqueue_style( 'morelang_style_ext', $cur_plugin_url . 'assets/morelang_ext.css', array(), ML_VER );
	if ( in_array( $hook_suffix, ['post.php', 'post-new.php'] ) ) {
		wp_enqueue_script( 'morelang_js_ext', $cur_plugin_url . 'assets/morelang_ext.js', array('morelang_js'), ML_VER, true );
	}

	wp_enqueue_script( 'morelang_js_media', $cur_plugin_url . 'assets/morelang_media.js', array(), ML_VER, true );

	/* Replaces the official "autosave.js" to handle the localized fields. */
	$wp_ver = get_bloginfo('version');
	if ( version_compare( $wp_ver, '4.6', '>=' ) && wp_script_is( 'autosave' ) ) { // The Wordpress releases before 4.6 are'nt supported.
		wp_dequeue_script('autosave');
		wp_deregister_script('autosave');

		$ml_autosave_js = 'autosave-morelang-49.js'; // Defaults to the latest.
		if ( version_compare( $wp_ver, '4.7', '<' ) ) $ml_autosave_js = 'autosave-morelang-46.js';
		else if ( version_compare( $wp_ver, '4.9', '<' ) ) $ml_autosave_js = 'autosave-morelang-47.js';

		wp_enqueue_script( 'morelang_js_autosave', $cur_plugin_url . 'autosave/' . $ml_autosave_js, array('morelang_js'), ML_VER, true );
		/* Reference: wp-includes/script-loader.php#wp_just_in_time_script_localization(...) */
		wp_localize_script( 'morelang_js_autosave', 'autosaveL10n',
				array('autosaveInterval' => AUTOSAVE_INTERVAL, 'blog_id' => get_current_blog_id()) );
	}
}


/* Autosave localized title, content, excerpt */
/* Need to hack "/wp-includes/js/autosave.js" to enable this feature. */
add_action( 'save_post', 'morelang\ml_save_post_autosave', 10, 3); // "/wp-includes/post.php"
function ml_save_post_autosave( $ID, $post, $update = TRUE ) {
	global $ml_registered_mlocales;

	if ( ! is_int( $ID ) ) return;
	
	if (! current_user_can('edit_post', $ID)) return;

	$autosave_data = NULL;
	if ( ! empty($_POST['data']['wp_autosave']) ) {
		$autosave_data = $_POST['data']['wp_autosave'];
	}
	if ( isset( $_POST['autosave'] ) && $_POST['autosave'] === 'true' ) { // the olders
		$autosave_data = $_POST;
	}
	if ( $autosave_data === NULL ) return;

	$ml_create_single_autosave_fld = function($locale, $fld) use($ID, $autosave_data) {
		if ( isset($autosave_data["${fld}_$locale"]) ) {
			$p_text = $autosave_data["${fld}_$locale"];
			if ( ! is_string( $p_text ) || $p_text === '' ) return;
			$p_text = sanitize_post_field( "post_${fld}", $p_text, $ID, 'db' );
			$p_text = ml_encode_emoji( $p_text, "post_${fld}" );
			// update_post_meta(...);  // if 'update_post_meta' is called on a revision, the meta will be updated to the revision's parent.
			update_metadata( 'post', $ID, ml_postmeta_name("post_$fld", $locale), $p_text );
		}
	};

	foreach ( $ml_registered_mlocales as $mlocale ) {
		$ml_create_single_autosave_fld($mlocale, 'title');
		$ml_create_single_autosave_fld($mlocale, 'content');
		$ml_create_single_autosave_fld($mlocale, 'excerpt');
	}
}


/* When opening a post for editing, Wordpress will try to remove outdated autosave.
   And because Wordpress doesn't provide a filter to avoid the deletion,
    we have to hook into the enclosing filter&action (in WP4.6~4.9.X, 'post_updated_messages' is called only once),
    but the '$notice' message could not be given.
   See the 'wp_delete_post_revision( $autosave->ID );' section in "wp-admin/edit-form-advanced.php" for details. */
add_filter( 'post_updated_messages', 'morelang\ml_autosave_post_updated_messages', 10, 1 );
function ml_autosave_post_updated_messages( $messages ) {
	add_filter( 'pre_delete_post', 'morelang\ml_autosave_pre_delete_post', 10, 3 );
}
function ml_autosave_pre_delete_post( $delete, $post, $force_delete ) {
	global $ml_registered_mlocales, $ml_revision_flds;
	if ( ! isset($post->ID) || ! isset($post->post_parent) ) return $delete;
	if ( ! wp_is_post_autosave($post->ID) ) return $delete;
	foreach ( $ml_revision_flds as $fld ) {
		foreach ( $ml_registered_mlocales as $mlocale ) {
			$ml_meta_name = ml_postmeta_name($fld, $mlocale);
			$r_meta_value = get_post_meta($post->ID, $ml_meta_name, TRUE);
			$p_meta_value = get_post_meta($post->post_parent, $ml_meta_name, TRUE);
			if ( normalize_whitespace($r_meta_value) !== normalize_whitespace($p_meta_value) ) {
				return FALSE;
			}
		}
	}
	return $delete;
}
add_action( 'add_meta_boxes', 'morelang\ml_autosave_add_meta_boxes', 10, 2 );
function ml_autosave_add_meta_boxes( $post_type, $post ) {
	remove_filter( 'pre_delete_post', 'morelang\ml_autosave_pre_delete_post' );
}


/* When autosaving, if an autosave already exists, Wordpress will delete the autosave if it's the same as the parent post.
   And because Wordpress doesn't provide a filter to avoid the deletion,
    we utilize the 'post_content_filtered' field (set in the "*.js"), which will only be filled in autosave Post,
    so the deletion will never happen (which is different from the default behavior, but the deletion looks unnecessary)
   See the 'wp_delete_post_revision( $old_autosave->ID );' section in "wp-admin/includes/post.php" */
add_filter( '_wp_post_revision_fields', 'morelang\ml_post_revision_fields', 10, 2 );
function ml_post_revision_fields( $fields, $post = NULL ) { // No `$post` parameter before WP 4.5.0
	if ( ! is_array($fields) ) return $fields;
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
		$fields['post_content_filtered'] = 'ML_Content_Filtered';
	}
	return $fields;
}


/* When autosaving a "draft" Post, it will save directly to the Post itself. */
add_filter( 'wp_insert_post_data', 'morelang\ml_insert_post_data', 10, 2 );
function ml_insert_post_data( $data, $postarr ) {
	if ( isset($data['post_status'], $data['post_content_filtered']) ) {
		if ( $data['post_content_filtered'] === 'morelang_avoid_unexpected_autosave_deletion' ) {
			if ( $data['post_status'] === 'draft' ) {
				$data['post_content_filtered'] = '';
			}
		}
	}
	return $data;
}


/* The translating menu */
if ( current_user_can( 'manage_options' ) ) {
	add_action( 'admin_menu', function () {
		require_once 'ml_translate.php';
		add_submenu_page( ML_OPT_MENUSLUG, __('More-Lang Translations', ML_TDOMAIN), __('Translations', ML_TDOMAIN),
				'manage_options', ML_TRANS_MENUSLUG, 'morelang\ml_trans_page' );
	} );
}


include_once 'ml_custom.php';

include_once 'ml_media.php';

include_once 'ml_lang_col.php';
