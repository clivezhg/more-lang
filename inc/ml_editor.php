<?php
namespace morelang;

/* The saving of taxonomy-term */
add_action( 'edited_terms', 'morelang\ml_edited_terms', 10, 2 ); 
add_action( 'created_term', 'morelang\ml_created_term', 10, 3 ); 
function ml_edited_terms($term_id, $taxonomy){
	ml_save_term_names( $term_id, $taxonomy, 'name' );
}
function ml_created_term($term_id, $tt_id, $taxonomy) {
	ml_save_term_names( $term_id, $taxonomy, 'tag-name' );
}
function ml_save_term_names($term_id, $taxonomy, $name_prefix) {
	global $ml_registered_mlocales;
	$term_names = array();
	foreach ( $ml_registered_mlocales as $mlocale ) {
		$fld_name = $name_prefix . '_' . $mlocale;
		if ( isset($_POST[$fld_name]) ) {
			$term_names[$mlocale] = $_POST[$fld_name];
		}
	}
	if ( count($term_names) > 0 ) {
		update_option( 'morelang_taxonomy_' . $taxonomy . '_' . $term_id, json_encode( $term_names ) );
	}	
}

/* Add additional general options to 'whitelist_options' */
add_filter( 'whitelist_options', 'morelang\ml_whitelist_options', 10, 1 );
function ml_whitelist_options( $whitelist_options ) {
	global $ml_registered_mlocales;
	if ( isset($whitelist_options['general']) ) {
		foreach ( $ml_registered_mlocales as $mlocale ) {
			$whitelist_options['general'][] = 'morelang_blogname_' . $mlocale;
			$whitelist_options['general'][] = 'morelang_blogdescription_' . $mlocale;
		}
	}
	return $whitelist_options;
}

/* Generate taxonomy-terms as Javascript value */
$builtinTaxonomies = get_taxonomies();
foreach ( $builtinTaxonomies as $taxonomy) {
	add_action( "{$taxonomy}_pre_edit_form", 'morelang\ml_taxonomy_pre_edit_form', 10, 2 );
}
add_action( 'registered_taxonomy', 'morelang\ml_registered_taxonomy', 10, 3 );
function ml_registered_taxonomy( $taxonomy, $object_type, $args ) {
	add_action( "{$taxonomy}_pre_edit_form", 'morelang\ml_taxonomy_pre_edit_form', 10, 2 );
}
function ml_taxonomy_pre_edit_form( $tag, $taxonomy ) {
	$taxonomy_names = get_option( "morelang_taxonomy_${taxonomy}_$tag->term_id");
	if ( $taxonomy_names ) {
		echo "<script type='text/javascript'>";
		echo 'var ml_taxonomy_names = ' . $taxonomy_names . ';';
		echo "</script>\n";
	}
}


/* The editing & saving of navigation menus.
 */
