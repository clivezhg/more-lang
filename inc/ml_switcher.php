<?php
namespace morelang;

function ml_get_request_nolang_url() {
	global $ml_requested_locale, $ml_requested_url_locale;
	// Note that when using ISAPI with IIS, the value will be off if the request was not made through the HTTPS protocol.
	$request_scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off') ? "https" : "http";
	$request_uri = $_SERVER['REQUEST_URI'];
	if ( $ml_requested_locale ) {
		$request_uri =  preg_replace( '/\/' . $ml_requested_url_locale . '(\/|$|([?]))/i',  '/$2', $request_uri, 1 );
	}
	$request_url = "$request_scheme://$_SERVER[HTTP_HOST]$request_uri"; // HTTP_HOST is not a constant
	return remove_query_arg("lang", $request_url);
}

/* The More-Lang language switcher panel
 */
function ml_lang_panel($pos = '', $is_widget = FALSE) {
	global $ml_registered_langs, $ml_opt_obj;
	ml_set_urlmode_to_qry_if_needed();

	$request_url = ml_get_request_nolang_url();
	$cur_locale = ml_get_locale();
	$class_name = 'ml-lang-switcher-wrap';
	if ( $pos ) $class_name .= " ml-$pos";
	elseif (! empty($ml_opt_obj->ml_pos_text)) $class_name .= " ml-hide-ele";
	echo '<div class="' . esc_attr( $class_name ) . '" style="position:'
			. (empty($ml_opt_obj->ml_pos_text) && !$is_widget ? 'fixed' : 'absolute') . '">';
	echo '<ul class="ml-lang-switcher">';
	if($pos) echo '<li class="ml-lang-close" onclick="this.classList && this.parentNode.classList.remove(\'ml-open\')">✖';
	foreach ($ml_registered_langs as $idx=>$lang) {
		$img_src = "";
		if (isset($lang->flag)) $img_src = plugin_dir_url( dirname(__FILE__) ) . 'cflag/' . $lang->flag;
		/* The default country flag icon file can be replaced as needed */
		$img_src = apply_filters( 'morelang_flag_url', $img_src, $lang->locale, 'panel' );
		$img_src = esc_attr( $img_src );
		$lang_img = '<img src="' . $img_src . '" alt="' . esc_attr($lang->label)  . '">';
		$lang_txt = '<span>' . esc_html($lang->label) . '</span>';
		$loc_cls = 'ml-locale-' . $lang->locale;
		if ( $lang->locale === $cur_locale || ($cur_locale === '' && $idx == 0) ) {
			$active_clicked = ($pos ? ' onclick="this.classList && this.parentNode.classList.add(\'ml-open\')"' : '');
			echo '<li class="ml-lang-item ml-active ' . $loc_cls . '"' . $active_clicked . '>' . '<a>' . $lang_img . $lang_txt . '</a></li>';
		}
		else {
			$filtered_request_url = apply_filters('morelang_map_to_locale_url', $request_url, $idx==0 ? '' : $lang->locale);
			$target_url = ml_add_lang_to_url($filtered_request_url, $lang->url_locale);
			echo '<li class="ml-lang-item ' . $loc_cls . '">' . '<a href="' . esc_url( $target_url ) . '">' . $lang_img . $lang_txt . '</a></li>';
		}
	}
	echo '</ul></div>';
	if ( isset( $ml_opt_obj->ml_pos_text ) && $ml_opt_obj->ml_pos_text != '' ) {
		$css_selector = addslashes( html_entity_decode( $ml_opt_obj->ml_pos_text ) );
?>
	<script type="text/javascript">
		document.onreadystatechange = function () {
			if (document.readyState === "interactive") {
				ml_move_switcher();
			}
		}
		function ml_move_switcher() {
			var mlTargetEles = document.querySelectorAll("<?php echo $css_selector ?>");
			var mlSwitcherEle = document.querySelector(".ml-lang-switcher-wrap");
			mlSwitcherEle.parentNode.removeChild(mlSwitcherEle);
			if (! (mlTargetEles && mlTargetEles.length && mlSwitcherEle)) return;
			mlSwitcherEle.className = 'ml-lang-switcher-wrap';
			for (var i = 0; i < Math.min(mlTargetEles.length,36); i++) {
				var newEle = mlSwitcherEle.cloneNode ? mlSwitcherEle.cloneNode(true) : mlSwitcherEle;
				mlTargetEles[i].insertAdjacentElement("beforeend", newEle);
			}
		}
	</script>
<?php
	}

	ml_restore_urlmode_if_needed();
}

