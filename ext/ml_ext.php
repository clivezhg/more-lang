<?php
namespace morelang;


add_action('admin_enqueue_scripts', 'morelang\morelang_ext_scripts', 10);
function morelang_ext_scripts() {
	$cur_plugin_url = plugin_dir_url( __FILE__ );

	wp_enqueue_style( 'morelang_style_ext', $cur_plugin_url . 'morelang_ext.css', array(), ML_VER );
	wp_enqueue_script( 'morelang_js_ext', $cur_plugin_url . 'morelang_ext.js', array('morelang_js'), ML_VER, true );
	wp_enqueue_script( 'morelang_js_custom', $cur_plugin_url . 'morelang_custom.js', array('morelang_js'), ML_VER, true );
}


include_once 'ml_custom.php';

@include_once 'pro/ml_pro.php';
