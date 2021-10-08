<?php
namespace morelang;
/* The configuration page of compatibility with Plugins & Themes in some special cases */


/* Enqueue scripts required by the page */
global $plugin_page;
if ( isset($plugin_page) && string_ends_with($plugin_page, ML_SPECIAL_MENUSLUG) ) {
	add_action('admin_enqueue_scripts', 'morelang\ml_special_scripts', 10);
}
function ml_special_scripts() {
	ml_enqueue_select2();
	wp_enqueue_style( 'morelang_style_special', ML_PLUGIN_URL . 'special/assets/morelang_special.css', array(), ML_VER );
	wp_enqueue_script('morelang_js_special', ML_PLUGIN_URL . 'special/assets/morelang_special_cfg.js', array('morelang_js', 'morelang_js_repos'), ML_VER, true);
}


/* The configuration form
 */
define('ML_SPECIAL_GRP', 'morelang-special-group');
function ml_special_option() {
	$ml_special_opt = ml_get_special_opt();
	echo "\n<script>";
	echo 'var ml_special_opt = ' . wp_json_encode($ml_special_opt) . ";\n";

	$excl_metas = ['_edit_lock', '_edit_last', '_pingme', '_encloseme', '_thumbnail_id', '_virtual',
			'_menu_item_classes', '_menu_item_menu_item_parent', '_menu_item_object', '_menu_item_object_id',
			'_menu_item_target', '_menu_item_type', '_menu_item_url', '_menu_item_xfn',
			'_wp_attached_file', '_wp_attachment_image_alt', '_wp_attachment_metadata', '_wp_page_template',
	]; // Known metas that are not expected to be translated
	global $wpdb;
	$qry = "SELECT DISTINCT meta_key FROM {$wpdb->postmeta} WHERE meta_key LIKE '\_%' ORDER BY meta_key";
	$meta_keys = $wpdb->get_col( $qry );
	if ( is_array($meta_keys) ) {
		foreach ($meta_keys as $key=>$val) {
			if ( strpos($val, ML_UID) !== FALSE ) unset( $meta_keys[$key] );
		}
		$ml_special_protected_post_meta_keys =  array_diff($meta_keys, $excl_metas);
		echo 'var ml_special_protected_post_meta_keys = ' . wp_json_encode($ml_special_protected_post_meta_keys) . ";\n";
	}

	echo "</script>\n";
?>
	<div id="ml-opt-wrap" class="ml-special-cfg">
		<div id="ml-opt-heading"><h2><?php echo esc_html__( 'More-Lang Compatibility with Plugins & Themes in some Special Cases', ML_TDOMAIN ) ?></h2></div>

		<table id="ml-langcfg-tbl">
			<tbody>
			</tbody>

			<tfoot>
				<tr><td>
					<fieldset class="ml-special-postmeta ml-tooltip-wider"><legend><?php esc_html_e( 'Post metas support', ML_TDOMAIN ) ?>
					<?php ml_tooltip( __('Some Plugins & Themes add Post metas, which are not directly supported by More-Lang.', ML_TDOMAIN)
							. __(' However, More-Lang supports the built-in Custom Fields panel.', ML_TDOMAIN)
							. __(' If any Post meta is not shown in the Custom Fields panel, you can add it here, then,', ML_TDOMAIN)
							. __(' if it exists in the Post being edited, it will be shown in the Custom Fields panel, and ready to be translated.', ML_TDOMAIN) ) ?>
					</div>
					</legend>
					<div class="ml-directive-label">
					<?php esc_html_e( 'Select or input the Post metas expected to be shown in the Custom Fields panel:', ML_TDOMAIN ) ?>
					</div>
					<select id="ml-special-post-meta-keys" multiple="multiple" style="width:100%"></select>
					</fieldset>

					<fieldset><legend><?php esc_html_e( 'Missing More-Lang inputs', ML_TDOMAIN ) ?>
					<?php ml_tooltip( __('Few Plugins will remove the More-Lang inputs in some modules.', ML_TDOMAIN)
								. __(' By setting this section, you can enter all language versions in the default inputs of the modules affected.', ML_TDOMAIN) ) ?>
					</legend>
					<span class="ml-directive-label"><?php esc_html_e( 'Modules affected:', ML_TDOMAIN ) ?></span>
					<span class="ml-radio-grp">
					<input type="checkbox" id="ml-special-menu">
					<label for="ml-special-menu"><?php esc_html_e( 'Menus', ML_TDOMAIN ) ?></label>
					<?php ml_tooltip( __('Only "Navigation Label" & "Title Attribute" will be processed.', ML_TDOMAIN)
							.  __(' Do not use it in other palces like embeded Widgets, etc.', ML_TDOMAIN) ) ?>
					&nbsp;&nbsp;&nbsp;<input type="checkbox" id="ml-special-widget">
					<label for="ml-special-widget"><?php esc_html_e( 'Widgets', ML_TDOMAIN ) ?></label>
					<?php ml_tooltip( __('Only "Title" will be processed.', ML_TDOMAIN) ) ?>
					</span>
					<br><span class="ml-directive-label"><?php esc_html_e( 'Text delimiter:', ML_TDOMAIN ) ?></span>
					<span class="ml-radio-grp ml-opt-gap">
					<input id="ml-special-delimiter-pipe" type="radio" name="ml-special-delimiter" value="<?php echo ML_SPECIAL_DELIMITER_PIPE ?>" checked>
					<label for="ml-special-delimiter-pipe">|||</label>
					<span class="ml-inner-comment">(<?php esc_html_e( 'e.g.', ML_TDOMAIN ) ?>, text1|||text2|||text3)</span>
					</span>
					</fieldset>

					<fieldset class="ml-tooltip-wider"><legend><?php esc_html_e( 'Link filters for adding language', ML_TDOMAIN ) ?>
					<?php ml_tooltip( __('If a link is missing language, there can be various reasons.', ML_TDOMAIN)
							. __(' One possible reason is that the link is not filtered by More-Lang.', ML_TDOMAIN)
							. __(' If you identify the link which should be filtered, you can add it here.', ML_TDOMAIN)
							. __(' (E.g., if you see ', ML_TDOMAIN) . '<em>apply_filters( \'xxx_link\', $link, ... )</em>, then add <em>xxx_link</em>)' ) ?>
					</div>
					</legend>
					<div class="ml-directive-label">
					<?php esc_html_e( 'Input the links expected to be filtered (separated by ', ML_TDOMAIN ); echo '<q>,</q>):' ?>
					</div>
					<input id="ml-special-filter-links" type="text" style="width:100%"></input>
					</fieldset>
				</td></tr>
			</tfoot>
		</table>

		<form id="ml-special-form" method="post" action="options.php">
			<?php
			settings_fields(ML_SPECIAL_GRP);
			do_settings_sections(ML_SPECIAL_GRP);
			?>
			<input type="hidden" id="morelang_special_opt" name="<?php echo ML_UID ?>special_option" value="<?php echo esc_attr(wp_json_encode($ml_special_opt)); ?>" />
			<?php submit_button(); ?>
		</form>
	</div>

<?php
	add_filter('admin_footer_text', 'morelang\ml_admin_footer_text');
	add_filter('update_footer', 'morelang\ml_update_footer');
}


add_action('admin_init', 'morelang\ml_cfg_register_special_settings');
function ml_cfg_register_special_settings() { // whitelist options
	register_setting(ML_SPECIAL_GRP, ML_UID . 'special_option');
}

add_filter('pre_update_option_' . ML_UID . 'special_option', 'morelang\ml_cfg_pre_update_special_option', 10, 2);
function ml_cfg_pre_update_special_option( $value, $old_value ) {
	if ( strlen($value) > 10000 ) {
		return $old_value;
	}
	return $value;
}

add_filter('sanitize_option_' . ML_UID . 'special_option', 'morelang\ml_cfg_sanitize_special_option', 10, 3);
function ml_cfg_sanitize_special_option( $value, $option, $original_value = '' ) {
	if ( $value === NULL ) return NULL;
	if ( ! is_string($value) ) return '';
	return wp_kses( $value, wp_kses_allowed_html('post') ); // Strip Javascript
}
