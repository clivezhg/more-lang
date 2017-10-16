<?php
namespace morelang;

/* More-Lang configuration UI.
 */
if ( is_admin() ) {
	ml_fetch_option();

	add_action('admin_menu', 'morelang\ml_add_menu');
	function ml_add_menu() { // More-Lang Plugin Settings
		require_once 'ml_option.php';
		add_menu_page('More-Lang plugin', 'More-Lang', 'administrator', __FILE__, 'morelang\morelang_options_page',
				'dashicons-groups'); // https://developer.wordpress.org/resource/dashicons
	}

	add_action( 'admin_print_scripts', 'morelang\ml_admin_jsdata' );
	function ml_admin_jsdata(){
		echo "<script type='text/javascript'>";
		echo 'var ml_opt_obj=' . json_encode( $GLOBALS['ml_opt_obj'] ) . ';';
		echo ' var ml_registered_langs=ml_opt_obj ? ml_opt_obj.ml_registered_langs || [] : [];';
		echo ' var ml_dft_locale=' . json_encode(get_locale()) . ';'; // too early to get locale?
		echo ' var ml_wp_vernum=' . ml_calc_wpvernum() . ';';
		$opt_bloginfo = new \stdClass();
		global $ml_registered_mlocales;
		foreach ( $ml_registered_mlocales as $mlocale ) {
			$opt_name1 = 'morelang_blogname_' . $mlocale;
			$opt_bloginfo->$opt_name1 = get_option( $opt_name1 );
			$opt_name2 = 'morelang_blogdescription_' . $mlocale;
			$opt_bloginfo->$opt_name2 = get_option( $opt_name2 );
		}
		echo 'var ml_opt_bloginfo=' . json_encode($opt_bloginfo) . ';';
		echo "</script>";
	}

	add_action( 'admin_print_scripts', 'morelang\ml_admin_jsfunc' );
	function ml_admin_jsfunc(){
?>
	<script type='text/javascript'>
		function mlChangeInputPos(dft_id, id_pfx) {
			jQuery(document).ready(function() {
				if (window.mlChangeInputPosImpl) {
					try {
						mlChangeInputPosImpl(dft_id, id_pfx);
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

	require_once 'ml_widget.php';
}
