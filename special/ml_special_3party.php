<?php
namespace morelang;
/* The entry file of compatibility with Plugins & Themes in some special cases */


define('ML_SPECIAL_DELIMITER_PIPE', '0');
define('ML_SPECIAL_MENUSLUG', 'morelang-special-setting');


if ( ! is_admin() ) {
	ml_special_front_actions(); // It is required for the default language
}
else {
	add_action('morelang_admin_loaded', 'morelang\ml_special_admin_actions');
}


function ml_special_front_actions() {
	$special_opt = ml_get_special_opt();
	$has_special = FALSE;

	if ( isset($special_opt->ml_special_menu) && $special_opt->ml_special_menu === TRUE ) {
		require_once 'ml_special_menu.php';
		$has_special = TRUE;
	}

	if ( isset($special_opt->ml_special_widget) && $special_opt->ml_special_widget === TRUE ) {
		require_once 'ml_special_widget.php';
		$has_special = TRUE;
	}

	if ( $has_special ) {
		if ( ! isset($special_opt->ml_special_delimiter)
				|| $special_opt->ml_special_delimiter === ML_SPECIAL_DELIMITER_PIPE ) {
			require_once 'ml_special_parser_pipe.php';
		}
	}

	if ( ml_get_locale() ) {
		if ( isset($special_opt->ml_special_filter_links) && $special_opt->ml_special_filter_links ) {
			require_once 'ml_special_link.php';
		}
	}
}


function ml_special_admin_actions() {
	/* Add the "Special" menu item */
	if ( current_user_can( 'manage_options' ) ) {
		add_action( 'admin_menu', function () {
			require_once 'ml_special_option.php';
			add_submenu_page( ML_OPT_MENUSLUG, __('More-Lang Compatibility in Special Cases', ML_TDOMAIN), __('Special', ML_TDOMAIN),
					'manage_options', ML_SPECIAL_MENUSLUG, 'morelang\ml_special_option' );
		} );
	}
}


/**
 * Get the option object of compatibility with Plugins & Themes in some special cases
 */
function ml_get_special_opt() {
	if ( isset($GLOBALS['ml_special_opt']) ) return $GLOBALS['ml_special_opt'];
	$ml_ret_obj = new \stdClass();
	$ml_special = get_option( 'morelang_nml_special_option' ); // ML_UID may be unavailable
	if (! is_string($ml_special) || strlen($ml_special) > 10000) $ml_special = '';
	if ( $ml_special ) {
		$ml_special_opt = json_decode( $ml_special );
		if ( is_object($ml_special_opt) ) $ml_ret_obj = $ml_special_opt;
		$GLOBALS['ml_special_opt'] = $ml_ret_obj;
	}
	return $ml_ret_obj;
}


$special_opt = ml_get_special_opt();
if ( isset($special_opt->ml_special_post_meta_keys) && is_array($special_opt->ml_special_post_meta_keys) ) {
	require_once 'ml_special_postmeta.php';
}
