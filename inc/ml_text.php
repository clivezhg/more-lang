<?php
namespace morelang;

/* Add the 'wp_postmeta' table to the searching scope
 */
add_filter('posts_distinct', 'morelang\morelang_search_distinct');
add_filter('posts_join', 'morelang\morelang_search_join' );
add_filter('posts_where', 'morelang\morelang_search_where' );
function morelang_search_distinct() {
	if( is_search() ) {
		return "DISTINCT";
	}
}
function morelang_search_join($join) {
	global $wpdb;
	if (is_search()) {
		$join .= " LEFT JOIN $wpdb->postmeta ON " .
			$wpdb->posts . ".ID = " . $wpdb->postmeta . ".post_id ";
	}
	return $join;
}
function morelang_search_where($where) {
	global $wpdb;
	if (is_search()) {
		$where = preg_replace("/\(\s*$wpdb->posts.post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
			"($wpdb->posts.post_title LIKE $1) OR ($wpdb->postmeta.meta_value LIKE $1)", $where);
	}
	return $where;
}


/* Generate localized blog info options */
ml_add_option_filter( 'blogname' );
ml_add_option_filter( 'blogdescription' );
function ml_add_option_filter( $optname ) {
	$ml_option_filter = function ( $pre_option ) use ($optname) {
		$cur_locale = ml_get_locale();
		if ( empty($cur_locale) ) return false;
		$optname_loc = "morelang_{$optname}_$cur_locale";
		$opt_val = get_option( $optname_loc );
		if ($opt_val) return $opt_val;
		return false;
	};
	add_filter( "pre_option_$optname", $ml_option_filter, 10, 1 );
}


/* Generate localized widget_title for WP_Widget_Text */
add_filter('widget_title', 'morelang\morelang_widget_title', 10, 3);
function morelang_widget_title($title, $instance = NULL, $id_base = NULL) {
	$cur_locale = ml_get_locale();
	if ( strlen($cur_locale) > 0 ) {
		$title_loc = ml_postmeta_name('title', $cur_locale);
		if ( ! empty($instance[$title_loc]) ) {
			return $instance[$title_loc];
		}
	}
	return $title;
}

/* Generate localized widget_text for WP_Widget_Text */
add_filter('widget_text', 'morelang\morelang_widget_text', 10, 3);
function morelang_widget_text($text, $instance = NULL, $id_base = NULL) {
	$cur_locale = ml_get_locale();
	if ( strlen($cur_locale) > 0 ) {
		$text_loc = ml_postmeta_name('text', $cur_locale);
		if ( ! empty($instance[$text_loc]) ) {
			return $instance[$text_loc];
		}
	}
	return $text;
}


/* Generate localized taxonomy-term. */
add_filter('get_term', 'morelang\ml_get_term', 10, 2);
function ml_get_term($_term, $taxonomy) {
	$locale = ml_get_locale();
	if ( $locale ) {
		$taxonomy_names = json_decode( get_option( "morelang_taxonomy_${taxonomy}_$_term->term_id" ) );
		if ( $taxonomy_names && isset($taxonomy_names->$locale) ) {
			$_term->name = $taxonomy_names->$locale;
		}
	}
	return $_term;
}

/* Generate localized taxonomy-term for 'wp_list_categories' */
add_filter('list_cats', 'morelang\ml_list_cat', 10, 2);
function ml_list_cat($name, $category) { // other 'list_cats' scenarios & other taxonomies?
	if ( ! isset($name) || ! isset($category) ) return $name;
	$locale = ml_get_locale();
	if ( $locale ) {
		$taxonomy_names = json_decode( get_option( "morelang_taxonomy_{$category->taxonomy}_{$category->term_id}" ) );
		if ( $taxonomy_names && isset($taxonomy_names->$locale) ) {
			return $taxonomy_names->$locale;
		}
	}
	return $name;
}


/* Generate localized post_title. */
/* For a 'nav_menu_item' Post, if its 'type' is 'post_type', then there are 2 steps of filter:
   1. the post title of the Post(used as the context) referenced by 'object_id' is filtered & returned;
   2. the result of step 1 is filtered & returned in the context of the 'nav_menu_item' Post */
add_filter( 'the_title', 'morelang\ml_the_title', 10, 2 );
function ml_the_title( $title, $id ) {
	$locale = ml_get_locale();
	if ( $locale ) {
		$title_meta = get_post_meta($id, ml_postmeta_name('post_title', $locale), TRUE );
		if ( $title_meta ) return $title_meta;
	}
	return $title;
}


/* Generate localized post_excerpt. */
add_filter( 'get_the_excerpt', 'morelang\ml_get_the_excerpt', 10, 2 );
function ml_get_the_excerpt( $excerpt, $post ) {
	$locale = ml_get_locale();
	if ( $locale && $post ) {
		$excerpt_meta = get_post_meta($post->ID, ml_postmeta_name('post_excerpt', $locale), TRUE );
		if ( $excerpt_meta ) return $excerpt_meta;
	}
	return $excerpt;
}


/* Generate localized post_content.
   See "/wp-includes/query.php#setup_postdata" */
add_filter( 'content_pagination', 'morelang\ml_content_pagination', 10, 2 );
function ml_content_pagination($pages, $post) {
	global $ml_registered_langs;
	$locale = ml_get_locale();
	if ( empty($locale) ) return $pages;
	$ml_content = get_post_meta($post->ID, ml_postmeta_name('post_content', $locale), TRUE );
	if ( empty($ml_content) ) {
		foreach ( $ml_registered_langs as $lang_obj ) {
			if ( $lang_obj->locale === $locale && !empty($lang_obj->moreOpt->missing_content) ) {
				return array( $lang_obj->moreOpt->missing_content );
			}
		}
		return $pages;
	}
	$ml_pages = array();
	if ( false !== strpos( $ml_content, '<!--nextpage-->' ) ) {
		$ml_content = str_replace( "\n<!--nextpage-->\n", '<!--nextpage-->', $ml_content );
		$ml_content = str_replace( "\n<!--nextpage-->", '<!--nextpage-->', $ml_content );
		$ml_content = str_replace( "<!--nextpage-->\n", '<!--nextpage-->', $ml_content );

		// Ignore nextpage at the beginning of the content.
		if ( 0 === strpos( $ml_content, '<!--nextpage-->' ) )
			$ml_content = substr( $ml_content, 15 );

		$ml_pages = explode('<!--nextpage-->', $ml_content);
	} else {
		$ml_pages = array( $ml_content );
	}
	return $ml_pages;
}


/* Generate localized menu items for 'wp_nav_menu(...)'.
 */
add_filter( 'wp_nav_menu_args', 'morelang\ml_nav_menu_args', 10, 1 );
function ml_nav_menu_args( $args ) {
	 $args['walker'] = new ML_Walker_NavMenu();
	 return $args;
}
class ML_Walker_NavMenu extends \Walker_Nav_Menu
{
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		$locale = ml_get_locale();
		if ( $locale ) {
			if ( $item->type === 'custom' && trim($item->url) ) { // no link filter for custom link!?
				$urlobj = parse_url( $item->url );
				if ( empty( $urlobj['scheme'] ) && empty( $urlobj['host'] ) ) {
					$item->url = ml_add_lang_to_url( home_url($item->url) );
				}
			}
		}
		parent::start_el($output, $item, $depth, $args);
	}
}
