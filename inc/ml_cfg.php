<?php
namespace morelang;


/**
 * Enqueue the Select2, a JS select box library improving the select element.
 */
function ml_enqueue_select2() {
	wp_enqueue_style( 'ml_select2_style', ML_PLUGIN_URL . 'dep/select2.min.css', array(), '4.0.3' );
	wp_enqueue_script( 'ml_select2_js', ML_PLUGIN_URL . 'dep/select2.min.js', array(), '4.0.3' );
}


/* More-Lang configuration UI.
 */
if ( is_admin() ) {
	if ( current_user_can( 'manage_options' ) ) {
		add_action( 'admin_menu', 'morelang\ml_add_menu' );
	}
	function ml_add_menu() { // More-Lang Plugin Settings
		require_once 'ml_option.php';
		add_menu_page(__('More-Lang plugin', ML_TDOMAIN), 'More-Lang', 'manage_options', ML_OPT_MENUSLUG, 'morelang\ml_options_page',
				'dashicons-groups'); // https://developer.wordpress.org/resource/dashicons
		// If the first submenu doesn't link back to the parent, a link to the parent will be added as the first(with the same 'menu_title')
		add_submenu_page(ML_OPT_MENUSLUG, __('More-Lang plugin', ML_TDOMAIN), __('Settings', ML_TDOMAIN),
				'manage_options', ML_OPT_MENUSLUG);
	}

	add_action( 'admin_print_scripts', 'morelang\ml_admin_jsdata' );
	function ml_admin_jsdata(){
		echo "<script type='text/javascript'>";
		echo 'var ml_opt_obj=' . wp_json_encode( $GLOBALS['ml_opt_obj'] ) . ';';
		echo ' var ml_registered_langs=window.ml_opt_obj ? ml_opt_obj.ml_registered_langs || [] : [];';
		echo ' var ml_dft_locale=' . wp_json_encode( get_locale() ) . ';'; // i.e., the "Site Language"
		echo ' var ml_wp_vernum=' . ml_calc_wpvernum() . ';';
		echo ' var ml_uid="' . ML_UID . '";';
		echo ' var ml_plugin_url="' . plugin_dir_url( dirname(__FILE__) ) . '";';
		$opt_bloginfo = new \stdClass();
		global $ml_registered_mlocales;
		foreach ( $ml_registered_mlocales as $mlocale ) {
			$opt_name1 = ML_UID . 'blogname_' . $mlocale;
			$opt_bloginfo->$opt_name1 = get_option( $opt_name1 );
			$opt_name2 = ML_UID . 'blogdescription_' . $mlocale;
			$opt_bloginfo->$opt_name2 = get_option( $opt_name2 );
		}
		echo ' var ml_opt_bloginfo=' . wp_json_encode($opt_bloginfo) . ';';
		$ml_i18n_arr = ['upd_label' => __('Update', ML_TDOMAIN), 'tran_label' => __('Translation', ML_TDOMAIN)];
		$ml_i18n_arr['upd_title'] = __('Enable it by selecting a non-default language', ML_TDOMAIN);
		$ml_i18n_arr['pub_title'] = __('Enable it by selecting the default language', ML_TDOMAIN);
		$ml_i18n_arr['not_switch_autosave'] = __('Not switch when it is autosaving', ML_TDOMAIN);
		$ml_i18n_arr['more_btn_label'] = __('More...', ML_TDOMAIN);
		echo ' var ml_i18n_obj=' . wp_json_encode( $ml_i18n_arr ) . ';' . "\n";
		echo "</script>";
	}

	add_action( 'admin_print_scripts', 'morelang\ml_admin_jsfunc' );
	function ml_admin_jsfunc(){
?>
	<script type='text/javascript'>
		function mlChangeInputPos(dft_id, id_pfx, argObj) {
			jQuery(document).ready(function() {
				if (window.mlChangeInputPosImpl) {
					try {
						mlChangeInputPosImpl(dft_id, id_pfx, argObj);
					} catch (e) { // Excetions will cause subsequent callbacks of 'ready' to be skipped
						console.error('mlChangeInputPosImpl', e);
					}
				}
				else console.log("mlChangeInputPosImpl not ready...");
			});
		}
	</script>
<?php
	}

	add_action( 'admin_print_styles', 'morelang\ml_admin_print_styles' );
	function ml_admin_print_styles(){
		require_once dirname(__FILE__) . '/../css/morelang_admin_css.php';
	}


	/* The admin footer text. */
	function ml_admin_footer_text () {
		$pro_msg = '';
		if (! defined('ML_PRO_VER')) { // Show the Pro link if it is not installed
			$pro_msg = '<span class="ml-pro-link">';
			$pro_msg .= esc_html__('More features are available in ', ML_TDOMAIN);
			$pro_msg .= '<a href="https://www.morelang.com/" target="_blank">More-Lang Pro</a></span>';
		}
		return $pro_msg  . sprintf( esc_html__('If you like %s,', ML_TDOMAIN), '<strong>More-Lang</strong>' )
			. sprintf( esc_html__('please leave a %s rating.', ML_TDOMAIN),
					'<a href="https://wordpress.org/support/plugin/more-lang/reviews?rate=5#new-post" target="_blank">'
					. '&#9733;&#9733;&#9733;&#9733;&#9733;</a>' )
			. esc_html__('Thanks a lot in advance!', ML_TDOMAIN);
	}

	/* The admin footer version info. */
	function ml_update_footer() {
		return 'Version ' . ML_VER;
	};


	require_once 'ml_widget.php';
}
