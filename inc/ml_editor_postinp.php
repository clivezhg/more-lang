<?php
namespace morelang;
/* The generation of Post inputs.
   May be disabled by add-on. */


/* Add localized input for the post_content.
   See "/wp-admin/edit-form-advanced.php" */
add_action( 'edit_form_after_editor', 'morelang\ml_edit_form_after_editor', 10, 1);
function ml_edit_form_after_editor($post) {
	global $post_type, $ml_registered_mlocales;
	if ( post_type_supports($post_type, 'editor') ) {
		foreach ( $ml_registered_mlocales as $mlocale ):
			$p_content = get_post_meta( $post->ID, ml_postmeta_name('post_content', $mlocale), TRUE );
			$p_content = sanitize_post_field( 'post_content', $p_content, $post->ID, 'edit' );
?>
	<textarea id="content_<?php echo $mlocale; ?>" name="content_<?php echo $mlocale; ?>" class="ml-local-ta" style="display:none;"><?php
		echo esc_textarea( $p_content );
	?></textarea>
<?php endforeach; }
} 


/* Add localized input for the post_title */
add_action('edit_form_after_title', 'morelang\my_edit_form_after_title', 10, 1);
function my_edit_form_after_title($post) {
	global $post_type, $ml_registered_mlangs, $title_placeholder;
	if ( post_type_supports($post_type, 'title') ) {
		foreach ( $ml_registered_mlangs as $mlang ):
			$mlocale = $mlang->locale;
			$lang_name = ml_get_admin_lang_label( $mlang );
		?>
			<div id="titlediv_<?php echo $mlocale ?>" data-langname="<?php echo $lang_name ?>">
			<div id="titlewrap_<?php echo $mlocale ?>">
			<?php
				$cur_title_placeholder = __( $title_placeholder . ' (in ' . $mlang->name . ')' );
				$p_title = get_post_meta( $post->ID, ml_postmeta_name('post_title', $mlocale), TRUE );
				$p_title = sanitize_post_field( 'post_title', $p_title, $post->ID, 'edit' );
			?>
				<label class="ml-title-prompt-text" id="title-prompt-text_<?php echo $mlocale ?>" for="title_<?php echo $mlocale; ?>"><?php echo $cur_title_placeholder; ?></label>
				<input class="ml-title-input" type="text" name="post_title_<?php echo $mlocale; ?>" size="30" value="<?php echo esc_attr( $p_title ); ?>" id="title_<?php echo $mlocale; ?>" spellcheck="true" autocomplete="off" />
			</div>
			</div><!-- /titlediv -->
	<?php
		endforeach;
	}
}


/* Generate localized excerpts as Javascript values */
add_action( 'edit_form_top', 'morelang\ml_edit_form_top', 10, 1 );
if ( version_compare( $GLOBALS['wp_version'], '3.7', '<' ) ) { // the olders have no 'edit_form_top'
	add_action( 'edit_form_after_editor', 'morelang\ml_edit_form_top', 10, 1 );
}
function ml_edit_form_top($post) {
	global $ml_registered_mlocales;
	echo '<script type="text/javascript">';
	foreach ( $ml_registered_mlocales as $mlocale ) {
		$p_excerpt = get_post_meta( $post->ID, ml_postmeta_name('post_excerpt', $mlocale), TRUE );
		// $p_excerpt = sanitize_post_field( 'post_excerpt', $p_excerpt, $post->ID, 'edit' ); // no for the json
		echo 'var ml_excerpt_' . $mlocale . ' = ' . wp_json_encode( $p_excerpt ) . '; ';
	}
	echo "</script>\n";
}
