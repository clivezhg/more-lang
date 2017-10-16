<?php
namespace morelang;

function ml_get_request_url() {
	$request_scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off') ? "https" : "http";
	$request_url = "$request_scheme://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	return remove_query_arg("lang", $request_url);
}

/* the more-lang language switcher panel
 */
function ml_lang_panel($pos = '', $is_widget = FALSE) {
	global $ml_registered_langs, $ml_opt_obj;
	// Note that when using ISAPI with IIS, the value will be off if the request was not made through the HTTPS protocol. 
	$request_url = ml_get_request_url();
	$cur_locale = ml_get_locale();
	echo '<div class="ml-lang-switcher-wrap ' . $pos . '" style="position: '
			. (empty($ml_opt_obj->ml_pos_text) && !$is_widget ? 'fixed' : 'absolute') . '">';
	echo '<ul class="ml-lang-switcher">';
	if($pos) echo '<li class="ml-lang-close" onclick="this.classList && this.parentNode.classList.remove(\'ml-open\')">✖';
	foreach ($ml_registered_langs as $idx=>$lang) {
		$img_src = "";
		if (isset($lang->flag)) $img_src = plugin_dir_url(__FILE__ ) . '../cflag/' . $lang->flag;
		$lang_img = '<img src="' . $img_src . '" alt="' . esc_attr($lang->label)  . '">';
		$lang_txt = '<span>' . esc_html($lang->label) . '</span>';
		if ( $lang->locale === $cur_locale || (empty($cur_locale) && $idx == 0) ) {
			$active_clicked = ($pos ? ' onclick="this.classList && this.parentNode.classList.add(\'ml-open\')"' : '');
			echo '<li class="ml-lang-item ml-active"' . $active_clicked . '>' . '<a>' . $lang_img . $lang_txt . '</a></li>';
		}
		else {
			echo '<li class="ml-lang-item">' . '<a href="' . esc_url(ml_add_lang_to_url($request_url, $lang->url_locale)) . '">' . $lang_img . $lang_txt . '</a></li>';
		}
	}
	echo '</ul></div>';
	if ( !empty($ml_opt_obj->ml_pos_text) ) {
		$css_selector = addslashes( $ml_opt_obj->ml_pos_text );
?>
	<script type="text/javascript">
		document.onreadystatechange = function () {
			if (document.readyState === "interactive") {
				ml_move_switcher();
			}
		}
		function ml_move_switcher() {
			var ml_target_ele = document.querySelector("<?php echo $css_selector ?>");
			var ml_switcher_ele = document.querySelector(".ml-lang-switcher-wrap");
			console.log("moving...", ml_target_ele, ml_switcher_ele);
			ml_switcher_ele.parentNode.removeChild(ml_switcher_ele);
			if (ml_target_ele && ml_switcher_ele) ml_target_ele.insertAdjacentElement("beforeend", ml_switcher_ele);
		}
	</script>
<?php
	}
}

/* the more-lang language switcher Widget
 */
class MorelangWidget extends \WP_Widget {
	function __construct() {
		$widget_ops = array('classname' => 'morelang_widget', 'description' => __('More-Lang Switcher Widget', 'morelang') );
		parent::__construct('more-lang-switcher', __('More-Lang Switcher', 'morelang'), $widget_ops);
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args((array) $new_instance, array( 'title' => ''));
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

	function widget( $args, $instance ) {
		global $ml_registered_langs;
		extract( $args );
		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Language Switcher' ) : $instance['title'], $instance, $this->id_base);
		echo $before_widget;
		if ( $title) {
			echo $before_title . $title . $after_title;
		}
		morelang_frontend_scripts( TRUE );
		ml_lang_panel( '', TRUE );
		echo $after_widget;
	}

	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = esc_attr( $instance['title'] );
	?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
			name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
<?php
	}
}

add_action( 'widgets_init', create_function('', 'return register_widget("morelang\MorelangWidget");') );


/* Automatically generate language chooser according to the configuration. */
add_action( 'wp_footer', 'morelang\ml_auto_language_chooser', 100 );
function ml_auto_language_chooser() {
	global $ml_opt_obj;
	if (isset($ml_opt_obj->ml_auto_chooser) && $ml_opt_obj->ml_auto_chooser) {
		ml_lang_panel( isset($ml_opt_obj->ml_pos_sel) ? $ml_opt_obj->ml_pos_sel : "" );
	}
}


