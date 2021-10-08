<?php
namespace morelang {


/* Calculate the Wordpress version number for More-Lang */
function ml_calc_wpvernum() {
	global $wp_version;
	$ml_wpvernum = 1;
	$vernums =  explode('.', $wp_version);
	if ( is_numeric($vernums[0]) ) $ml_wpvernum = $vernums[0] * 10000;
	if ( isset($vernums[1]) && is_numeric($vernums[1]) ) $ml_wpvernum = $ml_wpvernum + $vernums[1] * 100;
	if ( isset($vernums[2]) && is_numeric($vernums[2]) ) $ml_wpvernum = $ml_wpvernum + $vernums[2];
	// More-Lang doesn't care about more minor version-number
	return $ml_wpvernum;
}


function string_ends_with($str, $sub) {
	if ( ! is_string($str) || ! is_string($sub) ) return FALSE;
	$start = max(0, strlen($str) - strlen($sub));
	return substr($str, $start) === $sub;
}


} // end 'namespace morelang'.


namespace {


if ( ! function_exists( '_ml_t' ) ) :

/**
 * Translate text using the translation entered on the More-Lang Plugin Translating page.
 * @param string $default_text. The default text to be translated.
 * @param string $any Optional. It is not used by More-Lang. The themes can set it to '$domain', then can easily change to '__(...)'
 * @return string The localized text.
 */
function _ml_t( $default_text, $any='' ) {
	return morelang\ml_translate_text( $default_text );
}

endif;


} // end 'namespace'.