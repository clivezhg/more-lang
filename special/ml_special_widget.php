<?php
namespace morelang;
/* Handle the special cases of the Widgets */


/* Remove the default More-Lang filter, otherwise its results will be used */
add_action('morelang_front_loaded', function() {
	remove_filter('widget_title', 'morelang\morelang_widget_title', 1);
});


/* Generate localized widget_title */
add_filter('widget_title', 'morelang\ml_special_widget_title', 0, 3);
function ml_special_widget_title( $title, $instance = NULL, $id_base = NULL ) {
	$req_text = ml_special_get_req_part( $title );
	return $req_text;
}
