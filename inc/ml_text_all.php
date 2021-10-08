<?php
namespace morelang;

/* This file will not be skipped when the default locale is requested. */

/* Generate localized date_format & time_format. */
ml_add_dtf_option_filter( 'date_format' );
ml_add_dtf_option_filter( 'time_format' );
function ml_add_dtf_option_filter( $optname ) {
	$ml_datetime_format = function ( $pre_option ) use ($optname) {
		global $ml_registered_langs;
		if ( count($ml_registered_langs) === 0 ) return false;
		$locale = ml_get_locale();
		$lang_obj = $ml_registered_langs[0];
		foreach ( $ml_registered_langs as $cur_lang_obj ) {
			if ( $cur_lang_obj->locale === $locale) $lang_obj = $cur_lang_obj;
		}
		if ( isset( $lang_obj->$optname ) && $lang_obj->$optname != '' )
			return $lang_obj->$optname;
		return false;
	};
	add_filter( "pre_option_$optname", $ml_datetime_format, 1, 1 );
}


/* Use 'missing_content' for the default locale if the post_content is empty. */
add_filter( 'content_pagination', 'morelang\ml_content_pagination_default', 1, 2 );
function ml_content_pagination_default($pages, $post) {
	global $ml_registered_langs;
	if ( ml_get_locale() === '' ) {
		if ( count($pages) === 1 && $pages[0] === '' && isset( $ml_registered_langs[0]->moreOpt->missing_content ) ) {
			$m_c = $ml_registered_langs[0]->moreOpt->missing_content;
			if ( $m_c != '' ) return array( $m_c );
		}
	}
	return $pages;
}


/* Add the 'wp_postmeta' table to the searching scope.
 */
add_filter( 'posts_distinct', 'morelang\ml_search_distinct' );
add_filter( 'posts_join', 'morelang\ml_search_join' );
add_filter( 'posts_where', 'morelang\ml_search_where' );
function ml_search_distinct() {
	if( is_search() ) {
		return "DISTINCT";
	}
}
function ml_search_join($join) {
	global $wpdb;
	if (is_search()) {
		$join .= " LEFT JOIN $wpdb->postmeta AS ml_pmeta ON (" .
			$wpdb->posts . ".ID = ml_pmeta.post_id ";
		$join .= "AND ml_pmeta.meta_key LIKE '_post_%" . ML_UID . "%') ";
	}
	return $join;
}
function ml_search_where($where) {
	global $wpdb;
	if (is_search()) {
		$ml_key = "'_post_%" . ML_UID . "%'";
		$where = preg_replace("/\(\s*$wpdb->posts.post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
			"($wpdb->posts.post_title LIKE $1) OR (ml_pmeta.meta_key LIKE $ml_key AND ml_pmeta.meta_value LIKE $1)", $where);
	}
	return $where;
}


/* A relative URL can be input for the Custom Link, then it is converted to absolute URL here
 */
add_filter( 'wp_nav_menu_objects', 'morelang\ml_wp_nav_menu_objects', 1, 2 ); // "wp-includes/nav-menu-template.php"
function ml_wp_nav_menu_objects( $sorted_menu_items, $args ) {
	if ( is_array($sorted_menu_items) ) {
		foreach ( $sorted_menu_items as $item ) {
			if ( $item->type === 'custom' && trim($item->url) ) { // no link filter for custom link!?
				$urlobj = parse_url( $item->url );
				if ( empty( $urlobj['scheme'] ) && empty( $urlobj['host'] ) ) {
					$item->url = ml_add_lang_to_url( home_url($item->url) );
				}
			}
		}
	}
	return $sorted_menu_items;
}
