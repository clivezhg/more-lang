<?php
namespace morelang;
/* Handle the special cases of the Menus */


/* Keep the processed menu Posts */
global $ml_special_menu_ids;
$ml_special_menu_ids = array();


/* Suppress the default More-Lang filters, otherwise their results will be used */
add_filter('get_post_metadata', 'morelang\ml_special_post_metadata', 1, 3);
function ml_special_post_metadata( $value, $object_id, $meta_key ) {
	global $ml_special_menu_ids;
	$cur_locale = ml_get_locale();
	if (! $cur_locale) return $value;

	if ($meta_key === ml_postmeta_name('post_title', $cur_locale)
			|| $meta_key === ml_postmeta_name('post_excerpt', $cur_locale)) {
		if ( in_array($object_id, $ml_special_menu_ids) ) {
			/* The default filters will not use '' */
			return '';
		}
	}

	return $value;
}


/* Generate localized menu item title. */
add_filter( 'wp_get_nav_menu_items', 'morelang\ml_special_wp_get_nav_menu_items', 1, 3 );
function ml_special_wp_get_nav_menu_items( $items, $menu, $args ) {
	global $ml_special_menu_ids;
	foreach ( $items as &$menu_item ) {
		if ( isset($menu_item->title) ) {
			$req_text = ml_special_get_req_part( $menu_item->title );
			$menu_item->title = $req_text;
			$ml_special_menu_ids[] = $menu_item->ID;
		}
	}

	return $items;
}


/* Generate localized menu item title attribute. */
add_filter( 'nav_menu_link_attributes', 'morelang\ml_special_nav_menu_link_attributes', 0, 3 );
function ml_special_nav_menu_link_attributes( $atts, $item, $args ) {
	if ( isset($atts['title']) && isset($item->ID) ) {
		$req_text = ml_special_get_req_part( $atts['title'] );
		$atts['title'] = $req_text;
	}

	return $atts;
}
