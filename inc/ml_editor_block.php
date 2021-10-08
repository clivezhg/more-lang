<?php
namespace morelang;
/* The support for the Gutenberg editor introduced in Wordpress 5.0 */


/* Save the localized versions of a Post in the Gutenberg editor */
function ml_save_localized_gutenberg_post( $req ) {
	require_once 'ml_editor.php'; // Require the 'ml_save_post' function
	$params_o = $req->get_params(); // another approach: 'json_decode( $req->get_body() );'
	if ( is_array( $params_o ) ) {
		$params = wp_slash( $params_o ); // 'int' will be converted to 'string'
		if ( isset($params['title']) && is_array($params['title']) ) {
			foreach( $params['title'] as $locale=>$val ) {
				$_POST['post_title_' . $locale] = $val;
			}
		}
		if ( isset($params['content']) && is_array($params['content']) ) {
			foreach( $params['content'] as $locale=>$val ) {
				$_POST['content_' . $locale] = $val;
			}
		}
		if ( isset($params['excerpt']) && is_array($params['excerpt']) ) {
			foreach( $params['excerpt'] as $locale=>$val ) {
				$_POST['excerpt_' . $locale] = $val;
			}
		}
		if ( isset($params['post_id']) && is_numeric($params['post_id']) ) {
			ml_save_post( (int)$params['post_id'], NULL );
			$meta_ret = ml_save_post_metas( $params );

			/* Update the modified time(WP itself also update without post change), so the revision will not be confusing */
			global $wpdb;
			$post_modified = current_time( 'mysql' );
			$post_modified_gmt = current_time( 'mysql', 1 );
			$data = compact( 'post_modified', 'post_modified_gmt' );
			$where = array( 'ID' => (int)$params['post_id'] );
			if ( false === $wpdb->update( $wpdb->posts, $data, $where ) ) {
				if (! is_wp_error( $meta_ret )) {
					$meta_ret = new WP_Error('db_update_error', $wpdb->last_error);
				}
			}
			/* Save a revision (the function will not create if no change) */
			wp_save_post_revision( $params['post_id'] );

			$resp = new \stdClass();
			$resp->message = __( 'Succeeded', ML_TDOMAIN );
			$resp->code = 0;
			if ( is_wp_error( $meta_ret ) ) {
				$resp->message = $meta_ret->get_error_message();
				$resp->code = 1;
			}
			return $resp;
		}
	}
}


/* Save the localized post metas. */
function ml_save_post_metas( $params ) {
	$post_type = 'post';
	if ( !empty( $params['post_type'] ) ) $post_type = $params['post_type'];
	$wp_meta = new \WP_REST_Post_Meta_Fields( $post_type );
	$object_type = 'post'; // See 'WP_REST_Post_Meta_Fields::get_meta_type()';

	if ( isset( $params['meta'] ) && is_array( $params['meta'] ) ) {
		$post_metas = array();
		$object_subtype = get_object_subtype( $object_type, $params['post_id'] );

		foreach( $params['meta'] as $locale=>$metas ) {
			foreach ( $metas as $key=>$val ) {
				$ml_meta_key = ml_postmeta_name($key, $locale);
				$post_metas[$ml_meta_key] = $val;
				$dft_auth_filter = "auth_{$object_type}_meta_{$key}"; // The default filter added by the other Plugin|Theme
				$auth_filter = "auth_{$object_type}_meta_{$ml_meta_key}";
				if (! empty($object_subtype)) {
					if ( has_filter("{$dft_auth_filter}_for_{$object_subtype}") ) {
						$dft_auth_filter .= "_for_{$object_subtype}";
						$auth_filter .= "_for_{$object_subtype}"; // Follow the default
					}
				}
				if (! has_filter($dft_auth_filter)) $dft_auth_filter = '';

				$ml_auth_meta = function($allowed, $meta_key, $object_id, $user_id, $cap, $caps) use($key, $dft_auth_filter) {
					if ( empty($dft_auth_filter) ) { // Just in case it happens (but it should not happen)
						return current_user_can('edit_post', $object_id ) && current_user_can('edit_post_meta', $object_id);
					}
					return apply_filters($dft_auth_filter, $allowed, $key, $object_id, $user_id, $cap, $caps);
				};

				add_filter( $auth_filter, $ml_auth_meta, 10, 6 );
			}
		}
		
		return $wp_meta->update_value( $post_metas, $params['post_id'] );
	}
}