add_action('wp_enqueue_scripts', 'morelang\morelang_frontend_scripts', 10);
function morelang_frontend_scripts( $force = FALSE ) {
	global $ml_opt_obj;
	if (isset($ml_opt_obj->ml_auto_chooser) && $ml_opt_obj->ml_auto_chooser || $force) {
		wp_enqueue_style( "morelang_front_style", plugin_dir_url( __FILE__ ) . '../css/morelang_front.css', array(), ML_VER );
	}
}


add_action( 'admin_head-nav-menus.php', 'morelang\ml_register_menu_meta_box' );
function ml_register_menu_meta_box() {
	add_meta_box( 'ml-menu-meta-box-id', esc_html__( 'More-Lang', 'morelang' ),
			'morelang\ml_render_menu_meta_box', 'nav-menus', 'side', 'core' );
}
function ml_render_menu_meta_box() {
	global $_nav_menu_placeholder, $nav_menu_selected_id;
	$_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1;
?>
	<div id="ml-link-cat" class="ml-link-div"><div class="tabs-panel-active">
	<ul id="ml-categorychecklist-pop" class="categorychecklist form-no-clear">
		<li>
		<label class="menu-item-title">
			<input class="menu-item-checkbox" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-object-id]" value="-1" type="checkbox" checked><?php _e( 'More-Lang Switcher', 'morelang' ) ?>
			<!-- 'menu-item-object-id': for Custom Link, it's the menu-item id; for the others, it's the target object id(any exceptions?) -->
		</label>
		<input class="menu-item-db-id" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-db-id]" value="0" type="hidden">
		<input class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" value="ml_switcher_menu" type="hidden">
		<input class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]" value="<?php _e( 'More-Lang Switcher', 'morelang' ) ?>" type="hidden">
		<input class="menu-item-target" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-target]" value="" type="hidden">
		<input class="menu-item-classes" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-classes]" value="ml-switcher-menuitem" type="hidden">
		</li>
	</ul>
	<p class="button-controls wp-clearfix">
		<span class="add-to-menu">
			<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e('Add to Menu'); ?>" name="add-custom-menu-item" id="submit-ml-link-cat" />
			<span class="spinner"></span>
		</span>
	</p>
	</div></div><!-- /.ml-link-div -->
<?php
}


/* Generate localized menu items for 'wp_nav_menu(...)'.
 */
if ( ! is_admin() ) {
	add_filter( 'wp_get_nav_menu_items', 'morelang\ml_menu_items', 10, 3 );
}
function ml_menu_items( $items, $menu, $args ) {
	global $ml_registered_langs;
	$max_id = -1;
	$max_menu_order = -1;
	$ml_sub_menus = array();
	$request_url = ml_get_request_url();
	$cur_locale = ml_get_locale();
	foreach ( $items as $idx=>$menu_item_obj ) {
		$max_id = max( $max_id, $menu_item_obj->ID );
		$max_menu_order = max( $max_menu_order, $menu_item_obj->menu_order );
		if ( $menu_item_obj->type === 'ml_switcher_menu' ) {
			$cur_sub_menus = array();
			$menu_item_obj->type = 'custom';
			$menu_item_obj->object_id = $menu_item_obj->ID;
			foreach ( $ml_registered_langs as $lang_idx=>$lang ) {
				$img_src = "";
				if (isset($lang->flag)) $img_src = plugin_dir_url(__FILE__ ) . '../cflag/' . $lang->flag;
				$lang_img = '<img src="' . $img_src . '" alt="' . esc_attr($lang->label)  . '" style="margin-right:5px;">';
				$lang_txt = '<span>' . esc_html($lang->label) . '</span>';
				$new_mi_obj = clone $menu_item_obj;
				$new_mi_obj->menu_item_parent = strval( $menu_item_obj->ID );
				$new_mi_obj->title = $lang_img . $lang_txt;
				if ( $lang->locale === $cur_locale || (empty($cur_locale) && $lang_idx == 0) ) {
					$menu_item_obj->title = $lang_img . $lang_txt;
					$new_mi_obj->url = '';
				}
				else
					$new_mi_obj->url = ml_add_lang_to_url($request_url, $lang->url_locale);
				array_push( $cur_sub_menus, $new_mi_obj );
			}
			$ml_sub_menus[$idx] = $cur_sub_menus;
		}
	}
	foreach ( $ml_sub_menus as $idx => &$sub_menus ) {
		foreach ( $sub_menus as &$sub_menu ) {
			$sub_menu->ID = ++$max_id;
			$sub_menu->db_id = $sub_menu->ID;
			$sub_menu->object_id = $sub_menu->ID;
			$sub_menu->menu_order = ++$max_menu_order;
		}
		array_splice( $items, $idx+1, 0, $sub_menus );
	}
	return  $items;
}
