<?php
namespace morelang;


// add_action( 'plugins_loaded', 'morelang\ml_clean_uri' );
   /* This approach was used without 'home_url' filter,
	   it is discarded since 'home_url' filter is used to fix the search issue of no lang info.
		(And the new approach doesn't break the URL=>WP_Resource process, see "wp-includes/rewrite.php#url_to_postid($url)") */
/* Clean the language parameter of a URL added by More-Lang */
function ml_clean_uri()
{
	global $ml_requested_locale, $ml_requested_url_locale, $pagenow;
	if ( $ml_requested_locale && $pagenow === 'index.php' ) {
		/* If no trailing '/', WP's redirection plus More-Lang's redirection may cause infinite loop */
		$_SERVER['REQUEST_URI'] =  preg_replace( '/\/' . $ml_requested_url_locale . '(\/|$|([?]))/i',  '/$2', $_SERVER['REQUEST_URI'], 1 );
	}
}


/** Generate language parameter for a given URL. */
function ml_add_lang_to_url($url, $lang='')
{
	global $ml_registered_url_mlocales, $ml_opt_obj, $ml_requested_url_locale;
	if ( ! $lang ) $lang = $ml_requested_url_locale;
	if ( $lang == '' || !in_array($lang, $ml_registered_url_mlocales) ) return $url;
	// $old_url = $url;
	/* Clean the $url:
	   1. May be called after 'home_url' filter, trailing '/' could be added, e.g., "wp-includes/link-template.php#get_permalink(...)"
	   2. Incorrectly added 'lang', like 'wp_pagenavi' plugin */
	$url = preg_replace( '/[?]lang=[a-zA-Z_-]+(\/)?/', '', $url );
	if ( isset($ml_opt_obj->ml_url_mode) && $ml_opt_obj->ml_url_mode === ML_URLMODE_QRY ) {
		$url = add_query_arg(array('lang'=>$lang), $url);
	}
	else { // partial url is given in some cases, e.g., the 'woocommerce_login_redirect' filter
		$url_base = ml_get_urlbase_for_re( parse_url($url, PHP_URL_HOST) ? FALSE : TRUE );
		if ( ! preg_match( '/'.$url_base.'\/'.$lang.'(\/|$)/', $url ) ) { // not repeat
			$url = preg_replace( '/(' . $url_base . ')/i', '$1/' . $lang, $url, 1 );
		}
	}
	// ml_add_filtered_url($old_url, $url);
	return $url;
}

/* // This part is for testing purposes only.
function ml_add_filtered_url($old_url, $new_url) {
	global $ml_filtered_urls, $wp_current_filter;
	static $idx = 0;
	if (true) {// strpos($old_url, 'lang=') > 0 ) {
		$cur_filter = $wp_current_filter[count($wp_current_filter)-1];
		$num = str_pad( (string)++$idx, 3 );
		if ( empty( $ml_filtered_urls ) ) $ml_filtered_urls = array();
		if ( ! isset($ml_filtered_urls[$cur_filter]) ) $ml_filtered_urls[$cur_filter] = array();
		$ml_filtered_urls[$cur_filter]["$num $old_url"] = $new_url;
	}
}
add_action( 'wp_footer', 'morelang\ml_url_footer', 1000 );
function ml_url_footer() {
	global $ml_filtered_urls;
	if ( ! empty($ml_filtered_urls) ) {
		foreach($ml_filtered_urls as $cur_filter=>$urlpairs) {
			echo "<script>console.log('$cur_filter');</script>";
			foreach($urlpairs as $old_url=>$new_url) {
				echo "<script>console.log('   $old_url');</script>";
				echo "<script>console.log('       $new_url');</script>";
			}
		}
	}
} */

/** Some urls must not use ML_URLMODE_PATH mode. */
function ml_add_lang_to_qry_mode_url( $url )
{
	global $ml_opt_obj;
	$old_mode = isset($ml_opt_obj->ml_url_mode) ? $ml_opt_obj->ml_url_mode : NULL;
	$ml_opt_obj->ml_url_mode = ML_URLMODE_QRY;
	$new_url = ml_add_lang_to_url( $url );
	$ml_opt_obj->ml_url_mode = $old_mode;
	return $new_url;
}

