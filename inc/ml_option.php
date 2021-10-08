<?php
namespace morelang;
/* More-Lang setting page */


/* Enqueue scripts required by the plugin setting page */
global $plugin_page;
if ( isset($plugin_page) && string_ends_with($plugin_page, ML_OPT_MENUSLUG) ) {
	add_action('admin_enqueue_scripts', 'morelang\ml_cfg_scripts', 10);
}
function ml_cfg_scripts() {
	ml_enqueue_select2();
	$cur_plugin_url = plugin_dir_url( dirname(__FILE__) );
	wp_enqueue_script( 'morelang_js_cfg', $cur_plugin_url . 'js/morelang_cfg.js', array('morelang_js'), ML_VER, true );
}

function ml_tooltip($content = '') {
	$content = wp_kses( $content, array('em'=>array(), 'br'=>array(), 'a'=>array('href'=>array(), 'target'=>array())) );
	echo '<span class="ml-tooltip"><span class="ml-tooltip-mark">?</span>';
	echo '<span class="ml-tooltip-content-wrap"><span class="ml-tooltip-content">';
	echo $content;
	echo '</span></span></span>';
}


/* More-Lang configuration form.
 */
define('ML_OPT_GRP', 'morelang-option-group');
function ml_options_page() {
	global $ml_opt_obj;
	$valid_permalink = ! empty( get_option('permalink_structure') );
?>
	<div id="ml-opt-wrap">
<?php if (! $valid_permalink): ?>
	<div class="ml-invalid-env-opt" data-level="<?php esc_html_e( 'ERROR', ML_TDOMAIN ) ?>">
		<?php esc_html_e( "Permalink should not be 'Plain'.", ML_TDOMAIN ) ?>
		<a href="options-permalink.php" target="_blank"><?php esc_html_e( 'Go to the setting page', ML_TDOMAIN ) ?></a>
	</div>
<?php endif; ?>
		<div id="ml-opt-heading"><h2><?php echo esc_html__( 'More-Lang Plugin Settings', ML_TDOMAIN ) . ' (V' . ML_VER . ')' ?></h2><?php do_action('morelang_option_head') ?></div>
		<div class="ml-btn-panel"><div>
			<span class="ml-directive-label"><?php esc_html_e( 'Add Locale:', ML_TDOMAIN ) ?></span>
			<select id="ml-locale-sel" size="8"></select>
			<button id="ml-add-locale" class="button button-primary" disabled><?php esc_html_e( 'Add', ML_TDOMAIN ) ?></button>
			<?php esc_html_e( 'or', ML_TDOMAIN ); ?>
			<button id="ml-create-locale" class="button button-primary"><?php esc_html_e( 'Create New', ML_TDOMAIN ) ?></button>
		</div></div>

		<table id="ml-langcfg-tbl">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Locale', ML_TDOMAIN ) ?>
					    <?php ml_tooltip( __('e.g., <em>en_GB</em>; it can also be Language Code, e.g., <em>en</em>.', ML_TDOMAIN)
								. __('<br>If you modify a Locale after entering any content for the language,', ML_TDOMAIN)
								. __('the link from the content to the language will be broken, you need to re-enter the content.', ML_TDOMAIN) ) ?>
					<th><?php esc_html_e( 'Name', ML_TDOMAIN ) ?>
					<th><?php esc_html_e( 'Frontend Label', ML_TDOMAIN ) ?>
					<th><?php esc_html_e( 'Flag', ML_TDOMAIN ) ?>
					<th><?php esc_html_e( 'Date Format', ML_TDOMAIN ) ?>
					    <?php ml_tooltip( __('"Date Format" & "Time Format": the pre-defined expect that the default Wordpress format is used in your theme,', ML_TDOMAIN)
								. esc_html__(' and ISO format is provided for most of the countries.', ML_TDOMAIN)
								. esc_html__(' If it is not suitable, enter your format.', ML_TDOMAIN) ) ?>
					<th><?php esc_html_e( 'Time Format', ML_TDOMAIN ) ?>
					<th><?php esc_html_e( 'More...', ML_TDOMAIN ) ?>
					<th><?php esc_html_e( 'Actions', ML_TDOMAIN ) ?>
				</tr>
			</thead>

			<tbody id="ml-langcfg-tbody">
			</tbody>

			<tfoot>
				<tr><td colspan="8">
					<fieldset><legend><?php esc_html_e( 'Frontend', ML_TDOMAIN ) ?></legend>
					<input type="checkbox" id="ml-auto-chooser" checked>
					<label for="ml-auto-chooser"><?php esc_html_e( 'Generate language switcher', ML_TDOMAIN ) ?></label>
					<span class="ml-auto-chooser-hight-fix"></span>
					<span id="ml-pos-style-grp"><span><?php esc_html_e( ' at:', ML_TDOMAIN ) ?></span>
					<span class="ml-radio-grp">
					<input type="radio" id="ml-pos-inputmode-sel" name="ml-pos-inputmode" value="<?php echo ML_POS_INPUTMODE_SEL ?>" checked>
					<label for="ml-pos-inputmode-sel"><?php esc_html_e( 'Fixed', ML_TDOMAIN ) ?></label>
					<select id="ml-pos-sel" name="ml-pos-sel">
						<option value="">&nbsp;</option> <!-- Element 'option' without attribute 'label' must not be empty -->
						<option value="TR" selected><?php esc_html_e( 'top-right', ML_TDOMAIN ) ?></option>
						<option value="BR"><?php esc_html_e( 'bottom-right', ML_TDOMAIN ) ?></option>
						<option value="BL"><?php esc_html_e( 'bottom-left', ML_TDOMAIN ) ?></option>
						<option value="TL"><?php esc_html_e( 'top-left', ML_TDOMAIN ) ?></option>
						<option value="T"><?php esc_html_e( 'top', ML_TDOMAIN ) ?></option>
						<option value="R"><?php esc_html_e( 'right', ML_TDOMAIN ) ?></option>
						<option value="B"><?php esc_html_e( 'bottom', ML_TDOMAIN ) ?></option>
						<option value="L"><?php esc_html_e( 'left', ML_TDOMAIN ) ?></option>
					</select>
					<input type="radio" id="ml-pos-inputmode-text" name="ml-pos-inputmode" value="<?php echo ML_POS_INPUTMODE_TEXT ?>">
					<label for="ml-pos-inputmode-text"><?php esc_html_e( 'CSS Selector', ML_TDOMAIN ) ?></label>
					<?php ml_tooltip( __('The switcher(s) will be appended to the element(s) specified by the CSS Selector input.', ML_TDOMAIN) ) ?>
					<input type="text" id="ml-pos-text" name="ml-pos-text" size="16" disabled>
					</span>
					<span>&nbsp;<?php esc_html_e( ' style:', ML_TDOMAIN ) ?></span>
					<select id="ml-style-sel" name="ml-style-sel">
						<option value="popup" selected><?php esc_html_e( 'Popup', ML_TDOMAIN ) ?></option>
						<option value="horizontal"><?php esc_html_e( 'Horizontal', ML_TDOMAIN ) ?></option>
					</select>
					</span>
					<br><input type="checkbox" id="ml-no-css">
					<label for="ml-no-css"><?php esc_html_e( "Do not use the default styles for the language switchers", ML_TDOMAIN ) ?></label>
					<?php ml_tooltip( __('I.e., do not import the "morelang_front.css", then we can customize the switcher styles more easily.', ML_TDOMAIN) ) ?>
					<br><input type="checkbox" id="ml-auto-redirect">
					<label for="ml-auto-redirect"><?php esc_html_e( "Redirect according to the browser's preferred languages when the front page is requested", ML_TDOMAIN ) ?></label>
					<?php ml_tooltip( __('If the front page is linked (i.e., "HTTP_REFERER" in PHP) from the same site, the redirection will not occur.', ML_TDOMAIN) ) ?>
					<br><span class="ml-directive-label"><?php esc_html_e( 'URL mode:', ML_TDOMAIN ) ?></span>
					<span class="ml-radio-grp ml-opt-gap">
					<input id="ml-url-mode-path" type="radio" name="ml-url-mode" value="<?php echo ML_URLMODE_PATH ?>" checked>
					<label for="ml-url-mode-path"><?php esc_html_e( 'Prepend to Path', ML_TDOMAIN ) ?></label> <span class="ml-inner-comment">(<?php esc_html_e( 'e.g.', ML_TDOMAIN ) ?>, http://localhost/en_GB/)</span>
					<input id="ml-url-mode-qry" type="radio" name="ml-url-mode" value="<?php echo ML_URLMODE_QRY ?>">
					<label for="ml-url-mode-qry"><?php esc_html_e( 'Query String', ML_TDOMAIN ) ?></label> <span class="ml-inner-comment">(<?php esc_html_e( 'e.g.', ML_TDOMAIN ) ?>, http://localhost/?lang=en_GB)</span>
					</span>
					<br><span class="ml-directive-label"><?php esc_html_e( 'URL language format:', ML_TDOMAIN ) ?></span>
					<span class="ml-radio-grp">
					<input type="checkbox" id="ml-url-locale-lower-case">
					<label for="ml-url-locale-lower-case"><?php esc_html_e( 'Lower Case', ML_TDOMAIN ) ?></label>
					<input type="checkbox" id="ml-url-locale-to-hyphen">
					<label for="ml-url-locale-to-hyphen"><?php esc_html_e( 'Underscore to Hyphen', ML_TDOMAIN ) ?></label>
					<input type="checkbox" id="ml-url-locale-no-country">
					<label for="ml-url-locale-no-country"><?php esc_html_e( 'No Country Code', ML_TDOMAIN ) ?></label>
					<span id="ml-url-locale-result-example" class="ml-inner-comment">(<?php esc_html_e( 'e.g., from en_GB to ', ML_TDOMAIN ) ?>
						<span id="ml-url-locale-result">en<span class="ml-country-part"><span class="ml-hyphen">-</span><span class="ml-underscore">_</span>GB</span></span>)</span>
					</span>
					<br class="ml-opt-gap-top"><input type="checkbox" id="ml-gen-hreflang">
					<label for="ml-gen-hreflang"><?php esc_html_e( "Generate hreflang tags", ML_TDOMAIN ) ?></label>
					<?php ml_tooltip( __('Some search engines use it for multilingual SEO.', ML_TDOMAIN) ) ?>
					<?php do_action('morelang_option_frontend') ?>
					</fieldset>

					<fieldset><legend><?php esc_html_e( 'Admin Panels', ML_TDOMAIN ) ?></legend>
					<span class="ml-optrow-spacer"></span><input type="checkbox" id="ml-short-label">
					<label for="ml-short-label"><?php esc_html_e( 'Use short mode for the language labels on the admin panels', ML_TDOMAIN ) ?></label>
						<?php ml_tooltip( __('If you have many languages, this can make the language switching tabs shorter.', ML_TDOMAIN) ) ?>
						<span class="ml-inner-comment">(<?php esc_html_e( 'i.e., the Locale instead of the Name', ML_TDOMAIN ) ?>)</span>
					<br><span class="ml-optrow-spacer"></span><input type="checkbox" id="ml-switcher-popup">
					<label for="ml-switcher-popup"><?php esc_html_e( 'Use pop-up language switcher on the rich editors', ML_TDOMAIN ) ?></label>
						<?php ml_tooltip( __('If you have many languages, the horizontal language switching tabs might mess up the rich editors, then you can enable this to clean it.', ML_TDOMAIN) ) ?>
						<span class="ml-inner-comment">(<?php esc_html_e( 'i.e., pop-up menu instead of horizontal tabs', ML_TDOMAIN ) ?>)</span>
					<br><span class="ml-optrow-spacer"></span><input type="checkbox" id="ml-clear-when-delete-plugin">
					<label for="ml-clear-when-delete-plugin"><?php esc_html_e( 'Clear the More-Lang data when "Delete" More-Lang on the Plugins panel', ML_TDOMAIN ) ?></label>
						<span class="ml-inner-comment">(<?php esc_html_e( 'this is unrecoverable', ML_TDOMAIN ) ?>)</span>
					<br><span class="ml-optrow-spacer"></span><input type="checkbox" id="ml-not-add-posts-column">
					<label for="ml-not-add-posts-column"><?php esc_html_e( 'Do not add "Language" column to the Posts lists.', ML_TDOMAIN ) ?></label>
						<?php ml_tooltip( __('If you feel the "Language" column is disturbing, you can disable it here.', ML_TDOMAIN) ) ?>
					<?php do_action('morelang_option_adminpanels') ?>
					</fieldset>

					<?php do_action('morelang_option_more') ?>

					<div class="ml-switch-modules">
					<input type="checkbox" id="ml-enable-special-3party-compat">
					<label for="ml-enable-special-3party-compat"><?php esc_html_e( "Enable compatibility with Plugins & Themes in some special cases", ML_TDOMAIN ) ?></label>
					<?php ml_tooltip( __('After enabling it, a menu item "Special" will be added to the navigation sidebar menu.', ML_TDOMAIN) ) ?>
					</div>
				</td></tr>
			</tfoot>
		</table>

		<form id="ml-langcfg-form" method="post" action="options.php">
			<?php
			settings_fields(ML_OPT_GRP);
			do_settings_sections(ML_OPT_GRP);
			?>
			<input type="hidden" id="morelang_option" name="<?php echo ML_UID ?>option" value="<?php echo esc_attr(wp_json_encode($ml_opt_obj)); ?>" />
			<?php
			do_action('morelang_option_inform');
			?>
			<?php submit_button(); ?>
		</form>

		<button id="ml-up-lang-temp" class="ml-up-lang" style="display:none;"><?php esc_html_e( "↑ up", ML_TDOMAIN ) ?></button>
		<button id="ml-del-lang-temp" class="ml-del-lang" style="display:none;"><?php esc_html_e( "Delete", ML_TDOMAIN ) ?></button>
		<div id="ml-dft-help-temp" style="display:none;"> <?php ml_tooltip( __('If you change the default language to another language after entering any content for either of the two languages,', ML_TDOMAIN)
				. __(' the link from the content to the language will be broken, you need to re-enter the content.', ML_TDOMAIN) ) ?></div>
		<data style="display:none;" id="ml-msg-prelocale" value=""><?php esc_html_e( "Select Pre-defined Locales", ML_TDOMAIN ) ?></data>
		<data style="display:none;" id="ml-msg-empty" value=""><?php esc_html_e( "Error: 'Locale' cannot be empty!", ML_TDOMAIN ) ?></data>
		<data style="display:none;" id="ml-msg-duplicate" value=""><?php esc_html_e( "Error: 'Locale' cannot be duplicate!", ML_TDOMAIN ) ?></data>
		<div id="ml-moreopt-tpl" style="display:none;">
			<div class="ml-moreopt-wrap">
				<span class="ml-moreopt-ind"><span><?php esc_html_e( "more options", ML_TDOMAIN ) ?></span><span><?php esc_html_e( "Close", ML_TDOMAIN ) ?></span></span>
				<div class="ml-moreopt-spacer">
					<div class="ml-moreopt-grp">
						<div class="ml-moreopt-row">
							<div class="ml-moreopt-title"><label class="ml-directive-label"><?php esc_html_e( "Missing content placeholder:", ML_TDOMAIN ) ?></label>
								<?php ml_tooltip( __("If a Post's content hasn't been translated, this will be displayed instead.", ML_TDOMAIN) ) ?></div>
							<div class="ml-moreopt-content"><textarea class="ml-missing-content" cols="36" rows="3"></textarea></div>
						<hr></div>
						<div class="ml-moreopt-row">
							<div class="ml-moreopt-title"><label class="ml-directive-label"><?php esc_html_e( "Is RTL language:", ML_TDOMAIN ) ?></label></div>
							<div class="ml-moreopt-content"><input type="checkbox" class="ml-is-rtl"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
	add_filter('admin_footer_text', 'morelang\ml_admin_footer_text');
	add_filter('update_footer', 'morelang\ml_update_footer');
}


add_action('admin_init', 'morelang\ml_cfg_register_settings');
function ml_cfg_register_settings() { // whitelist options
	register_setting(ML_OPT_GRP, ML_UID . 'option');
}

add_filter( 'pre_update_option_' . ML_UID . 'option', 'morelang\ml_cfg_pre_update_option', 10, 2 );
function ml_cfg_pre_update_option( $value, $old_value ) {
	if ( strlen($value) > 60000 ) {
		return $old_value; // Exception, not save
	}
	return $value;
}

add_filter( 'sanitize_option_' . ML_UID . 'option', 'morelang\ml_cfg_sanitize_option', 10, 3 );
function ml_cfg_sanitize_option( $value, $option, $original_value = '' ) {
	if ( $value === NULL ) return NULL;
	if ( ! is_string($value) ) return '';
	return wp_kses( $value, wp_kses_allowed_html('post') ); // Strip Javascript
}