require_once ABSPATH . '/wp-admin/includes/nav-menu.php';
class ML_Walker_Nav_Menu_Edit extends \Walker_Nav_Menu_Edit {
	function start_el(&$output, $item, $depth = 0, $args = Array(), $id = 0) {
		global $ml_registered_mlangs;
		parent::start_el($output, $item, $depth, $args, $id);
		$item_id = esc_attr( $item->ID );
		if ( $item->type === 'ml_switcher_menu' ) {
			$output .= '<script type="text/javascript">var ml_menu_label=document.getElementById("edit-menu-item-title-' . $item_id . '");';
			$output .= 'ml_menu_label && (ml_menu_label.parentNode.style.display="none");</script>';
		}
		if ( ! in_array($item->type, array('post_type', 'custom')) ) {
			return $output;
		}
		ob_start();
		foreach ( $ml_registered_mlangs as $mlang ):
			$memb_name = 'title_' . $mlang->locale;
			$cur_val = isset($item->$memb_name) ? $item->$memb_name : '';
			if (! $cur_val) {
				$original_local_title = get_post_meta($item->object_id, ml_postmeta_name('post_title', $mlang->locale), TRUE );
				if ( $original_local_title ) $cur_val = $original_local_title;
			}
			$lang_name = ml_get_admin_lang_label( $mlang );
	?>
			<p class="description description-wide">
				<label for="edit-menu-item-title-<?php echo $item_id . '_' . $mlang->locale; ?>">
					<?php _e( 'Navigation Label' ); echo (" ($mlang->name)"); ?><br />
					<input type="text" id="edit-menu-item-title-<?php echo $item_id . '_' . $mlang->locale; ?>" class="widefat edit-menu-item-title"
							 name="menu-item-title_<?php echo $mlang->locale;?>[<?php echo $item_id; ?>]"
							 data-langname="<?php echo $lang_name ?>"
							 value="<?php echo esc_attr( $cur_val ); ?>" />
				</label>
			</p>
	<?php
		endforeach;
		$mi_inp_id = 'edit-menu-item-title-' . $item_id;
		echo '<script>mlChangeInputPos("' . $mi_inp_id . '", ' . '"' . $mi_inp_id . '_" );</script>';
		$output = preg_replace('/(.*\sid="edit-menu-item-title-' . $item_id . '".*<\/p>)(.*)/Us', '$1' . ob_get_clean() . '$2', $output);
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
		foreach ( $ml_registered_mlocales as $mlocale ) {
			$title_locale = get_post_meta($menu_item->ID, ml_postmeta_name('post_title', $mlocale), TRUE);
			if ( ! empty($title_locale) ) {
				$memb_name = 'title_' . $mlocale;
				$menu_item->$memb_name = $title_locale;
			}
		}
		return $menu_item;
	}
	
	function ml_update_nav_menu_item( $menu_id, $menu_item_db_id, $args ) {
		global $ml_registered_mlocales;
		foreach ( $ml_registered_mlocales as $mlocale ) {
			if ( isset( $_POST['menu-item-title_' . $mlocale] ) ) {
				$m_titles = $_POST['menu-item-title_' . $mlocale];
				if ( is_array($m_titles) && isset($m_titles[$menu_item_db_id]) ) {
					update_post_meta( $menu_item_db_id, ml_postmeta_name('post_title', $mlocale), $m_titles[$menu_item_db_id] );
				}
			}
		}
	}
	
	function ml_edit_nav_menu_walker($walker, $menu_id) {
		return 'morelang\ML_Walker_Nav_Menu_Edit';
	}
}
$GLOBALS['ml_custom_menu'] = new ML_Custom_Menu();


/* Add localized input for the post_content.
   See "/wp-admin/edit-form-advanced.php" */
add_action( 'edit_form_after_editor', 'morelang\ml_edit_form_after_editor', 10, 1);
function ml_edit_form_after_editor($post) {
	global $post_type, $ml_registered_mlocales;
	if ( post_type_supports($post_type, 'editor') ) {
		foreach ( $ml_registered_mlocales as $mlocale ):
?>
	<textarea id="content_<?php echo $mlocale; ?>" name="content_<?php echo $mlocale; ?>" class="ml-local-ta" style="display:none;"><?php
		echo esc_textarea( get_post_meta($post->ID, ml_postmeta_name('post_content', $mlocale), TRUE) );
	?></textarea>
<?php endforeach; }
} 


/* Add localized input for the post_title */
add_action('edit_form_after_title', 'morelang\my_edit_form_after_title', 10, 1);
function my_edit_form_after_title($post) {
	global $post_type, $ml_registered_mlangs, $title_placeholder;
	if ( post_type_supports($post_type, 'title') ) {
		foreach ( $ml_registered_mlangs as $mlang ):
			$mlocale = $mlang->locale;
			$lang_name = ml_get_admin_lang_label( $mlang );
		?>
			<div id="titlediv_<?php echo $mlocale ?>" data-langname="<?php echo $lang_name ?>">
			<div id="titlewrap_<?php echo $mlocale ?>">
			<?php
				$cur_title_placeholder = __( $title_placeholder . ' (in ' . $mlang->name . ')' );
			?>
				<label class="ml-title-prompt-text" id="title-prompt-text_<?php echo $mlocale ?>" for="title_<?php echo $mlocale; ?>"><?php echo $cur_title_placeholder; ?></label>
				<input class="ml-title-input" type="text" name="post_title_<?php echo $mlocale; ?>" size="30" value="<?php echo esc_attr( get_post_meta($post->ID, ml_postmeta_name('post_title', $mlocale), TRUE) ); ?>" id="title_<?php echo $mlocale; ?>" spellcheck="true" autocomplete="off" />
			</div>
			</div><!-- /titlediv -->
	<?php
		endforeach;
	}
}


