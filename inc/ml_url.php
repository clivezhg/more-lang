<?php
namespace morelang;

add_action( 'plugins_loaded', 'morelang\ml_clean_uri' );
/* Clean the language parameter of a URL added by More-Lang */
function ml_clean_uri() {
	global $ml_requested_locale, $ml_requested_url_locale, $pagenow;
	if ( $ml_requested_locale && $pagenow === 'index.php' ) {
		$_SERVER['REQUEST_URI'] =  preg_replace( '/\/' . $ml_requested_url_locale . '(\/|$)/i',  '$1', $_SERVER['REQUEST_URI'], 1 );
	}
}

/* Generate language parameter for a given URL.
 */
function ml_add_lang_to_url($url, $lang='')
{
	global $ml_registered_url_mlocales, $ml_opt_obj, $ml_requested_url_locale;
	$url = preg_replace( '/[?]lang=[a-zA-Z_-]+\//', '/', $url ); // incorrectly added 'lang', like 'wp_pagenavi' plugin
	if ( ! $lang ) $lang = $ml_requested_url_locale;
	if ( $lang == '' || !in_array($lang, $ml_registered_url_mlocales) ) return $url;
	if ( isset($ml_opt_obj->ml_url_mode) && $ml_opt_obj->ml_url_mode === ML_URLMODE_QRY ) {
		$url = add_query_arg(array('lang'=>$lang), $url);
	}
	else {
		$url_base = ml_get_urlbase_for_re();
		if ( preg_match( '/'.$url_base.'\/'.$lang.'/', $url.'/' ) ) return $url; // to avoid duplication
		$url = preg_replace( '/(' . $url_base . ')/i', '$1/' . $lang, $url, 1 );		
	}
	return $url;
}

function ml_add_link_locale_filters()
{
	$links_to_filter = array(
		// 'home_url', // as the base of other URLs, to filter it will lead to double adding.
		'author_feed_link',
		'author_link',
		'get_comment_author_url_link',
		'day_link',
		'month_link',
		'year_link',
		'page_link',
		'post_link',
		'post_comments_feed_link',
		'post_type_link',
		'post_type_archive_link',
		'category_feed_link',
		'category_link',
		'tag_link',
		'term_link',
		'the_permalink',
		'feed_link',
		'tag_feed_link',
		'get_pagenum_link',
	);
	foreach ( $links_to_filter as $link ) {
		add_filter( $link, 'morelang\ml_add_lang_to_url' );
	}
}
ml_add_link_locale_filters();
