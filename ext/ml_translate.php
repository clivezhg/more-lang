<?php
namespace morelang;
/* More-Lang translating page */


/* Enqueue scripts required by the page */
global $plugin_page;
if ( isset($plugin_page) && string_ends_with($plugin_page, ML_TRANS_MENUSLUG) ) {
	add_action('admin_enqueue_scripts', 'morelang\ml_trans_scripts', 10);
}
function ml_trans_scripts() {
	$cur_plugin_url = plugin_dir_url( dirname(__FILE__) );
	wp_enqueue_script('morelang_js_trans', $cur_plugin_url . 'ext/assets/morelang_translate.js', array('morelang_js', 'morelang_js_repos'), ML_VER, true);
}


/* More-Lang translating form.
 */
define('ML_TRANS_GRP', 'morelang-trans-group');
function ml_trans_page() {
	global $ml_registered_langs;
	$ml_trans_obj = ml_get_translation();
	/* Extension for addding texts */
	$ml_texts_to_translate = apply_filters( 'morelang_texts_to_translate', [] );
	echo "\n<script>";
	echo 'var ml_trans_obj = ' . wp_json_encode($ml_trans_obj) . ";\n";
	echo 'var ml_texts_to_translate = ' . wp_json_encode($ml_texts_to_translate) . ';';
	echo "</script>\n";
?>
	<div id="ml-trans-wrap">
		<div id="ml-trans-heading"><h2><?php echo esc_html__( 'More-Lang Plugin Translations', ML_TDOMAIN ) . ' (V' . ML_VER . ')' ?></h2><?php do_action('morelang_trans_head') ?></div>
		<div class="ml-btn-panel"><div>
			<button id="ml-add-single" class="button button-primary"><?php esc_html_e( 'Add Single Line', ML_TDOMAIN ) ?></button>
			<button id="ml-add-multi" class="button button-primary"><?php esc_html_e( 'Add Multi Lines', ML_TDOMAIN ) ?></button>
			<button id="ml-del-selected" class="button button-primary" disabled><?php esc_html_e( 'Delete Selected', ML_TDOMAIN ) ?></button>
		</div><?php submit_button(NULL, 'primary', 'submit_tr', FALSE, ['id'=>'submit_tr', 'onclick'=>'jQuery([id=submit]).click()']) ?>
		</div>

		<table id="ml-trans-tbl">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Default Text', ML_TDOMAIN ); if (ml_get_dft_lang()) echo ' (' . ml_get_dft_lang()->name . ')' ?>
					<th><?php esc_html_e( 'Translation', ML_TDOMAIN ) ?>
					<th>
				</tr>
			</thead>

			<tbody id="ml-trans-tbody">
			</tbody>

			<tfoot><tr><td></td><td><label for="ml-sel-all">All</label></td><td><input type="checkbox" id="ml-sel-all"></td></tr></tfoot>
		</table>

		<form id="ml-trans-form" method="post" action="options.php">
			<?php
			settings_fields(ML_TRANS_GRP);
			do_settings_sections(ML_TRANS_GRP);
			?>
			<input type="hidden" id="morelang_translation" name="<?php echo ML_UID ?>translation" value="<?php echo esc_attr(wp_json_encode($ml_trans_obj)); ?>" />
			<?php submit_button(); ?>
		</form>

		<div class="notice notice-info is-dismissible">
			<?php
			esc_html_e('More-Lang provides in-place editors as far as possible. If it does not provide in certain cases', ML_TDOMAIN);
			echo '<i>' . esc_html__(' (i.e., the Name of any Custom Field, or the Name of any non-taxonomy Product attribute)', ML_TDOMAIN) . '</i>';
			esc_html_e(', you can translate the text here, but in most cases you need to make code change to get the localized text', ML_TDOMAIN);
			esc_html_e(', please see "How to translate text without an in-place editor?" in the ', ML_TDOMAIN);
			echo '<a href="https://wordpress.org/plugins/more-lang/#how%20to%20translate%20text%20without%20an%20in-place%20editor%3F" target="_blank">FAQ</a>';
			?>
		</div>

		<data style="display:none;" id="ml-msg-notready" value=""><?php esc_html_e( "Error: Please config the languages first!", ML_TDOMAIN ) ?></data>
		<data style="display:none;" id="ml-msg-duplicate" value=""><?php esc_html_e( "Error: 'Default Text' cannot be duplicate!", ML_TDOMAIN ) ?></data>
	</div>
<?php
	add_filter('admin_footer_text', 'morelang\ml_admin_footer_text');
	add_filter('update_footer', 'morelang\ml_update_footer');
}


add_action('admin_init', 'morelang\ml_register_trans_settings');
function ml_register_trans_settings() { // whitelist options
	register_setting(ML_TRANS_GRP, ML_UID . 'translation');
}

add_filter('pre_update_option_' . ML_UID . 'translation', 'morelang\ml_pre_update_translation', 10, 2);
function ml_pre_update_translation( $value, $old_value ) {
	if ( strlen($value) > 300000 ) {
		return $old_value; // Exception, not save
	}
	return $value;
}

add_filter( 'sanitize_option_' . ML_UID . 'translation', 'morelang\ml_sanitize_translation', 10, 3 );
function ml_sanitize_translation( $value, $option, $original_value = '' ) {
	if ( $value === NULL ) return NULL;
	if ( ! is_string($value) ) return '';
	return wp_kses( $value, wp_kses_allowed_html('post') ); // Strip Javascript
}