/* Generate localized excerpts as Javascript values */
add_action( 'edit_form_top', 'morelang\ml_edit_form_top', 10, 1 );
if ( version_compare( $GLOBALS['wp_version'], '3.7', '<' ) ) { // the olders have no 'edit_form_top'
	add_action( 'edit_form_after_editor', 'morelang\ml_edit_form_top', 10, 1 );
}
function ml_edit_form_top($post) {
	global $ml_registered_mlocales;
	echo '<script type="text/javascript">';
	foreach ( $ml_registered_mlocales as $mlocale ) {
		echo 'var ml_excerpt_' . $mlocale . '=' . json_encode( get_post_meta($post->ID, ml_postmeta_name('post_excerpt', $mlocale), TRUE ) ) . '; ';
	}
	echo "</script>\n";
}


/* Save more-lang postmetas when saving post.
   The older versions only pass 2 parameters: $post_ID, $post */
add_action( 'save_post', 'morelang\ml_save_post', 10, 3); // "/wp-includes/post.php"
function ml_save_post($post_ID, $post, $update = TRUE) {
	global $ml_registered_mlocales;
	if ( ! $post_ID ) return;
	if ( ! empty($_POST['data']['wp_autosave']) ) {
		ml_save_post_autosave($post_ID, $post, $_POST['data']['wp_autosave']);
		return;
	}
	if ( ! empty($_POST['autosave']) && $_POST['autosave'] === 'true' ) { // the olders
		ml_save_post_autosave($post_ID, $post, $_POST);
		return;
	}
	foreach ( $ml_registered_mlocales as $mlocale ) {
		/* if the submitted value is empty, remove the record to reduce table size */
		if ( empty($_POST["post_title_$mlocale"] ) ) {
			delete_post_meta($post_ID, ml_postmeta_name('post_title', $mlocale));
		}
		else {
			update_post_meta($post_ID, ml_postmeta_name('post_title', $mlocale), $_POST["post_title_$mlocale"]);
		}
		if ( empty($_POST["content_$mlocale"] ) ) {
			delete_post_meta($post_ID, ml_postmeta_name('post_content', $mlocale));
		}
		else {
			update_post_meta($post_ID, ml_postmeta_name('post_content', $mlocale), $_POST["content_$mlocale"]);
		}
		if ( empty($_POST["excerpt_$mlocale"] ) ) {
			delete_post_meta($post_ID, ml_postmeta_name('post_excerpt', $mlocale));
		}
		else {
			update_post_meta($post_ID, ml_postmeta_name('post_excerpt', $mlocale), $_POST["excerpt_$mlocale"]);
		}
	}
}



/* Autosave localized title, content, excerpt */
/* Need to hack "/wp-includes/js/autosave.js" to enable this feature. */
function ml_save_post_autosave($ID, $post, $autosave_data) {
	global $ml_registered_mlocales;
	$ml_create_single_autosave_fld = function($locale, $fld) use($ID, $autosave_data) {
		if ( ! empty($autosave_data["${fld}_$locale"]) ) {
			// update_post_meta(...);  // if 'update_post_meta' is called on a revision, the meta will be updated to the revision's parent.
			update_metadata('post', $ID, ml_postmeta_name("post_$fld", $locale), $autosave_data["${fld}_$locale"]);
		}
	};
	foreach ( $ml_registered_mlocales as $mlocale ) {
		$ml_create_single_autosave_fld($mlocale, 'title');
		$ml_create_single_autosave_fld($mlocale, 'content');
		$ml_create_single_autosave_fld($mlocale, 'excerpt');
	}
}
