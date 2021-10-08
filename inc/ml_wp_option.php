<?php
namespace morelang;
/* The management of Wordpress related options. */


/* Add additional general options to 'whitelist_options' */
add_filter( 'whitelist_options', 'morelang\ml_whitelist_options', 10, 1 );
function ml_whitelist_options( $whitelist_options ) {
	global $ml_registered_mlocales;
	if ( isset($whitelist_options['general']) ) {
		foreach ( $ml_registered_mlocales as $mlocale ) {
			$whitelist_options['general'][] = ML_UID . 'blogname_' . $mlocale;
			$whitelist_options['general'][] = ML_UID . 'blogdescription_' . $mlocale;
		}
	}
	return $whitelist_options;
}
