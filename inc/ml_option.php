<?php
namespace morelang;

/* Enqueue scripts required by the plugin setting page */
global $plugin_page;
if ( !empty($plugin_page) && string_ends_with($plugin_page, 'ml_cfg.php') ) {
	add_action('admin_enqueue_scripts', 'morelang\morelang_cfg_scripts', 10);
}
function morelang_cfg_scripts() {
	$cur_plugin_url = plugin_dir_url( __FILE__ );
	wp_enqueue_style( 'ml_select2_style', $cur_plugin_url . '../dep/select2.min.css', array(), '4.0.3' );
	wp_enqueue_script( 'ml_select2_js', $cur_plugin_url . '../dep/select2.min.js', array(), '4.0.3' );
}

function ml_tooltip($content = '') { 
	echo '<span class="ml-tooltip"><span class="ml-tooltip-mark">?</span>';
	echo '<span class="ml-tooltip-content-wrap"><span class="ml-tooltip-content">';
	echo $content;
	echo '</span></span></span>';
}

/* more-lang configuration form.
 */
define('ML_OPT_GRP', 'morelang-option-group');
function morelang_options_page() {
	global $ml_opt_obj; ?>
	<div id="ml-opt-wrap">
		<h2><?php _e( 'More-Lang Plugin Setting', 'morelang' ) ?></h2>
		<table id="ml-langcfg-tbl">
			<thead>
				<tr>
					<th><?php _e( 'Locale', 'morelang' ) ?>
					    <?php ml_tooltip( __('e.g., <em>en_GB</em>; it can also be Language Code, e.g., <em>en</em>', 'morelang') ) ?>
					<th><?php _e( 'Name', 'morelang' ) ?>
					<th><?php _e( 'Frontend Label', 'morelang' ) ?>
					<th><?php _e( 'Flag', 'morelang' ) ?>
					    <?php ml_tooltip( __('Saved in <em>~\plugins\morelang\cflag</em>, you can copy your own flags to it', 'morelang') ) ?>
					<th><?php _e( 'Date Format', 'morelang' ) ?>
					<th><?php _e( 'Time Format', 'morelang' ) ?>
					<th><?php _e( 'More...', 'morelang' ) ?>
					<th><?php _e( 'Actions', 'morelang' ) ?>
				</tr>
			</thead>
			<tbody id="ml-langcfg-tbody">
			</tbody>

			<tfoot>
				<tr><td colspan="8">
					<fieldset><legend><?php _e( 'Frontend', 'morelang' ) ?></legend>
					<input type="checkbox" id="ml-auto-chooser" checked>
					<label for="ml-auto-chooser"><?php _e( 'Generate the language chooser automatically', 'morelang' ) ?></label>
					<span id="ml-pos-input-grp"><label><?php _e( ', at:', 'morelang' ) ?></label>
					<span class="ml-radio-grp">
					<input type="radio" id="ml-pos-inputmode-sel" name="ml-pos-inputmode" value="<?php echo ML_POS_INPUTMODE_SEL ?>" checked>
					<label for="ml-pos-inputmode-sel"><?php _e( 'Fixed Position', 'morelang' ) ?></label>
					<select id="ml-pos-sel" name="ml-pos-sel">
						<option value=""></option>
						<option value="TR" selected><?php _e( 'top-right', 'morelang' ) ?></option>
						<option value="BR"><?php _e( 'bottom-right', 'morelang' ) ?></option>
						<option value="BL"><?php _e( 'bottom-left', 'morelang' ) ?></option>
						<option value="TL"><?php _e( 'top-left', 'morelang' ) ?></option>
						<option value="T"><?php _e( 'top', 'morelang' ) ?></option>
						<option value="R"><?php _e( 'right', 'morelang' ) ?></option>
						<option value="B"><?php _e( 'bottom', 'morelang' ) ?></option>
						<option value="L"><?php _e( 'left', 'morelang' ) ?></option>
					</select>
					<input type="radio" id="ml-pos-inputmode-text" name="ml-pos-inputmode" value="<?php echo ML_POS_INPUTMODE_TEXT ?>">
					<label for="ml-pos-inputmode-text"><?php _e( 'CSS Selector', 'morelang' ) ?></label>
					<input type="text" id="ml-pos-text" name="ml-pos-text" size="11" disabled>
					</span>
					</span>
					<br><?php _e( 'URL mode:', 'morelang' ) ?><span class="ml-radio-grp ml-opt-gap">
					<input id="ml-url-mode-path" type="radio" name="ml-url-mode" value="<?php echo ML_URLMODE_PATH ?>" checked>
					<label for="ml-url-mode-path"><?php _e( 'Prepend to Path', 'morelang' ) ?> <span class="ml-inner-comment">(<?php _e( 'e.g.', 'morelang' ) ?>, http://localhost/en_GB/)</span></label>
					<input id="ml-url-mode-qry" type="radio" name="ml-url-mode" value="<?php echo ML_URLMODE_QRY ?>">
					<label for="ml-url-mode-qry"><?php _e( 'Query String', 'morelang' ) ?> <span class="ml-inner-comment">(<?php _e( 'e.g.', 'morelang' ) ?>, http://localhost/?lang=en_GB)</span></label>
					</span>
					<br><?php _e( 'URL language format:', 'morelang' ) ?><span class="ml-radio-grp">
					<input type="checkbox" id="ml-url-locale-lower-case">
					<label for="ml-url-locale-lower-case"><?php _e( 'Lower Case', 'morelang' ) ?></label>
					<input type="checkbox" id="ml-url-locale-to-hyphen">
					<label for="ml-url-locale-to-hyphen"><?php _e( 'Underscore to Hyphen', 'morelang' ) ?></label>
					<input type="checkbox" id="ml-url-locale-no-country">
					<label for="ml-url-locale-no-country"><?php _e( 'No Country Code', 'morelang' ) ?></label>
					<span id="ml-url-locale-result-example" class="ml-inner-comment">(<?php _e( 'e.g., from en_GB to ', 'morelang' ) ?>
						<span id="ml-url-locale-result">en<span class="ml-country-part"><span class="ml-hyphen">-</span><span class="ml-underscore">_</span>GB</span></span>)</span>
					</span>
					</fieldset>

					<fieldset><legend><?php _e( 'Admin Panels', 'morelang' ) ?></legend>
					<input type="checkbox" id="ml-short-label">
					<label for="ml-short-label"><?php _e( 'Use short mode for the language labels on the admin panels', 'morelang' ) ?>
						<span class="ml-inner-comment">(<?php _e( 'i.e., the Locale instead of the Name', 'morelang' ) ?>)</span></label>
					</fieldset>
				</td></tr>
			</tfoot>
		</table>
		<hr>
		<div id="ml-locale-panel">
			<label><?php _e( 'Add Locale:', 'morelang' ) ?></label>
			<?php ml_tooltip( __('How are they selected & sorted? Reference:', 'morelang') . '<a target="_blank" href="http://en.wikipedia.org/wiki/Languages_used_on_the_Internet">Languages_used_on...</a>' ) ?>
			<select id="ml-locale-sel" size="8"></select>
		</div>
		<button id="ml-add-locale" class="button button-primary" disabled><?php _e( 'Add', 'morelang' ) ?></button>
		or
		<button id="ml-create-locale" class="button button-primary"><?php _e( 'Create New', 'morelang' ) ?></button>
		<form id="ml-langcfg-form" method="post" action="options.php">
			<?php
			settings_fields(ML_OPT_GRP);
			do_settings_sections(ML_OPT_GRP);
			?>
			<input type="hidden" id="morelang_option" name="morelang_option" value="<?php echo esc_attr(json_encode($ml_opt_obj)); ?>" />
			<?php submit_button(); ?>
		</form>

		<button id="ml-up-lang-temp" class="ml-up-lang" style="display:none;"><?php _e( "↑ up", 'morelang' ) ?></button>
		<button id="ml-del-lang-temp" class="ml-del-lang" style="display:none;"><?php _e( "Delete", 'morelang' ) ?></button>
		<data style="display:none;" value="ml-msg-empty"><?php _e( "Error: 'Locale' cannot be empty!", 'morelang' ) ?></data>
		<data style="display:none;" value="ml-msg-duplicate"><?php _e( "Error: 'Locale' cannot be duplicate!", 'morelang' ) ?></data>
		<div id="ml-moreopt-tpl" style="display:none;">
			<div class="ml-moreopt-wrap">
				<span class="ml-moreopt-ind"><span><?php _e( "more options", 'morelang' ) ?></span><span><?php _e( "Close", 'morelang' ) ?></span></span>
				<div class="ml-moreopt-spacer">
					<div class="ml-moreopt-grp">
						<div><label><?php _e( "Missing Content Placeholder:", 'morelang' ) ?> <input type="text" class="ml-missing-content" size="36"></label>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
}

add_action('admin_init', 'morelang\register_ml_option_settings');
function register_ml_option_settings() { // whitelist options
	register_setting(ML_OPT_GRP, 'morelang_option');
}
