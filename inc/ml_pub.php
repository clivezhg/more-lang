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
	$ml_option = get_option( ML_UID . 'option' );
	if ( $ml_option ) {
		if (! is_string($ml_option) || strlen($ml_option) > 60000) $ml_option = '';
		$ml_opt_obj = json_decode( $ml_option );
		if (! is_object($ml_opt_obj)) $ml_opt_obj = new \stdClass();
		if ( isset($ml_opt_obj->ml_registered_langs) ) {
			ml_sanitize_langs( $ml_opt_obj->ml_registered_langs );
		}
		$ml_registered_langs = isset($ml_opt_obj->ml_registered_langs) ? $ml_opt_obj->ml_registered_langs : array();
		/* if ( count($ml_registered_langs) > 0 ) {
			$ml_skip_locale_filter = TRUE;
			$ml_registered_langs[0]->locale = get_locale(); // always use the Site Language as the default locale
			$ml_skip_locale_filter = FALSE;
		} */
	}
	else {
		$ml_opt_obj = new \stdClass();
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


/*
 * Sanitize the fetched language options, just in case that the DB is hacked.
 */
function ml_sanitize_langs( &$langs ) {
	if ( is_array($langs) ) {
		$old_cnt = count( $langs );
		$non_loc_c = '/[^a-zA-Z0-9_]/'; // Characters not allowed in Locale
		$non_urlloc_c = '/[^a-zA-Z0-9_-]/'; // Characters not allowed in the Locale part of a URL
		foreach ($langs as $idx=>$lang) {
			if (isset($lang->locale) && is_string($lang->locale) && preg_match($non_loc_c, $lang->locale)) {
				$lang->locale = preg_replace($non_loc_c, '_', $lang->locale);
			}
			if (! isset($lang->locale) || ! is_string($lang->locale) || ! $lang->locale
					|| strlen($lang->locale) > 60) {
				unset( $langs[$idx] );
				continue;
			}
			if (isset($lang->url_locale) && is_string($lang->url_locale) && preg_match($non_urlloc_c, $lang->url_locale)) {
				$lang->url_locale = preg_replace($non_urlloc_c, '_', $lang->url_locale);
			}
			if (! isset($lang->url_locale) || ! is_string($lang->url_locale) || ! $lang->url_locale
					|| strlen($lang->url_locale) > 60) {
				$lang->url_locale = $lang->locale;
			}

			if (isset($lang->name) && is_string($lang->name)) $lang->name = esc_attr( $lang->name );
			else $lang->name = '';
			if (isset($lang->label) && is_string($lang->label)) $lang->label = esc_attr( $lang->label );
			else $lang->label = '';

			if (! isset($lang->flag) || ! is_string($lang->flag)) {
				$lang->flag = '';
			}
			if (! preg_match('/^[a-zA-Z0-9_-]{1,8}[.][a-zA-Z_]{1,5}$/', $lang->flag)) {
				$lang->flag = preg_replace('/\s/', '%20', $lang->flag);
				$lang->flag = esc_attr( $lang->flag );
			}

			/* Script should not be in 'missing_content' */
			if (isset($lang->moreOpt->missing_content) && is_string($lang->moreOpt->missing_content)
					&& trim($lang->moreOpt->missing_content)) {
				$lang->moreOpt->missing_content = wp_kses( $lang->moreOpt->missing_content, wp_kses_allowed_html('post') );
			}
		}

		if (count($langs) != $old_cnt) $langs = array_values( $langs ); // Re-index if removed.
	}

	return $langs;
}


/**
 * Get the default language.
 */
function ml_get_dft_lang() {
	global $ml_registered_langs;
	if ( count($ml_registered_langs) > 0 ) return $ml_registered_langs[0];
	return NULL;
}


/**
 * Get the translation object.
 */
function ml_get_translation() {
	if ( isset($GLOBALS['ml_trans_obj']) ) return $GLOBALS['ml_trans_obj'];
	$ml_ret_obj = new \stdClass();
	$ml_translation = get_option( ML_UID . 'translation' );
	if ( $ml_translation ) {
		$ml_trans_obj = json_decode( $ml_translation );
		if ( is_object($ml_trans_obj) ) $ml_ret_obj = $ml_trans_obj;
		$GLOBALS['ml_trans_obj'] = $ml_ret_obj;
	}
	return $ml_ret_obj;
}


add_filter( 'morelang_translate_text', 'morelang\ml_translate_text', 1, 1 );
/**
 * The interface to get localized text for the More-Lang Translating module.
 * Use 'apply_filters("morelang_translate_text", $dft_text)' if loose bounding is prefered. */
function ml_translate_text( $text ) {
	$locale = ml_get_locale();
	if ( $locale ) {
		$ml_trans_obj = ml_get_translation();
		if ( isset($ml_trans_obj->$text->$locale ) ) {
			return $ml_trans_obj->$text->$locale;
		}
	}
	return $text;
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


/**
  * Whether the front page is being requested.
  * WP's 'is_home' & 'is_front_page' are mainly for theme templates, not usable before 'get_posts' is done.
  */
function ml_is_wp_home() {
	global $ml_base_url;
	if ( isset( $_SERVER['REQUEST_URI'] ) ) {
		$url_base =  rtrim( parse_url($ml_base_url, PHP_URL_PATH), '/\\' ); // no ending '/'
		$req_uri = rtrim( $_SERVER['REQUEST_URI'], '/\\' );
		if ( $req_uri === $url_base ) {
			if ( ! isset( $_SERVER['QUERY_STRING'] ) || ! trim( $_SERVER['QUERY_STRING'] ) ) return true;
		}
	}
	return false;
}


$ml_requested_locale = '';
$ml_requested_url_locale = '';
$ml_requested_lang = NULL;
/* Set the requested language by the frontend */
function ml_set_requested_lang() {
	global $ml_requested_locale, $ml_requested_url_locale, $ml_requested_lang, $ml_registered_langs, $ml_registered_mlangs, $ml_opt_obj;
	ml_set_urlmode_to_qry_if_needed();

	if ( isset($ml_opt_obj->ml_url_mode) && $ml_opt_obj->ml_url_mode === ML_URLMODE_QRY ) {
		if ( isset( $_GET['lang'] ) )	{
			$ml_requested_url_locale = trim( $_GET['lang'] );
		}
	}
	else {
		$req_uri = $_SERVER['REQUEST_URI'];
		$url_base = ml_get_urlbase_for_re( TRUE );
		preg_match( '/' . $url_base . '\/([a-zA-Z_-]+)(\/|$|[?])' . '/i', $req_uri, $matches );
		if ( count($matches) > 1 ) {
			$ml_requested_url_locale = trim( $matches[1] );
		}
	}
	$exist = FALSE;
	foreach ( $ml_registered_mlangs as $lang_obj ) {
		if ( $lang_obj->url_locale === $ml_requested_url_locale ) {
			$ml_requested_locale = $lang_obj->locale;
			$ml_requested_lang = $lang_obj;
			$exist = TRUE;
			break;
		}
	}
	if (! $exist) $ml_requested_url_locale = '';

	/* For RTL, if the corresponding ".mo" is not installed, 'rtl' will not be set, see "class-wp-locale.php#init()" */
	if ( ! is_admin() ) {
		$lang_o = $ml_requested_lang;
		if ( ! $lang_o && count($ml_registered_langs) > 0 ) $lang_o = $ml_registered_langs[0];
		if ( isset($lang_o->moreOpt->isRTL) && $lang_o->moreOpt->isRTL === TRUE ) {
			$GLOBALS['text_direction'] = 'rtl';
		}
	}

	ml_restore_urlmode_if_needed();
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


/* // Directly setting $GLOBALS['text_direction'] is used to be more efficient, see 'ml_set_requested_lang'
add_filter( 'gettext_with_context', 'morelang\ml_gettext_with_context', 1, 4 );
function ml_gettext_with_context( $translation, $text, $context, $domain ) {
	global $ml_requested_lang, $ml_registered_langs, $pagenow;
	if ( $pagenow !== 'index.php' ) return $translation;
	if ( $text === 'ltr' && $context === 'text direction' ) {
		$lang_obj = $ml_requested_lang;
		if ( ! $lang_obj && count($ml_registered_langs) > 0 && ! is_admin() ) $lang_obj = $ml_registered_langs[0];
		if ( isset($lang_obj->moreOpt->isRTL) && $lang_obj->moreOpt->isRTL === TRUE ) {
			return 'rtl';
		}
	}
	return $translation;
} */


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
	return '_' . $clean_name . '_' . ML_UID . $locale;
}


/**
 * Get language label for the admin panels. (the 'Label' on the plugin setting screen is for the visitors)
 * @param object $lang, a language object
 * @return string
 */
function ml_get_admin_lang_label($lang) {
	global $ml_registered_langs;
	$label = ml_get_admin_lang_label_base($lang);
	$idx = 0;
	foreach ( $ml_registered_langs as $alang ) {
		if ( $label === ml_get_admin_lang_label_base($alang) ) $idx++;
		if ( isset($lang->locale, $alang->locale) && $lang->locale === $alang->locale ) break;
	}
	if ($idx > 1) $label = $label . (string)$idx;
	return $label;
}
function ml_get_admin_lang_label_base($lang) {
	global $ml_opt_obj, $ml_registered_langs;
	if ( isset($ml_opt_obj->ml_short_label) && $ml_opt_obj->ml_short_label ) {
		$locale = '';
		if ( isset($lang->locale) ) $locale = $lang->locale;
		if ( count($ml_registered_langs) > 9) {
			$parts = explode('_', $locale);
			return $parts[0];
		}
		else return $locale;
	}
	else return $lang->name;
}


/** In some cases, if "ML_URLMODE_PATH" is used, the appended locale will cause WP fail to locate the resource.
    (Basically, if the '$pagenow' is not '/index.php', the function should apply the change) */
function ml_set_urlmode_to_qry_if_needed() {
	global $ml_opt_obj, $old_url_mode, $pagenow;
	if ( $pagenow !== 'wp-login.php' ) return;
	$old_url_mode = isset($ml_opt_obj->ml_url_mode) ? $ml_opt_obj->ml_url_mode : NULL;
	$ml_opt_obj->ml_url_mode = ML_URLMODE_QRY;
}
/** It must be used in pair with 'ml_set_urlmode_to_qry_if_needed'. */
function ml_restore_urlmode_if_needed() {
	global $ml_opt_obj, $old_url_mode;
	if ( isset($old_url_mode) && is_string($old_url_mode) ) $ml_opt_obj->ml_url_mode = $old_url_mode;
}


/** Return whether the $val is valid text input by user */
function ml_valid_text( $val ) {
	/* !='' is less strict, but can be used when the $val must be in string|boolean|NULL&0&0.0 */
	return is_string($val) && $val !== '';
}
