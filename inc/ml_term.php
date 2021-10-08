<?php
namespace morelang;
/* The management of taxonomy-terms. */


/* The saving of taxonomy-term */
add_action( 'edited_terms', 'morelang\ml_edited_terms', 10, 2 );
add_action( 'created_term', 'morelang\ml_created_term', 10, 3 );
function ml_edited_terms($term_id, $taxonomy) {
	ml_save_term_fld_vals( $term_id, $taxonomy, 'name' );
	ml_save_term_fld_vals( $term_id, $taxonomy, 'description', 'description' );
}
function ml_created_term($term_id, $tt_id, $taxonomy) {
	ml_save_term_fld_vals( $term_id, $taxonomy, 'tag-name' );
	ml_save_term_fld_vals( $term_id, $taxonomy, 'description', 'description' );
}
function ml_save_term_fld_vals($term_id, $taxonomy, $name_prefix, $fld='name') {
	global $ml_registered_mlocales;
	$term_fld_vals = array();
	$has_ml_value = FALSE;
	foreach ( $ml_registered_mlocales as $mlocale ) {
		$fld_name = $name_prefix . '_' . $mlocale;
		if ( isset( $_POST[$fld_name] ) && is_string( $_POST[$fld_name] ) ) {
			$has_ml_value = TRUE;
			if ( ! $_POST[$fld_name] ) continue; // not save empty value
			$term_fld_val = sanitize_term_field( $fld, $_POST[$fld_name], $term_id, $taxonomy, 'db' );
			// It seems wp_unslash should be ahead of sanitize_term_field, but WP behaves like this way
			$term_fld_val = wp_unslash( $term_fld_val );
			$term_fld_vals[$mlocale] = $term_fld_val;
		}
	}
	if ( $has_ml_value ) { // Has to be normal action
		$opt_name = ML_UID . 'taxonomy_' . $taxonomy . ($fld!=='name' ? "_$fld":'') .'' . '_' . $term_id;
		update_option( $opt_name, wp_json_encode( $term_fld_vals ) );
	}
}

/* Delete localized terms */
add_action( 'delete_term', 'morelang\ml_delete_term', 10, 5 );
function ml_delete_term( $term, $tt_id, $taxonomy, $deleted_term, $object_ids ) {
	delete_option( ML_UID . 'taxonomy_' . $taxonomy . '_' . $term );
	delete_option( ML_UID . 'taxonomy_' . $taxonomy . '_description_' . $term );
}


/* Generate taxonomy-terms as Javascript value */
$builtinTaxonomies = get_taxonomies();
foreach ( $builtinTaxonomies as $taxonomy) {
	add_action( "{$taxonomy}_pre_edit_form", 'morelang\ml_taxonomy_pre_edit_form', 10, 2 );
}
add_action( 'registered_taxonomy', 'morelang\ml_registered_taxonomy', 10, 3 );
function ml_registered_taxonomy( $taxonomy, $object_type, $args ) {
	add_action( "{$taxonomy}_pre_edit_form", 'morelang\ml_taxonomy_pre_edit_form', 10, 2 );
}
function ml_taxonomy_pre_edit_form( $tag, $taxonomy ) {
	$taxonomy_names = wp_json_encode( json_decode( get_option( ML_UID . "taxonomy_${taxonomy}_$tag->term_id") ) );
	$taxonomy_descs = wp_json_encode( json_decode( get_option( ML_UID . "taxonomy_${taxonomy}_description_$tag->term_id") ) );
	if ( $taxonomy_names || $taxonomy_descs ) {
		echo "<script type='text/javascript'>\n";
		if ( $taxonomy_names ) echo 'var ml_taxonomy_names = ' . ($taxonomy_names ?: 'null') . ";\n";
		if ( $taxonomy_descs ) echo 'var ml_taxonomy_descs = ' . ($taxonomy_descs ?: 'null') . ";\n";
		echo "</script>\n";
	}
}

