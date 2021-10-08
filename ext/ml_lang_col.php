<?php
namespace morelang;
/* Adds "Language" column to the Posts lists to show the translation status of each Post. */


global $ml_opt_obj;
if ( ! isset($ml_opt_obj->ml_not_add_posts_column) || $ml_opt_obj->ml_not_add_posts_column !== true ) {
	add_action( 'registered_post_type', 'morelang\ml_register_post_column_filter', 10, 2 );
}
function ml_register_post_column_filter( $post_type, $post_type_object ) {
	add_filter( "manage_edit-{$post_type}_columns", 'morelang\ml_add_post_column', 10, 1 );
	add_action( "manage_{$post_type}_posts_custom_column", 'morelang\ml_show_post_column', 10, 2 );
}

/* Filters the column headers for a Post list table to add the "Languange" column. */
function ml_add_post_column( $columns ) {
	$col_title = __('This column is added by More-Lang to show the translation status of each Post.', ML_TDOMAIN);
	$col_title = esc_attr( $col_title );
	$columns['ml_lang_col'] = "<span title='$col_title'>" . __('Language', ML_TDOMAIN) . '</span>';
	return $columns;
}

/* Shows the translation status of a Post in a Posts list table. */
function ml_show_post_column( $column, $post_id ) {
	global $ml_registered_mlangs;
	if ( $column !== 'ml_lang_col' ) return;
	foreach ( $ml_registered_mlangs as $lang ) {
		$content_meta = get_post_meta( $post_id, ml_postmeta_name('post_content', $lang->locale), TRUE );
		$flag_url = plugin_dir_url( __FILE__ ) . '../cflag/' . $lang->flag;
		$img_title = esc_attr( $lang->label );
		if ( strlen(trim($content_meta)) > 0 ) {
			echo "<img src='$flag_url' title='$img_title'>";
		}
		else  {
			echo "<img class='ml-col-flag-disabled' src='$flag_url' title='$img_title'>";
		}
	}
}
