<?php
namespace morelang;

function ml_map_locale1_title($val) { return ml_postmeta_name('title', $val); }
function ml_map_locale1_text($val) { return ml_postmeta_name('text', $val); }
function ml_map_locale2($val) { return ''; }
global $ml_registered_mlocales, $more_locales, $wp_native_widgets, $locale_title_map, $locale_text_map;
$more_locales = array_slice($ml_registered_mlocales, 0);
$locale_title_map = array_map('morelang\ml_map_locale2', array_flip( array_map('morelang\ml_map_locale1_title', $more_locales) ) );
$locale_text_map = array_map('morelang\ml_map_locale2', array_flip( array_map('morelang\ml_map_locale1_text', $more_locales) ) );

/* Updating Widget settings */
add_filter('widget_update_callback', 'morelang\ml_widget_update', 10, 4);
function ml_widget_update($instance, $new_instance, $old_instance, $this) {
	global $locale_title_map, $locale_text_map;
	foreach ( $locale_title_map as $locale_title => $empty_str ) {
		if ( isset($new_instance[$locale_title]) ) {
			$instance[$locale_title] = strip_tags($new_instance[$locale_title]);
		}
	}
	foreach ( $locale_text_map as $locale_text => $empty_str ) {
		if ( isset($new_instance[$locale_text]) ) {
			$instance[$locale_text] = $new_instance[$locale_text];
		}
	}
	return $instance;
}

/* Add localized inputs for the Widgets
 */
$wp_native_widgets = ['WP_Nav_Menu_Widget', 'WP_Widget_Archives', 'WP_Widget_Calendar', 'WP_Widget_Categories', /*'WP_Widget_Links',*/ 'WP_Widget_Meta',
   'WP_Widget_Pages', 'WP_Widget_Recent_Comments', 'WP_Widget_Recent_Posts', /*'WP_Widget_RSS',*/ 'WP_Widget_Search', 'WP_Widget_Tag_Cloud', 'WP_Widget_Text'];
add_action('in_widget_form', 'morelang\ml_in_widget_form', 10, 3);
function ml_in_widget_form(&$wp_widget, &$return, $instance) {
	global $wp_native_widgets;
	$w_cls = get_class($wp_widget);
	// the other supported Widgets should be in "ext".
	if ( !in_array($w_cls, $wp_native_widgets) && !($w_cls === 'morelang\\MorelangWidget') ) return;
	return ml_in_widget_form_impl($wp_widget, $return, $instance);
}
function ml_in_widget_form_impl(&$wp_widget, &$return, $instance) {
	global $locale_title_map, $locale_text_map;
	$return = null;
	if (gettype($wp_widget->number) !== 'integer') { // should not be in "Available Widgets"
		return;
	}
	$locale_title_arg = wp_parse_args( (array) $instance, $locale_title_map );
	$dft_title_id = $wp_widget->get_field_id('title');
	$title_id_pfx = $wp_widget->get_field_id('_title');
	foreach ( $locale_title_map as $locale_title => $empty_str ):
		$title_id = $wp_widget->get_field_id($locale_title);
		$lang_name = ml_get_admin_lang_label( ml_get_lang_by_locale_suffix($locale_title) );
	?>
		<p><label for="<?php echo $title_id ?>"><?php echo _('Title') . ' (' . $lang_name . '):'; ?></label>
			<input class="widefat" id="<?php echo $title_id ?>" name="<?php echo $wp_widget->get_field_name($locale_title); ?>"
				type="text" value="<?php echo esc_attr($locale_title_arg[$locale_title]); ?>" data-langname="<?php echo $lang_name ?>" />
		</p>
	<?php
	endforeach;
	echo '<script>mlChangeInputPos("' . $dft_title_id . '", "' . $title_id_pfx . '");</script>';
	// The above is also used by 'ml_ext_in_widget_form_title'
	if (get_class($wp_widget) === "WP_Widget_Text"):
		$dft_text_id = $wp_widget->get_field_id('text');
		$text_id_pfx = $wp_widget->get_field_id('_text');
		$locale_text_arg = wp_parse_args( (array) $instance, $locale_text_map );
		foreach ( $locale_text_map as $locale_text => $empty_str ):
			$text_id = $wp_widget->get_field_id($locale_text);
			$lang_name = ml_get_admin_lang_label( ml_get_lang_by_locale_suffix($locale_text) );
	?>
		<p><label for="<?php echo $text_id ?>"><?php echo _( 'Content' . ' (' . $lang_name . '):' ); ?></label>
			<textarea class="widefat" rows="16" cols="20" id="<?php echo $text_id ?>" name="<?php echo $wp_widget->get_field_name($locale_text); ?>"
				data-langname="<?php echo $lang_name ?>"><?php echo esc_textarea( $locale_text_arg[$locale_text] ); ?></textarea>
		</p>
	<?php
		endforeach;
		echo '<script>mlChangeInputPos("' . $dft_text_id . '", "' . $text_id_pfx . '");</script>';
	endif;
	return;
}


require_once 'ml_switcher.php';
