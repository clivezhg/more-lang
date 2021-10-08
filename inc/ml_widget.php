<?php
namespace morelang;

function ml_map_locale1_title($val) { return ml_postmeta_name('title', $val); }
function ml_map_locale1_text($val) { return ml_postmeta_name('text', $val); }
function ml_map_locale1_content($val) { return ml_postmeta_name('content', $val); }
function ml_map_locale2($val) { return ''; }
global $ml_registered_locales, $ml_registered_mlocales, $more_locales, $wp_native_widgets, $locale_title_map, $locale_text_map, $locale_content_map;
$more_locales = array_slice($ml_registered_mlocales, 0);
$locale_title_map = array_map('morelang\ml_map_locale2', array_flip( array_map('morelang\ml_map_locale1_title', $more_locales) ) );
$locale_text_map = array_map('morelang\ml_map_locale2', array_flip( array_map('morelang\ml_map_locale1_text', $more_locales) ) );
$locale_content_map = array_map('morelang\ml_map_locale2', array_flip( array_map('morelang\ml_map_locale1_content', $ml_registered_locales) ) );

/* Updating Widget settings */
add_filter('widget_update_callback', 'morelang\ml_widget_update', 10, 4);
function ml_widget_update($instance, $new_instance, $old_instance, $obj) {
	global $locale_title_map, $locale_text_map, $locale_content_map;
	foreach ( $locale_title_map as $locale_title => $empty_str ) {
		if ( isset($new_instance[$locale_title]) ) {
			$instance[$locale_title] = strip_tags($new_instance[$locale_title]);
		}
	}
	if ( get_class($obj) === "WP_Widget_Text" ) {
		foreach ( $locale_text_map as $locale_text => $empty_str ) {
			if ( isset($new_instance[$locale_text]) ) {
				$instance[$locale_text] = $new_instance[$locale_text];
			}
		}
	}
	if ( get_class($obj) === "WP_Widget_Custom_HTML" ) {
		foreach ( array_slice($locale_content_map, 1) as $locale_content => $empty_str ) {
			if ( isset($new_instance[$locale_content]) ) {
				$instance[$locale_content] = $new_instance[$locale_content];
			}
		}
	}
	return $instance;
}

/* Add localized inputs for the Widgets
 */
$wp_native_widgets = ['WP_Nav_Menu_Widget', 'WP_Widget_Archives', 'WP_Widget_Calendar', 'WP_Widget_Categories', /*'WP_Widget_Links',*/ 'WP_Widget_Meta',
   'WP_Widget_Pages', 'WP_Widget_Recent_Comments', 'WP_Widget_Recent_Posts', /*'WP_Widget_RSS',*/ 'WP_Widget_Search', 'WP_Widget_Tag_Cloud', 'WP_Widget_Text'];
