<?php
namespace morelang;

/**
 * Plugin Name: More-Lang
 * Text Domain: more-lang
 * Plugin URI: https://wordpress.org/plugins/more-lang/
 * Description: A multilingual support plugin for Wordpress
 * Version: 2.5.9
 * Author: Clive Zheng
 * Author URI: https://www.morelang.com
 * License: GPLv2 or later
 */
define('ML_VER', '2.5.9');
define('ML_UID', 'morelang_nml_'); // be unique to avoid name collision
define('ML_TDOMAIN', 'more-lang'); // Text Domain

defined('ABSPATH') or die("No script kiddies please!");

define('ML_DIR_NAME', basename(dirname(__FILE__)));
define('ML_PLUGIN_URL', plugin_dir_url( __FILE__ ));

define('ML_URLMODE_PATH', '0');
define('ML_URLMODE_QRY', '1');
define('ML_POS_INPUTMODE_SEL', '0'); // the same as "morelang_cfg.js"
define('ML_POS_INPUTMODE_TEXT', '1');
define('ML_OPT_MENUSLUG', 'morelang-inc-setting');
define('ML_TRANS_MENUSLUG', 'morelang-ext-translation');

require_once 'inc/ml_pub.php';
require_once 'inc/ml_editor_block.php';

ml_fetch_option();

if ( ! is_admin() ) {
	ml_init_front();

	require_once 'inc/ml_url.php';
	require_once 'inc/ml_switcher.php';
	require_once 'inc/ml_text_all.php';

	if ( ml_get_locale() ) { // skip the default locale
		require_once 'inc/ml_text.php';
		include_once 'ext/ml_ext_text.php';
	}

	function ml_front_actions() {
		do_action( 'morelang_front_all_loaded' );
		if ( ! ml_get_locale() ) return;
		do_action( 'morelang_front_loaded' ); // If called directly, handlers registered later will not be called
	}
	add_action( 'plugins_loaded', 'morelang\ml_front_actions' );
}
else {
	/* Add config links to the Plugins page */
	function ml_settings_link($links) {
		$tran_link = '<a href="admin.php?page=' . ML_TRANS_MENUSLUG . '">' . __('Translations', ML_TDOMAIN) . '</a>';
		$setting_link = '<a href="admin.php?page=' . ML_OPT_MENUSLUG . '">' . __('Settings', ML_TDOMAIN) . '</a>';
		array_unshift( $links, $setting_link, $tran_link );
		return $links;
	}
	$plugin = plugin_basename(__FILE__);
	add_filter( "plugin_action_links_$plugin", 'morelang\ml_settings_link' );

	global $ml_opt_obj;
	if ( isset( $ml_opt_obj->ml_clear_when_delete_plugin ) && $ml_opt_obj->ml_clear_when_delete_plugin === true ) {
		register_uninstall_hook(__FILE__, 'morelang\ml_uninstall');
	}
	function ml_uninstall() { // to be scanned, should not be nested in a function
		global $wpdb;
		if ( current_user_can( 'manage_options' ) && current_user_can( 'delete_posts' ) ) {
			$wlike = str_replace('_', '#_', "'%" . ML_UID . "%'") . " ESCAPE '#'";
			$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE $wlike");
			$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE $wlike");
		}
	}

	add_action( 'plugins_loaded', 'morelang\ml_admin_actions' );
}

function ml_admin_actions() {
	if (! current_user_can('edit_posts') && ! current_user_can('edit_theme_options') && ! current_user_can('manage_categories')) {
		// the 'current_user_can(...)' is not runnable before "pluggable.php" is loaded (so put it in 'plugins_loaded')
		return;
	}

	load_plugin_textdomain( ML_TDOMAIN, false, dirname(plugin_basename( __FILE__ )) . '/languages' );

	require_once 'inc/ml_cfg.php';
	require_once 'inc/ml_editor.php';

	add_action('admin_enqueue_scripts', 'morelang\morelang_scripts', 10);
	function morelang_scripts() {
		$cur_plugin_url = plugin_dir_url( __FILE__ );

		wp_enqueue_style( 'morelang_style', $cur_plugin_url . 'css/morelang_admin.css', array(), ML_VER );
		wp_enqueue_style( 'morelang_style_int', $cur_plugin_url . 'ext/assets/morelang_int.css', array(), ML_VER );
		wp_enqueue_script( 'jquery-ui-tabs' );

		wp_enqueue_script( 'morelang_js', $cur_plugin_url . 'js/morelang.js', array(), ML_VER, true );
		wp_enqueue_script( 'morelang_js_repos', $cur_plugin_url . 'js/morelang_repos.js', array(), ML_VER, true );
		if ( ! apply_filters('morelang_disable_postinp', FALSE) ) {
			$screen = get_current_screen();
			if ( method_exists($screen, 'is_block_editor') && $screen->is_block_editor() ) {
				wp_enqueue_script( 'morelang_js_block', $cur_plugin_url . 'js/morelang_blocks.js', array(), ML_VER, true );
			}
		}
	}


	include_once 'ext/ml_ext.php';

	do_action('morelang_admin_loaded');
}


/* Deal with the compatibility with Plugins & Themes in some special cases (disabled by default) */
if ( isset( $ml_opt_obj->ml_enable_special_3party_compat ) && $ml_opt_obj->ml_enable_special_3party_compat === true ) {
	include_once 'special/ml_special_3party.php';
}
