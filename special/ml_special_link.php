<?php
namespace morelang;
/* Filter links to add language */


$ml_filter_links = explode(',', $special_opt->ml_special_filter_links);
if (! is_array($ml_filter_links)) return;

foreach ( $ml_filter_links as $link ) {
	$link = trim( $link );
	if ( $link ) {
		add_filter( $link, 'morelang\ml_add_lang_to_url', 1000 );
	}
}