if ( version_compare( get_bloginfo('version'), '4.8', '>=' ) ) {
	$wp_48_49_widgets = ['WP_Widget_Media_Image', 'WP_Widget_Media_Audio', 'WP_Widget_Media_Video', 'WP_Widget_Media_Gallery', 'WP_Widget_Custom_HTML'];
	$wp_native_widgets = array_merge( $wp_native_widgets, $wp_48_49_widgets );
	global $wp_sync_widgets;
	$wp_sync_widgets = array_merge( $wp_48_49_widgets, ['WP_Widget_Text'] ); // Control-Sync structure widgets introcuded in WP4.8
}
add_action('in_widget_form', 'morelang\ml_in_widget_form', 10, 3);
function ml_in_widget_form(&$wp_widget, &$return, $instance) {
	global $wp_native_widgets;
	$w_cls = get_class($wp_widget);
	// the other supported Widgets should be in "ext".
	if ( !in_array($w_cls, $wp_native_widgets) && !($w_cls === 'morelang\\MorelangWidget') ) return;
	return ml_in_widget_form_impl($wp_widget, $return, $instance);
}
function ml_in_widget_form_impl(&$wp_widget, &$return, $instance) {
	global $locale_title_map, $locale_text_map, $locale_content_map, $wp_sync_widgets;
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
		<p><label for="<?php echo $title_id ?>"><?php echo esc_html__( 'Title', ML_TDOMAIN ) . ' (' . $lang_name . '):'; ?></label>
			<input class="widefat" id="<?php echo $title_id ?>" name="<?php echo $wp_widget->get_field_name($locale_title); ?>"
				type="text" value="<?php echo esc_attr($locale_title_arg[$locale_title]); ?>" data-langname="<?php echo $lang_name ?>" />
		</p>
	<?php
	endforeach;
	if ( version_compare( get_bloginfo('version'), '4.8', '<' )
			|| ( get_class($wp_widget) === 'WP_Widget_Custom_HTML' && version_compare( get_bloginfo('version'), '4.9', '<' ) )
			|| ! in_array(get_class($wp_widget), $wp_sync_widgets) ):
		echo "<script>mlChangeInputPos('$dft_title_id', '$title_id_pfx');</script>";
	elseif ( isset($title_id) ):
?>
<script>
	jQuery(function() { // Localize the new approach widget titles introduced in WP4.8+
		var intId = setInterval( function() {
			/* There is no 'sync-input' class in WP4.8~WP4.8.1 */
			var inputSelector = ":not(.widget-content) > input.title:not(.sync-input)";
			var el_title_id = jQuery("#<?php echo $title_id ?>").closest(".widget-inside").find(inputSelector).attr("id");
			if (el_title_id) {
				clearInterval(intId);
				var esc_title_id = ml_esc_wpele_id( el_title_id ); // "." might be contained
				if ( jQuery("#"+esc_title_id).attr("data-handled") === "true" ) {
					jQuery("#"+esc_title_id + " ~ [id^=ml-widget-tabs-]").remove();
				}
				mlChangeInputPos(el_title_id, "<?php echo $title_id_pfx ?>");
				jQuery("#"+esc_title_id).attr("data-handled", "true");
<?php if ( in_array(get_class($wp_widget), ['WP_Widget_Text']) ) { ?>
				/* "WP_Widget_Text" is partialy(only the editor loading phase) handled in 'mlLocalizeWidgetTextRicheditor'. */
				setTimeout( function() {
					/* It must run after the contents part, otherwise the values are not available */
					mlRecoverLangSelection();
				}, 60 );
<?php } ?>
			}
		}, 160);
	});
</script>
<?php
	endif;
	// The above is also used by 'ml_ext_in_widget_form_title'

	/* process WP_Widget_Text */
	if (get_class($wp_widget) === "WP_Widget_Text"):
		$dft_text_id = $wp_widget->get_field_id('text');
		$text_id_pfx = $wp_widget->get_field_id('_text');
		$locale_text_arg = wp_parse_args( (array) $instance, $locale_text_map );
		foreach ( $locale_text_map as $locale_text => $empty_str ):
			$text_id = $wp_widget->get_field_id($locale_text);
			$lang_name = ml_get_admin_lang_label( ml_get_lang_by_locale_suffix($locale_text) );
	?>
		<p<?php echo version_compare( get_bloginfo('version'), '4.8', '<' ) ? '':' style="display:none;"' ?>><label for="<?php echo $text_id ?>"><?php echo esc_html__( 'Content', ML_TDOMAIN ) . ' (' . $lang_name . '):'; ?></label>
			<textarea class="widefat" rows="16" cols="20" id="<?php echo $text_id ?>" name="<?php echo $wp_widget->get_field_name($locale_text); ?>"
				data-langname="<?php echo $lang_name ?>"><?php echo esc_textarea( $locale_text_arg[$locale_text] ); ?></textarea>
		</p>
	<?php
		endforeach;
		if ( version_compare( get_bloginfo('version'), '4.8', '<' ) ):
			echo "<script>mlChangeInputPos('$dft_text_id', '$text_id_pfx');</script>";
		else:
	?>
<script>
	jQuery(function() { // Localize the new approach WP_Widget_Text introduced in WP4.8+
		var intId = setInterval( function() {
			var el_text_id = jQuery("#<?php echo $dft_text_id ?>").closest(".widget-inside").find("textarea.wp-editor-area").attr("id");
			if (el_text_id) {
				var esc_text_id = ml_esc_wpele_id( el_text_id );
				var $container = jQuery("#"+esc_text_id).closest(".widget-inside");
				if ( ! $container.find(".wp-editor-tools .wp-editor-tabs").length ) return;
				clearInterval(intId);
				mlLocalizeWidgetTextRicheditor(el_text_id, "<?php echo $dft_text_id ?>", "<?php echo $text_id_pfx ?>", $container);
			}
		}, 160);
	});
</script>
	<?php
		endif;
	endif; // end "WP_Widget_Text"

	/* process WP_Widget_Custom_HTML */
	if (get_class($wp_widget) === "WP_Widget_Custom_HTML"):
		$dft_content_id = $wp_widget->get_field_id('content');
		$content_id_pfx = $wp_widget->get_field_id('_content');
		$locale_content_arg = wp_parse_args( (array) $instance, $locale_content_map );
		/* CodeMirror was not introduced before WP4.9. */
		$beforeWP49 = version_compare( get_bloginfo('version'), '4.9', '<' );
		$eleStyle = $beforeWP49 ? '' : 'display:none;';
		$cur_idx = 0;
		foreach ( $locale_content_map as $locale_content => $empty_str ):
			if ($beforeWP49 && $cur_idx++ === 0) continue;
			$contentt_id = $wp_widget->get_field_id($locale_content);
			$lang_name = ml_get_admin_lang_label( ml_get_lang_by_locale_suffix($locale_content) );
	?>
		<p style="<?php echo $eleStyle ?>"><label for="<?php echo $contentt_id ?>"><?php echo esc_html__( 'Content', ML_TDOMAIN ) . ' (' . $lang_name . '):'; ?></label>
			<textarea style="<?php echo $eleStyle ?>" class="widefat code" rows="8" cols="20" id="<?php echo $contentt_id ?>" name="<?php echo $wp_widget->get_field_name($locale_content) ?>"
				data-langname="<?php echo $lang_name ?>"><?php echo esc_textarea( $locale_content_arg[$locale_content] ); ?></textarea>
		</p>
	<?php
		endforeach;
		if ( $beforeWP49 ):
			echo "<script>mlChangeInputPos('$dft_content_id', '$content_id_pfx');   document.getElementById('$dft_content_id').setAttribute('rows', '8');</script>";
		else:
?>
<script>
	jQuery(function() { // Localize the WP_Widget_Custom_HTML introduced in WP4.8.1
		var intId = setInterval( function() { // Note: unlimited loop is needed, since the elements do not exist before the container is opened(the others are similar)
			var el_content_id = jQuery("#<?php echo $dft_content_id ?>").closest(".widget-inside").find("textarea.content:not(.sync-input)").attr("id");
			if (el_content_id) {
				var esc_content_id = ml_esc_wpele_id( el_content_id );
				var $container = jQuery("#"+esc_content_id).closest(".widget-inside");
				if ( ! $container.find("div.CodeMirror").length ) return;
				clearInterval(intId);
				mlLocalizeWidgetCustomHTML(el_content_id, "<?php echo $dft_content_id ?>", "<?php echo $content_id_pfx ?>", $container);
			}
		}, 160);
	});
</script>
<?php
		endif;
	endif; // end "WP_Widget_Custom_HTML"

	return;
}


require_once 'ml_switcher.php';
