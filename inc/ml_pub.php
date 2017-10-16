<?php
namespace morelang;

require_once 'pub_util.php';

/**
 * should be called ealier
 * @global array $ml_registered_langs, will not be null.
 * @global array $ml_registered_mlangs, will not be null, skipping the default language.
 * @global array $ml_registered_locales, will not be null.
 * @global array $ml_registered_mlocales, will not be null, skipping the default language.
 * @global array $ml_registered_url_mlocales, will not be null, skipping the default language.
 * @global boolean $ml_skip_locale_filter, whether to change nothing in 'ml_theme_localized'.
 * @global object $ml_opt_obj, will not be null, 'morelang_option' object.
 */
function ml_fetch_option() {
	global $ml_registered_langs, $ml_registered_mlangs;
	global $ml_registered_locales, $ml_registered_mlocales, $ml_registered_url_mlocales;
	global $ml_skip_locale_filter, $ml_opt_obj;
	$ml_option = get_option( 'morelang_option' );
	if ( $ml_option ) {
		$ml_opt_obj = json_decode( $ml_option );
		if (! is_object($ml_opt_obj)) $ml_opt_obj = new \stdClass();
		$ml_registered_langs = isset($ml_opt_obj->ml_registered_langs) ? $ml_opt_obj->ml_registered_langs : array();
		/* if ( count($ml_registered_langs) > 0 ) {
			$ml_skip_locale_filter = TRUE;
			$ml_registered_langs[0]->locale = get_locale(); // always use the Site Language as the default locale
			$ml_skip_locale_filter = FALSE;
		} */
	}
	if ( ! is_array($ml_registered_langs) ) {
		$ml_registered_langs = array();
	}
	$ml_registered_mlangs = array();
	$ml_registered_locales = array();
	$ml_registered_mlocales = array();
	$ml_registered_url_mlocales = array();
	foreach ( $ml_registered_langs as $idx => $registered_lang ) {
		$ml_registered_locales[] = $registered_lang->locale;
		if ( $idx > 0 ) {
			$ml_registered_mlangs[] = $registered_lang;
			$ml_registered_mlocales[] = $registered_lang->locale;
			$ml_registered_url_mlocales[] = $registered_lang->url_locale;
		}
	}
}


/**
 * @global type $ml_registered_langs
 * @param type $locale_in_suffix, e.g., it can be in the form of 'title_en'.
 * @return object
 */
function ml_get_lang_by_locale_suffix($locale_in_suffix) {
	global $ml_registered_langs;
	foreach ( $ml_registered_langs as $idx => $registered_lang ) {
		if ( string_ends_with($locale_in_suffix, $registered_lang->locale) ) {
			return $registered_lang;
		}
	}
	return null;
}


function ml_init_front() {
	ml_fetch_option();
	ml_set_requested_lang();
}


$ml_base_url = get_home_url(); // it's "Site Address" on the screen, don't be confused.
/* '$only_path': e.g., "\/abc" for TRUE, "\/\/localhost\:8080\/abc" for FALSE. */
function ml_get_urlbase_for_re( $only_path = FALSE ) {
	global $ml_base_url; // put it in global to skip the filter
	$url_base = $only_path ? parse_url($ml_base_url, PHP_URL_PATH) : preg_replace('/(http:|https:)/i', '', $ml_base_url);
	$url_base = rtrim( $url_base, '/\\' );
	$url_base = preg_quote( $url_base, '/' );
	return $url_base;
}


$ml_requested_locale = '';
$ml_requested_url_locale = '';
/* Set the requested language by the frontend */
function ml_set_requested_lang() {
	global $ml_requested_locale, $ml_requested_url_locale, $ml_registered_langs, $ml_opt_obj;
	if ( isset($ml_opt_obj->ml_url_mode) && $ml_opt_obj->ml_url_mode === ML_URLMODE_QRY ) {
		if ( isset( $_GET['lang'] ) )	{
			$ml_requested_url_locale = trim( $_GET['lang'] );
		}
	}
	else {
		$req_uri = $_SERVER['REQUEST_URI'];
		$url_base = ml_get_urlbase_for_re( TRUE );
		preg_match( '/' . $url_base . '\/([a-zA-Z_-]+)(\/|$)' . '/i', $req_uri, $matches );
		if ( count($matches) > 1 ) {
			$ml_requested_url_locale = trim( $matches[1] );
		}
	}
	$exist = FALSE;
	foreach ( $ml_registered_langs as $lang_obj ) {
		if ( $lang_obj->url_locale === $ml_requested_url_locale ) {
			$ml_requested_locale = $lang_obj->locale;
			$exist = TRUE;
			break;
		}
	}
	if (! $exist) $ml_requested_url_locale = '';
}


/*
 * to change a theme locale, it must be called before load_theme_textdomain()
 */
function ml_filter_locale( $locale )
{
	global $ml_skip_locale_filter;
	global $ml_requested_locale, $ml_registered_locales;
	// if ( isset($ml_skip_locale_filter) && $ml_skip_locale_filter ) return $locale;
	if ( $ml_requested_locale )	{
		return $ml_requested_locale;
	}
	else if ( count($ml_registered_locales) > 0) {
		return $ml_registered_locales[0];
	}

	return $locale;
}
if ( ! is_admin() ) add_filter( 'locale', 'morelang\ml_filter_locale' );


/**
 * Get the registered locale being requested.
 * @param boolean $default_empty
 * @return string
 */
function ml_get_locale($default_empty = TRUE)
{
	global $ml_registered_locales;
	$ml_locale = get_locale();
	if ( in_array($ml_locale, $ml_registered_locales) ) {
		if ( $default_empty && $ml_locale === $ml_registered_locales[0] )
			$ml_locale = '';
	} else {
		$ml_locale = '';
		if ( count($ml_registered_locales) > 0) {
			$ml_locale = $ml_registered_locales[0];
		}
	}
	return $ml_locale;
}


/**
 * Generate the 'meta_key' value to be used for non-default locale.
 */
function ml_postmeta_name($clean_name, $locale) {
	// if we dislike the "_" prefix, we can choose the filter approach: "apply_filter('is_protected_meta', ...);"
	return '_' . $clean_name . '_morelang_' . $locale;
}


/**
 * Get language label for the admin panels. (the 'Label' on the plugin setting screen is for the visitors)
 * @param object $lang, a language object
 * @return string
 */
function ml_get_admin_lang_label($lang) {
	global $ml_opt_obj, $ml_registered_langs;
	if ( isset($ml_opt_obj->ml_short_label) && $ml_opt_obj->ml_short_label ) {
		$locale = '';
		if ( isset($lang->locale) ) $locale = $lang->locale;
		if ( count($ml_registered_langs) > 9) {
			$parts = explode('_', $locale);
			return array_pop( $parts );
		}
		else return $locale;
	}
	else return $lang->label;
}