function ml_add_link_locale_filters()
{
	$links_to_filter = array(
		'home_url', // as the base of many URLs, double 'ml_add_lang_to_url' calling will happen.
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
		/// 'category_link',
		'tag_link',
		'term_link',
		'the_permalink',
		'feed_link',
		'tag_feed_link',
		'get_pagenum_link',
	);
	foreach ( $links_to_filter as $link ) {
		add_filter( $link, 'morelang\ml_add_lang_to_url', 1000 );
	}

	$qry_mode_links_to_filter = array(
		'login_url',
		'register_url',
		'lostpassword_url',
	);
	foreach ( $qry_mode_links_to_filter as $link ) {
		add_filter( $link, 'morelang\ml_add_lang_to_qry_mode_url', 1000 );
	}

	add_action( 'plugins_loaded', function() use($links_to_filter, $qry_mode_links_to_filter) {
		do_action( 'morelang_filter_url', array_merge($links_to_filter, $qry_mode_links_to_filter) );
	}, 1000 ); // Interface for add-on.
}

ml_add_link_locale_filters();


/**
 * Compare two languages. Return 0 if not match, 0.5 if only languages match, 1 if both languages and countries match.
 * @param string $lang1, string $lang2. The format can be: "xx", "xx-yy", "xx_yy".
 * @return numeric
 */
function ml_compare_langs($lang1, $lang2) {
	if ( ! is_string($lang1) || ! is_string($lang2) ) return 0;
	$arr1 = preg_split( "/_|-/", strtolower( $lang1 ) );
	$arr2 = preg_split( "/_|-/", strtolower( $lang2 ) );
	$ret = 0;
	if ( $arr1[0] === $arr2[0] ) {
		$ret = 0.5;
		if ( isset($arr1[1], $arr2[1]) && $arr1[1] === $arr2[1] ) {
			$ret = 1;
		}
	}
	return $ret;
}

/* Whether the request is linked from the same site */
function ml_from_same_site()
{
	$same_site = true;
	if ( ! isset( $_SERVER['HTTP_REFERER'] ) || ! is_string( $_SERVER['HTTP_REFERER'] ) ) {
		$same_site = false;
	}
	else {
		$ref_url = $_SERVER['HTTP_REFERER'];
		$home_url = get_home_url();
		if ( parse_url( $home_url, PHP_URL_HOST ) !== parse_url( $ref_url, PHP_URL_HOST ) ) {
			$same_site = false;
		}
		else if ( parse_url( $home_url, PHP_URL_PORT ) !== parse_url( $ref_url, PHP_URL_PORT ) ) {
			$same_site = false;
		}
		else if ( ! preg_match( '/^'.ml_get_urlbase_for_re(true).'(\/|$)/', parse_url( $ref_url, PHP_URL_PATH ) ) ) {
			$same_site = false;
		} // 'null' will be treated as "";
	}
	return $same_site;
}

/* Get the preferred languages sent by the browser */
function ml_get_accept_langs()
{
	$accept_langs = array(); // An array like "en-gb" => 1.0
	if ( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) && is_string( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
		$lang_items = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
		foreach ( $lang_items as $lang_item ) {
			$lang_parts = explode(';', $lang_item);
			$m_q = array();
			if (count( $lang_parts ) > 1) preg_match('/\s*q\s*=\s*(.*)\s*/', $lang_parts[1], $m_q);
			$q_num = (count($m_q) > 1) ? (float) $m_q[1] : 1.0; // set default to 1 for any without q factor
			if ( trim($lang_parts[0]) && $q_num > 0.0 ) $accept_langs[trim($lang_parts[0])] = $q_num;
		}
	}
	arsort( $accept_langs );
	return $accept_langs;
}

if ( isset( $ml_opt_obj->ml_auto_redirect ) && $ml_opt_obj->ml_auto_redirect === true
		&& ! ml_get_locale() && ! ml_from_same_site() ) {
	ml_redirect_by_language_matching();
}

