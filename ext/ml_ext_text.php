<?php
namespace morelang;


/* Get localized post meta */
add_filter('get_post_metadata', 'morelang\ml_post_metadata', 1, 4);
function ml_post_metadata( $value, $object_id, $meta_key, $single ) {
	if ( ! $meta_key ) return $value; // '' is possible, e.g., get all metas in wp-includes/post-template.php#the_meta
	$needed = in_array( $meta_key, ['_wp_attachment_image_alt'] ); // Some special cases must be filtered
	if ( ( is_protected_meta($meta_key, 'post') || strpos($meta_key, ML_UID) !== FALSE ) && !$needed ) {
		return $value;
	}
	$locale = ml_get_locale();
	if ( $locale ) {
		$custom_meta = get_post_meta( $object_id, ml_postmeta_name($meta_key, $locale), $single );
		if ($custom_meta) return $custom_meta;
	}
	return $value;
}


/* Localize list of post custom field */
add_filter('the_meta_key', 'morelang\ml_meta_key', 1, 3);
function ml_meta_key( $html, $key, $value ) {
	// the original $html: "<li><span class='post-meta-key'>$key:</span> $value</li>\n"
	if ( is_protected_meta( $key, 'post' ) || strpos( $key, ML_UID ) !== FALSE )
		return $html;
	$the_id = get_the_ID();
	$f_key = apply_filters( 'morelang_translate_text', $key );
	$locale = ml_get_locale();
	if ( $locale ) {
		$custom_meta = get_post_meta( $the_id, ml_postmeta_name($key, $locale), TRUE );
		if ($custom_meta) {
			return "<li><span class='post-meta-key'>$f_key:</span> $custom_meta</li>\n";
		}
	}
	return $html;
}


/* Localize the 'caption' in a media */
add_filter( 'wp_get_attachment_caption', 'morelang\ml_wp_get_attachment_caption', 10, 2 );
function ml_wp_get_attachment_caption($caption, $ID) {
	$locale = ml_get_locale();
	if ( $locale && is_numeric($ID) ) {
		$cap_metaname = ml_postmeta_name('post_excerpt', $locale);
		$cap_meta = get_post_meta($ID, $cap_metaname, TRUE);
		if ( $cap_meta ) return $cap_meta;
	}

	return $caption;
}


/* Localize the 'artist' & 'album' in a media */
add_filter( 'wp_get_attachment_metadata', 'morelang\ml_wp_get_attachment_metadata', 10, 2 );
function ml_wp_get_attachment_metadata($data, $ID) {
	$locale = ml_get_locale();
	if ( ! $locale || ! is_array($data) || ! is_numeric($ID) ) return $data;

	$artist_meta = get_post_meta( $ID, ml_postmeta_name('_wp_attachment_metadata_artist', $locale), true );
	if ( $artist_meta ) {
		$data['artist'] = $artist_meta;
	}
	$album_meta = get_post_meta( $ID, ml_postmeta_name('_wp_attachment_metadata_album', $locale), true );
	if ( $album_meta ) {
		$data['album'] = $album_meta;
	}

	return $data;
}


/* Localize the media-image Widget */
add_filter( 'widget_media_image_instance', 'morelang\ml_widget_media_image_instance', 10, 1 );
function ml_widget_media_image_instance( $instance ) {
	$locale = ml_get_locale();
	if ( $locale && is_numeric($instance['attachment_id']) ) {
		$alt_metaname = ml_postmeta_name('_wp_attachment_image_alt', $locale);
		$alt_meta = get_post_meta($instance['attachment_id'], $alt_metaname, TRUE);
		if ( $alt_meta ) $instance['alt'] = $alt_meta;
		$cap_meta = ml_wp_get_attachment_caption('', $instance['attachment_id']);
		if ( $cap_meta ) $instance['caption'] = $cap_meta;
	}

	return $instance;
}


add_filter( 'morelang_translate_media', 'morelang\ml_translate_media', 1, 1 );
/* The interface to translate Medias for the other plugins */
function ml_translate_media( $post ) {
	$locale = ml_get_locale();
	if ( $locale && isset($post->ID) ) {
		$id = $post->ID;
		$title_meta = get_post_meta($id, ml_postmeta_name('post_title', $locale), TRUE);
		if ( $title_meta ) $post->post_title = $title_meta;
		$excerpt_meta = get_post_meta($id, ml_postmeta_name('post_excerpt', $locale), TRUE);
		if ( $excerpt_meta ) $post->post_excerpt = $excerpt_meta;
		/* The Post metas will be translated by the 'get_post_metadata' handler 'ml_post_metadata' */
	}

	return $post;
}
