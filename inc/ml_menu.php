<?php
namespace morelang;
/* The management of navigation menus. */


/* The editing & saving of navigation menus.
 */
require_once ABSPATH . '/wp-admin/includes/nav-menu.php';
class ML_Walker_Nav_Menu_Edit extends \Walker_Nav_Menu_Edit {
	function start_el(&$output, $item, $depth = 0, $args = Array(), $id = 0) {
		global $ml_registered_mlangs;
		parent::start_el($output, $item, $depth, $args, $id);
		$item_id = esc_attr( $item->ID );
		if ( ! in_array($item->type, array('post_type', 'post_type_archive', 'custom', 'ml_switcher_menu')) ) {
			return $output;
		}
		ob_start();
		foreach ( $ml_registered_mlangs as $mlang ):
			$memb_name = 'title_' . $mlang->locale;
			$cur_val = isset($item->$memb_name) ? $item->$memb_name : ''; // set in 'ml_setup_nav_menu_item(...)'
			$lang_name = ml_get_admin_lang_label( $mlang );
	?>
			<p class="description description-wide">
				<label for="edit-menu-item-title-<?php echo $item_id . '_' . $mlang->locale; ?>">
					<?php _e( 'Navigation Label' ); echo (" ($mlang->name)"); ?><br />
					<input type="text" id="edit-menu-item-title-<?php echo $item_id . '_' . $mlang->locale; ?>" class="widefat edit-menu-item-title"
							 name="menu-item-title_<?php echo $mlang->locale;?>[<?php echo $item_id; ?>]"
							 data-langname="<?php echo $lang_name ?>"
							 value="<?php echo wp_slash( esc_attr( $cur_val ) ); ?>" />
				</label>
			</p>
	<?php
		endforeach;
		$mi_inp_id = 'edit-menu-item-title-' . $item_id;
		echo '<script>mlChangeInputPos("' . $mi_inp_id . '", ' . '"' . $mi_inp_id . '_" );</script>';
		$output = preg_replace('/(.*\sid="edit-menu-item-title-' . $item_id . '".*<\/p>)(.*)/Us', '$1' . ob_get_clean() . '$2', $output);

		ob_start();
		foreach ( $ml_registered_mlangs as $mlang ):
			$memb_name = 'attr_title_' . $mlang->locale;
			$cur_val = isset($item->$memb_name) ? $item->$memb_name : '';
			$lang_name = ml_get_admin_lang_label( $mlang );
	?>
			<p class="field-title-attribute field-attr-title description description-wide">
				<label for="edit-menu-item-attr-title-<?php echo $item_id . '_' . $mlang->locale; ?>">
					<?php _e( 'Title Attribute' ); echo (" ($mlang->name)"); ?><br />
					<input type="text" id="edit-menu-item-attr-title-<?php echo $item_id . '_' . $mlang->locale; ?>" class="widefat edit-menu-item-attr-title"
							 name="menu-item-attr-title_<?php echo $mlang->locale;?>[<?php echo $item_id; ?>]"
							 data-langname="<?php echo $lang_name ?>"
							 value="<?php echo esc_attr( $cur_val ); ?>" />
				</label>
			</p>
	<?php
		endforeach;
		$mi_inp_id = 'edit-menu-item-attr-title-' . $item_id;
		echo '<script>mlChangeInputPos("' . $mi_inp_id . '", ' . '"' . $mi_inp_id . '_" );</script>';
		$output = preg_replace('/(.*\sid="edit-menu-item-attr-title-' . $item_id . '".*<\/p>)(.*)/Us', '$1' . ob_get_clean() . '$2', $output);
	}
}


class ML_Custom_Menu {
	function __construct() {
		// add locale title fields to menu-item
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'ml_setup_nav_menu_item' ) );
		// save locale title fields 
		add_action( 'wp_update_nav_menu_item', array( $this, 'ml_update_nav_menu_item'), 10, 3 );
		// menu editing walker
		add_filter( 'wp_edit_nav_menu_walker', array( $this, 'ml_edit_nav_menu_walker'), 10, 2 );
	}
	
	function ml_setup_nav_menu_item( $menu_item ) {
		global $ml_registered_mlocales;
		if ( ! $menu_item ) return $menu_item; // See "wp_ajax_add_menu_item()", at the 1st 'wp_setup_nav_menu_item', it can be 'null'
		foreach ( $ml_registered_mlocales as $mlocale ) {
			$title_locale = get_post_meta($menu_item->ID, ml_postmeta_name('post_title', $mlocale), TRUE);
			if ( $title_locale != '' ) {
				$memb_name = 'title_' . $mlocale;
				$menu_item->$memb_name = $title_locale;
			}
			$attr_title_locale = get_post_meta($menu_item->ID, ml_postmeta_name('post_excerpt', $mlocale), TRUE);
			if ( $attr_title_locale != '' ) {
				$memb_name = 'attr_title_' . $mlocale;
				$menu_item->$memb_name = $attr_title_locale;
			}
		}
		if ( isset( $menu_item->type ) && $menu_item->type === 'ml_switcher_menu' ) {
			$menu_item->type_label = __( 'More-Lang Switcher', ML_TDOMAIN );
			if ( ! isset( $menu_item->title ) || $menu_item->title === '' ) {
				$menu_item->title = ' '; // otherwise '$menu_item' will not be saved
			}
		}
		return $menu_item;
	}
	
	function ml_update_nav_menu_item( $menu_id, $menu_item_db_id, $args ) {
		global $ml_registered_mlocales;
		foreach ( $ml_registered_mlocales as $mlocale ) {
			if ( isset( $_POST['menu-item-title_' . $mlocale] ) ) {
				$m_titles = $_POST['menu-item-title_' . $mlocale];
				if ( is_array($m_titles) && isset($m_titles[$menu_item_db_id]) && is_string($m_titles[$menu_item_db_id]) ) {
					$m_title = sanitize_post_field( 'post_title', $m_titles[$menu_item_db_id], $menu_item_db_id, 'db' );
					update_post_meta( $menu_item_db_id, ml_postmeta_name('post_title', $mlocale), $m_title );
				}
			}
			if ( isset( $_POST['menu-item-attr-title_' . $mlocale] ) ) {
				$m_attr_titles = $_POST['menu-item-attr-title_' . $mlocale];
				if ( is_array($m_attr_titles) && isset($m_attr_titles[$menu_item_db_id]) && is_string($m_attr_titles[$menu_item_db_id]) ) {
					$m_attr_title = sanitize_post_field( 'post_excerpt', $m_attr_titles[$menu_item_db_id], $menu_item_db_id, 'db' );
					update_post_meta( $menu_item_db_id, ml_postmeta_name('post_excerpt', $mlocale), $m_attr_title );
				}
			}
		}
	}
	
	function ml_edit_nav_menu_walker($walker, $menu_id) {
		return 'morelang\ML_Walker_Nav_Menu_Edit';
	}
}
$GLOBALS['ml_custom_menu'] = new ML_Custom_Menu();