/* Redirect according to the browser's preferred languages when the front page is requested */
function ml_redirect_by_language_matching()
{
	global $wp_query, $ml_registered_locales, $ml_registered_mlangs;
	/*	if ( is_home() || is_front_page() ) { // Not usable before 'parse_query' action,
	   'is_front_page' can even return wrong value before 'get_posts()' finishes, bug of WP? */
	if ( ml_is_wp_home() ) {
		$accept_langs = ml_get_accept_langs();
		if ( count($accept_langs) < 1 || count($ml_registered_locales) < 2 ) return;

		/* Match the first accept-language with the default registered language */
		if ( ml_compare_langs(key($accept_langs), $ml_registered_locales[0]) > 0 ) return;

		/* Match the first accept-language with the non-default registered languages */
		$best_match = array('lang'=>'', 'val'=>0);
		foreach ($ml_registered_mlangs as $reg_mlang) {
			$val = ml_compare_langs( key($accept_langs), $reg_mlang->locale );
			if ( $val > $best_match['val'] ) {
				$best_match['lang'] = $reg_mlang;
				$best_match['val'] = $val;
			}
		}
		if ( $best_match['val'] > 0 ) {
			ml_redirect_browser_to_language( $best_match['lang'] );
			return;
		}

		/* Match the second to last accept-languages with the default registered language */
		foreach ( array_slice($accept_langs,1) as $lang=>$val ) {
			if ( ml_compare_langs($lang, $ml_registered_locales[0]) > 0 ) return;
		}

		/* Match the second to last accept-languages with the non-default registered languages */
		foreach ( array_slice($accept_langs,1) as $lang=>$val ) {
			foreach ( $ml_registered_mlangs as $reg_mlang ) {
				if ( ml_compare_langs($lang, $reg_mlang->locale) > 0 ) {
					ml_redirect_browser_to_language( $reg_mlang );
					return;
				}
			}
		}
	}
}

function ml_redirect_browser_to_language( $lang )
{
	add_action( 'plugins_loaded', function() use($lang) { // Wait for the More-Lang Pro
		ml_redirect_browser_to_language_impl( $lang );
	} );
}

/* Redirect the browser to the specified language */
function ml_redirect_browser_to_language_impl( $lang )
{
	$home_uri = parse_url( home_url("/") )['path'];
	$filtered_home_uri = apply_filters('morelang_map_to_locale_url', $home_uri, $lang->locale);
	$filtered_home_uri = preg_replace( '/^' . preg_quote($home_uri, '/') . '/', '', $filtered_home_uri );
	$target_url = home_url( $filtered_home_uri, isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : null );
	$target_url = ml_add_lang_to_url( $target_url, $lang->url_locale );
	// wp_redirect( ... ); // Unavailable before 'plugins_loaded' action ( see "pluggable.php" ).
	global $is_IIS;
	if ( !$is_IIS && PHP_SAPI != 'cgi-fcgi' ) status_header(302);
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	header("Location: $target_url", true, 302);
	nocache_headers();
	exit;
}


if ( isset( $ml_opt_obj->ml_gen_hreflang ) && $ml_opt_obj->ml_gen_hreflang === true ) {
	add_action( 'wp_head', 'morelang\ml_wp_head_hreflang_tag', 1 );
}

/* Generate hreflang tags, which are useful for multilingual SEO */
function ml_wp_head_hreflang_tag()
{
	global $ml_registered_langs;
	ml_set_urlmode_to_qry_if_needed();

	$request_url = ml_get_request_nolang_url();
	$default_url = '';
	foreach ($ml_registered_langs as $idx=>$lang) {
		$hreflang = str_replace('_', '-', strtolower($lang->locale));
		$filtered_request_url = apply_filters('morelang_map_to_locale_url', $request_url, $idx==0 ? '' : $lang->locale);
		$target_url = $filtered_request_url;
		if ( $idx >= 1 ) {
			$target_url = ml_add_lang_to_url( $target_url, $lang->url_locale );
		}
		else {
			$default_url = $filtered_request_url;
		}
		printf('<link rel="alternate" hreflang="%s" href="%s" />' . "\n", $hreflang, esc_url($target_url));
	}
	printf('<link rel="alternate" hreflang="x-default" href="%s" />' . "\n", esc_url($default_url));

	ml_restore_urlmode_if_needed();
}


/*
 * Shortcode for adding language to a URL. Usage:
 * "[morelang-url-addlang]/example/[/morelang-url-addlang]", or
 * "[morelang-url-addlang fullpath]http://www.example.com/example/[/morelang-url-addlang]"
 */
function ml_url_add_lang_shortcode( $atts, $content = '/' ) {
	$fullp = is_array($atts) && (in_array('fullpath', $atts) || isset($atts['fullpath']) && $atts['fullpath']=='true');
	$ret = trim( $content );
	if (! $fullp) $ret = home_url( $ret );
	$ret = ml_add_lang_to_url( $ret );
	return $ret;
}
add_shortcode( 'morelang-url-addlang', 'morelang\ml_url_add_lang_shortcode' );