/* The More-Lang language switcher Widget
 */
class MorelangWidget extends \WP_Widget {
	function __construct() {
		$widget_ops = array('classname' => ML_UID . 'widget', 'description' => __('More-Lang Switcher Widget', ML_TDOMAIN) );
		parent::__construct(ML_UID . 'switcher', __('More-Lang Switcher', ML_TDOMAIN), $widget_ops);
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args((array) $new_instance, array( 'title' => ''));
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

	function widget( $args, $instance ) {
		extract( $args );
		$title = (! isset( $instance['title'] ) || $instance['title'] === '') ? __( 'Language Switcher', ML_TDOMAIN ) : $instance['title'];
		$title = apply_filters('widget_title', $title, $instance, $this->id_base);
		echo $before_widget;
		if ( $title !== null ) {
			echo $before_title . esc_html($title) . $after_title;
		}
		morelang_frontend_scripts( TRUE );
		ml_lang_panel( '', TRUE );
		echo $after_widget;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = esc_attr( $instance['title'] );
	?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php esc_html_e( 'Title:', ML_TDOMAIN ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
			name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
<?php
	}
}

add_action( 'widgets_init', function() { register_widget('morelang\MorelangWidget'); } );


/* Automatically generate language chooser according to the configuration. */
add_action( 'wp_footer', 'morelang\ml_auto_language_chooser', 100 );
function ml_auto_language_chooser() {
	global $ml_opt_obj;
	if (isset($ml_opt_obj->ml_auto_chooser) && $ml_opt_obj->ml_auto_chooser) {
		ml_lang_panel( isset($ml_opt_obj->ml_pos_sel) ? $ml_opt_obj->ml_pos_sel : "" );
	}
}
/* "login" and "register" pages(wp-login.php) */
add_action( 'login_footer', 'morelang\ml_auto_language_chooser', 100 );


add_action('wp_enqueue_scripts', 'morelang\morelang_frontend_scripts', 10);
function morelang_frontend_scripts( $force = FALSE ) {
	global $ml_opt_obj;
	if (isset($ml_opt_obj->ml_no_css) && $ml_opt_obj->ml_no_css) return;
	if (isset($ml_opt_obj->ml_auto_chooser) && $ml_opt_obj->ml_auto_chooser || $force) {
		$css_file = 'morelang_front.css';
		if (isset($ml_opt_obj->ml_style_sel) && $ml_opt_obj->ml_style_sel === 'horizontal') {
			$css_file = 'morelang_front_hr.css';
		}
		$front_css_url = ML_PLUGIN_URL . 'css/' . $css_file;
		$front_css_url = apply_filters( 'morelang_front_cssurl', $front_css_url );
		/* The default css file can be replaced as needed */
		wp_enqueue_style( "morelang_front_style", $front_css_url, array(), ML_VER );
	}
}
add_action('login_enqueue_scripts', 'morelang\morelang_frontend_scripts', 10);


add_action( 'admin_head-nav-menus.php', 'morelang\ml_register_menu_meta_box' );
function ml_register_menu_meta_box() {
	add_meta_box( 'ml-menu-meta-box-id', esc_html__( 'More-Lang', ML_TDOMAIN ),
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
			<input class="menu-item-checkbox" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-object-id]" value="-1" type="checkbox" checked><?php esc_html_e( 'More-Lang Switcher', ML_TDOMAIN ) ?>
			<!-- 'menu-item-object-id': for Custom Link, it's the menu-item id; for the others, it's the target object id(any exceptions?) -->
		</label>
		<input class="menu-item-db-id" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-db-id]" value="0" type="hidden">
		<input class="menu-item-object" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-object]" value="ml-switcher-obj" type="hidden">
		<input class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" value="ml_switcher_menu" type="hidden">
		<input class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]" value="" type="hidden">
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
	add_filter( 'wp_get_nav_menu_items', 'morelang\ml_switcher_menu_items', 1, 3 );
}
function ml_switcher_menu_items( $items, $menu, $args ) {
	global $ml_registered_langs;
	/* In normal cases, "/wp-login.php" will not generate "nav_menu". This is put here in case of future use. */
	ml_set_urlmode_to_qry_if_needed();

	$max_menu_order = -1;
	$ml_sub_menus = array();
	$request_url = ml_get_request_nolang_url();
	$cur_locale = ml_get_locale();
	foreach ( $items as $idx=>$menu_item_obj ) {
		$max_menu_order = max( $max_menu_order, $menu_item_obj->menu_order );
		if ( $menu_item_obj->type === 'ml_switcher_menu' ) {
			$cur_sub_menus = array();
			$menu_item_obj->type = 'custom';
			$menu_item_obj->object_id = $menu_item_obj->ID;
			if ( $cur_locale ) {
				$title_meta = get_post_meta($menu_item_obj->ID, ml_postmeta_name('post_title', $cur_locale), TRUE);
				if ( ml_valid_text($title_meta) ) $menu_item_obj->title = $title_meta;
			}
			foreach ( $ml_registered_langs as $lang_idx=>$lang ) {
				$img_src = "";
				if (isset($lang->flag)) $img_src = plugin_dir_url( dirname(__FILE__) ) . 'cflag/' . $lang->flag;
				$img_src = apply_filters( 'morelang_flag_url', $img_src, $lang->locale, 'menu' );
				$img_src = esc_attr( $img_src );
				$lang_img = '<img class="ml-switcher-flag" src="' . $img_src . '" alt="' . esc_attr($lang->label)  . '" style="margin:0px 5px;">';
				$lang_txt = '<span>' . esc_html($lang->label) . '</span>';
				$new_mi_obj = clone $menu_item_obj;
				$new_mi_obj->menu_item_parent = strval( $menu_item_obj->ID );
				$new_mi_obj->title = $lang_img . $lang_txt; // The format is also used in "ml_text.php#ml_the_title(...)"
				if ( $lang->locale === $cur_locale || ($cur_locale === '' && $lang_idx == 0) ) {
					if ( trim( $menu_item_obj->title ) === '' ) $menu_item_obj->title = $lang_img . $lang_txt;
					else $menu_item_obj->title = $lang_img . '<span>' . esc_html($menu_item_obj->title) . '</span>';
					$new_mi_obj->url = '';
				}
				else {
					$filtered_request_url = apply_filters('morelang_map_to_locale_url', $request_url, $lang_idx==0 ? '' : $lang->locale);
					$target_url = ml_add_lang_to_url($filtered_request_url, $lang->url_locale);
					$new_mi_obj->url = $target_url;
				}
				if ( isset($new_mi_obj->classes) && is_array($new_mi_obj->classes) ) {
					array_push( $new_mi_obj->classes, 'ml-locale-' . $lang->locale );
				}
				array_push( $cur_sub_menus, $new_mi_obj );
			}
			$ml_sub_menus[$idx] = $cur_sub_menus;
		}
	}
	foreach ( $ml_sub_menus as $idx => &$sub_menus ) {
		foreach ( $sub_menus as &$sub_menu ) {
			$sub_menu->ID = 0; // 0 is expected to never be a valid post_id, otherwise issue may be caused
			$sub_menu->db_id = $sub_menu->ID;
			$sub_menu->object_id = $sub_menu->ID;
			$sub_menu->menu_order = ++$max_menu_order;
		}
		array_splice( $items, $idx+1, 0, $sub_menus );
	}

	ml_restore_urlmode_if_needed();

	return  $items;
}
