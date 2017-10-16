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
		if ( ! empty($lang_obj->$optname) ) return $lang_obj->$optname;
		return false;
	};
	add_filter( "pre_option_$optname", $ml_datetime_format, 10, 1 );
}


/* Use 'missing_content' for default locale if the post_content is empty. */
add_filter( 'content_pagination', 'morelang\ml_content_pagination_default', 10, 2 );
function ml_content_pagination_default($pages, $post) {
	global $ml_registered_langs;
	$locale = ml_get_locale();
	if ( empty($locale) ) {
		if ( count($pages) === 1 && empty($pages[0]) ) {
			if ( count($ml_registered_langs) > 0 && !empty($ml_registered_langs[0]->moreOpt->missing_content) )
				return array( $ml_registered_langs[0]->moreOpt->missing_content );
		}
	}
	return $pages;
}
