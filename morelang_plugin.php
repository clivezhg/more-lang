<?php
namespace morelang;

/**
 * Plugin Name: More-Lang
 * Plugin URI: https://wordpress.org/plugins/more-lang
 * Description: Multilingual support
 * Version: 0.9.9
 * Author: Clive Zheng
 * Author URI: https://github.com/clivezhg
 * License:
 */
defined('ABSPATH') or die("No script kiddies please!");

define('ML_URLMODE_PATH', '0');
define('ML_URLMODE_QRY', '1');
define('ML_POS_INPUTMODE_SEL', '0'); // the same as "morelang_cfg.js"
define('ML_POS_INPUTMODE_TEXT', '1');

require_once 'inc/ml_pub.php';

if ( ! is_admin() ) {
	ml_init_front();

	require_once 'inc/ml_url.php';
	require_once 'inc/ml_switcher.php';

	require_once 'inc/ml_text_all.php';
	if ( ! ml_get_locale() ) {
		return; // skip the default locale
	}
	require_once 'inc/ml_text.php';
	include_once 'ext/ml_ext_text.php';
}
else {
	// Add Settings link on Plugins page
	function ml_settings_link($links) { 
		$settings_link = '<a href="admin.php?page=morelang%2Finc%2Fml_cfg.php">Settings</a>'; 
		array_unshift($links, $settings_link); 
		return $links; 
	}
	$plugin = plugin_basename(__FILE__); 
	add_filter( "plugin_action_links_$plugin", 'morelang\ml_settings_link' );

	add_action( 'plugins_loaded', 'morelang\ml_admin_actions' );
}

function ml_admin_actions() {
	if ( ! current_user_can('administrator') ) { // admin actions
		// the 'current_user_can(...)' is not runnable before "pluggable.php" is loaded (so put it in 'plugins_loaded')
		return;
	}
	ml_fetch_option();
	load_plugin_textdomain( 'morelang', false, dirname(plugin_basename( __FILE__ )) . '/languages' );

	require_once 'inc/ml_cfg.php';

	register_deactivation_hook(__FILE__, 'morelang\morelang_deactivation');
	function morelang_deactivation(){}

	require_once 'inc/ml_editor.php';

	add_action('admin_enqueue_scripts', 'morelang\morelang_scripts', 10);
	function morelang_scripts() {
		$cur_plugin_url = plugin_dir_url( __FILE__ );

		wp_enqueue_style( 'morelang_style', $cur_plugin_url . 'css/morelang_admin.css', array(), '1.0' );

		wp_enqueue_script( 'jquery-ui-tabs' );

		if (file_exists(plugin_dir_path(__FILE__) . 'jsmin/morelang.min.js')) {
			wp_enqueue_script( 'morelang_js_min', $cur_plugin_url . 'jsmin/morelang.min.js', array(), '1.0', true );
			return;
		}
		wp_enqueue_script( 'morelang_js', $cur_plugin_url . 'js/morelang.js', array(), '1.0', true );
		if (file_exists(plugin_dir_path(__FILE__) . 'js/morelang_cfg.js')) {
			wp_enqueue_script( 'morelang_js_cfg', $cur_plugin_url . 'js/morelang_cfg.js', array(), '1.0', true );
		}
		if (file_exists(plugin_dir_path(__FILE__) . 'js/morelang_repos.js')) {
			wp_enqueue_script( 'morelang_js_repos', $cur_plugin_url . 'js/morelang_repos.js', array(), '1.0', true );
		}
	}

	include_once 'ext/ml_ext.php';
}