/* When 'register_meta(...)', "If `auth_callback` is not provided, fall back to `is_protected_meta()`",
    then the 'auth_callback' of More-Lang metas will be set to the '__return_false' function,
    but the result will be overridden by '$ml_auth_meta' */
/* Register the More-Lang metas, then they can be saved. */
function ml_register_meta_args( $args, $defaults, $object_type, $meta_key ) {
	global $ml_registered_mlocales;
	if ( strpos( $meta_key, ML_UID ) > 0 ) return $args;
	foreach ($ml_registered_mlocales as $mlocale) {
		$ml_meta_key = ml_postmeta_name($meta_key, $mlocale);
		register_meta( $object_type, $ml_meta_key, $args );
	}
	return $args;
}
if ( strpos($_SERVER['REQUEST_URI'], '/morelang/gutenberg-post') > 0 ) {
	add_filter( 'register_meta_args', 'morelang\ml_register_meta_args', 10, 4 );
}


/* Setup the More-Lang REST API. */
add_action('rest_api_init', function() {
	register_rest_route( 'morelang', '/gutenberg-post' . '/(?P<post_id>[\d]+)', [
			'args' => [
				'post_id' => [	'type' => 'integer' ]
			],
			[
				'methods' => 'PUT',
				'callback' => 'morelang\ml_save_localized_gutenberg_post',
				'permission_callback' => function() {
					return current_user_can('edit_posts');
				},
			]
		]
	);
});


/* Generate localized Post texts as Javascript values */
add_action( 'enqueue_block_editor_assets', 'morelang\ml_enqueue_block_editor_assets' );
function ml_enqueue_block_editor_assets() {
	global $post, $ml_registered_mlocales;
	if ( isset($post->ID) && is_int($post->ID) ) {
		$locale_titles = array();
		$locale_contents = array();
		$locale_excerpts = array();
		$metas = get_post_meta( $post->ID, '', TRUE ); // TRUE is skipped for ''.
		$locale_metas = array();
		foreach ( $ml_registered_mlocales as $mlocale ) {
			$cur_metas = array();
			foreach ($metas as $key=>$val) {
				if ( is_array($val) ) $val = count($val) > 0 ? $val[0] : NULL;
				if ( $val === NULL ) continue;
				if ( string_ends_with($key, '_' . ML_UID . $mlocale) ) {
					$len1 = strlen($key);
					$len2 = strlen(ML_UID . $mlocale);
					$key_o = substr($key, 1, $len1-$len2-2);
					switch ( $key_o ) {
					case 'post_title':
						$locale_titles[$mlocale] = $val;
						break;
					case 'post_content':
						$locale_contents[$mlocale] = $val;
						break;
					case 'post_excerpt':
						$locale_excerpts[$mlocale] = $val;
						break;
					default:
						$cur_metas[$key_o] = $val;
					}
				}
			}
			if ( count($cur_metas) > 0 ) $locale_metas[$mlocale] = $cur_metas;
		}
		$ml_post_data = array();
		if ( count($locale_titles) > 0 ) {
			$ml_post_data['locale_titles'] = $locale_titles;
		}
		if ( count($locale_contents) > 0 ) {
			$ml_post_data['locale_contents'] = $locale_contents;
		}
		if ( count($locale_excerpts) > 0 ) {
			$ml_post_data['locale_excerpts'] = $locale_excerpts;
		}
		if ( count($locale_metas) > 0 ) {
			$ml_post_data['locale_metas'] = $locale_metas;
		}
		wp_add_inline_script(
			'wp-api-fetch',
			'var ml_post_data=' . wp_json_encode( $ml_post_data ),
			'after'
		);
	}
}


/* Skip non-default languages for autosaving. */
add_filter('rest_pre_dispatch', 'morelang\ml_rest_pre_dispatch', 10, 3);
function ml_rest_pre_dispatch( $null_val, $this_obj, $request ) {
	if (method_exists($request, 'get_body')) {
		$data = json_decode( $request->get_body() );
		if ( isset($data->morelang_non_default) && $data->morelang_non_default ) {
			$obj = new \stdClass();
			$obj->data = new \stdClass();
			$obj->data->status = 200;
			return $obj;
		}
	}

	return NULL;
}
