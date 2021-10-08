/* Change the 'ui-...' classes in case the styles are broken by other modules.
   (We can use 'classes' option in the 'tabs(...)' function in jQuery UI 1.12+ instead). */
function mlReplaceUiClass ( ele ) {
	ele.className = ele.className.replace(/ui-/g, "ml-ui-");
	jQuery(ele).find("*").add(ele).each(function() {
		this.className = this.className.replace(/\S*corner\S*/g, "");
	});
}


/* The ids might be like "meta[6][value]" in WP 3.X (technically in HTML "id" and "name" attributes can't contain '[]'s), etc.,
	which should be escaped for jQuery:
	http://learn.jquery.com/using-jquery-core/faq/how-do-i-select-an-element-by-an-id-that-has-characters-used-in-css-notation/ */
function ml_esc_wpele_id( id ) {
	return id.replace( /(:|\.|\[|\]|,|=|@)/g, "\\$1" );
}


(function($) {

/* Recovers the previous language selection for a single tabs. */
window.mlRecoverLangSelectionById = function( tabs_id ) {
	var tabsEle = document.getElementById( tabs_id );
	if ( ! tabsEle ) return;
	var mlLangtabSel = window.wpCookies && wpCookies.get("morelang-langtab-sel");
	if ( mlLangtabSel ) {
		$(tabsEle).find("li a").each(function() {
			if ( $(this).text() === mlLangtabSel && ! $(this).closest("li").hasClass("ui-tabs-active") ) {
				var idx = $(this).parent().parent().find("li").index( $(this).parent() );
				$(this).closest(".ml-ui-tabs").tabs().tabs( "option", "active", idx );
			}
		});
	}
}


/* Arranges the localized inputs into tabs. */
window.mlChangeInputPosImpl = function(dft_id, id_pfx, argObj) {
	dft_id = ml_esc_wpele_id(dft_id);
	id_pfx = ml_esc_wpele_id(id_pfx);
	if ( $("#" + dft_id).length > 0 && mlLangReady() ) {
		var $dft_p = $("#" + dft_id).parent(":not(label)");
		if($dft_p.length === 0)	$dft_p = $("#" + dft_id).parent("label").parent();
		if($dft_p.length === 0) return; // unexpected
		var tabs_id = "ml-widget-tabs-" + dft_id;
		var $ml_widget_tabs = $('<div></div>').attr("id", tabs_id);
		var $ml_widget_ul = $('<ul></ul>');
		$ml_widget_tabs.append( $ml_widget_ul );
		$(":input[id^=" + id_pfx +"]").each(function() {
			var $inp_div = $("<div></div>").attr("id", this.id + "-wrap");
			var $inp_p = $(this).parent("p, div");
			if( $inp_p.length === 0 ) $inp_p = $(this).parent("label").parent("p"); // Menu Item
			if( $inp_p.length === 0 ) $inp_p = $(this).parent("td").parent("tr"); // "Site Title", "Tagline", Taxonomy-Term
			var $inp_li = $('<li>');
			$('<a>').attr("href", ('#' + $inp_div.attr("id"))).text($(this).attr("data-langname")).appendTo( $inp_li );
			$ml_widget_ul.append( $inp_li );
			$inp_div.append(this);
			mlSetInputDirection(this);
			$ml_widget_tabs.append($inp_div);
			$inp_p.remove();
			mlChangeTabEmpty(this, $inp_li);
		});
		$ml_widget_tabs.tabs();
		mlReplaceUiClass( $ml_widget_tabs.get(0) );
		$ml_widget_tabs.get(0).className += " ml-ui-tabs-nop"; // not Post editor
		$dft_p.append( $ml_widget_tabs );
		if ( $dft_p.parent(".description-thin").parent(".menu-item-settings").length > 0 ) // before WP 4.3
			$dft_p.parent(".description-thin").removeClass("description-thin").addClass("description-wide");
		if ( ! (argObj && argObj.notRecoverLang) ) {
			mlRecoverLangSelectionById( tabs_id );
		}
	}
}


/* Sets "rtl" style on an input element of RTL language. */
window.mlSetInputDirection = function(inputEle) {
	if ( mlLangReady() && inputEle ) {
		var inpName = $(inputEle).attr("id");
		if (! inpName) return;
		var dirClass = "ml-dir-ltr";
		for ( var i = 0; i < ml_registered_langs.length; i++ ) {
			var langObj = ml_registered_langs[i];
			var locale2 = "_" + langObj.locale;
			if ( langObj.moreOpt && langObj.moreOpt.isRTL === true
					&& inpName.lastIndexOf(locale2) === inpName.length-locale2.length ) {
				dirClass = "ml-dir-rtl";
				break;
			}
		}
		$(inputEle).addClass( dirClass );
	}
}


/* Localize the text part of WP_Widget_Text in WP4.8+(which introduced the rich editor) */
window.mlLocalizeWidgetTextRicheditor = function(el_text_id, dft_text_id, text_id_pfx, $container) {
	/* To avoid the auto-fill issue(e.g., in Firefox) which may confuse the users.
	   It seems WP had better set "autocomplete" to "off" for the widget forms */
	// $container.find("form").attr("autocomplete", "off"); // no use to set it after the page is loaded
	/* There is no 'sync-input' class in WP4.8~WP4.8.1 */
	var inputSelector = ".text.sync-input, .widget-content input.text";
	if ( $("#"+el_text_id).val() !== $container.find( inputSelector )[0].defaultValue ) {
		$("#"+el_text_id).val( $container.find( inputSelector )[0].defaultValue ).trigger("change");
	}
	$container.find("textarea[id*="+ml_uid+"]").each(function() {
		// Both textarea & input have the 'defaultValue' property, which is the initial set value by default
		if ( $(this).val !== this.defaultValue ) $(this).val( this.defaultValue );
	});

	$container.find("input[type=submit]").on("mousedown", function() { // Must be before the "click" handler of WP text-widget.js
		// Switch to the default to avoid replacing the extra language's content with the default by the WP text-widget module.
		$container.find(".ml-editor-switcher").find(".ml-switch-editor:first-child").trigger("click", {notSyncLang:true});
		$("#"+dft_text_id).val( $("#"+el_text_id+"_dft").val() ); // Discard the value set by the text-widget sync-process.
	});

	/* If the WP "text-widget" is changed to make 'el_text_id' contain non-standard characters, this function might fail */
	if ( $("#"+el_text_id).attr("data-handled") !== "true" ) {
		var tools_id = "wp-"+el_text_id+"-editor-tools";
		$("<div class='ml-editor-switcher ml-switcher-popup'></div>").attr("id", tools_id).prependTo(
				$container.find(".wp-editor-tools .wp-editor-tabs") ); // Create the switcher container
		for (var i = 1; i < ml_registered_langs.length; i++) {
			var lang = ml_registered_langs[i];
			var sourceTaId = "#" + text_id_pfx + "_" + ml_uid + lang.locale; // Generated in the ".php""
			var $newTA = $('<textarea style="display:none;"></textarea>').attr("id", el_text_id+"_"+lang.locale);
			$newTA.val( $(sourceTaId).get(0).defaultValue );
			$newTA.attr("data-source", sourceTaId);
			$newTA.insertAfter("#"+el_text_id);
			$newTA.on("change input", (function createChangeHandler($newTA, sourceTaId) {
				return function() {
					$(sourceTaId).val( $newTA.val() );
				}
			})($newTA, sourceTaId) ); // Closures in loop
		}
		/* Syncs the values returned from the server after saving. */
		$(document).on("widget-updated", function(evt, $widgetRoot) {
			if ( $widgetRoot && $widgetRoot.attr("id") === $container.parent().attr("id") ) {
				$container.find("textarea[id^=" + el_text_id + "_]").each( function() {
					var dataSource = $(this).attr("data-source");
					if ( dataSource ) $(this).val( $container.find(dataSource).val() );
				} );
			}
		});

		try {
			mlLocalizeRichEditor( el_text_id );
			var editor = tinyMCE.get( el_text_id );
			if ( editor ) {
				/* Unlike 'mlLocalizeWidgetCustomHTML', 'mlLocalizeWidgetTextRicheditor' will be called only once,
				   after that the script generated in the "ml_widget.php" will do the work. */
				editor.on('init', function() { // 'init' is after 'load'
					mlRecoverLangSelection(); // "editor.dom is undefined" will be reported in the 'doSetting' if directly called
				} );
			}
		} catch (e) { console.log(e); }
	}

	$("#"+el_text_id).attr("data-handled", "true");
}


/* Localize the content part of WP_Widget_Custom_HTML(introduced in WP4.8.1, using the CodeMirror) */
window.mlLocalizeWidgetCustomHTML = function(el_content_id, dft_content_id, content_id_pfx, $container) {
	var esc_content_idf = "#" + ml_esc_wpele_id( el_content_id );
	// not directly use the id in case non-standard characters might be used for 'el_content_id' in the future
	var widget_tabs_idf = esc_content_idf + " ~ [id^=ml-widget-tabs-]";
	if ( $(esc_content_idf).attr("data-handled") === "true" ) {
		$container.find( widget_tabs_idf ).remove();
	}

	mlChangeInputPosImpl(el_content_id, content_id_pfx, {notRecoverLang: true});
	var $widget_tabs_ele = $container.find( widget_tabs_idf ).eq(0);
	$widget_tabs_ele.addClass("ml-custom-html");
	$widget_tabs_ele.insertAfter( $container.find(esc_content_idf) );
	$container.find("textarea[id*="+ml_uid+"]").each(function() {
		// Supress the browsers' auto-fill function.
		if ( $(this).val !== this.defaultValue ) $(this).val( this.defaultValue );
	});
	$container.find(esc_content_idf).on("change", function() {
		var target_content = $widget_tabs_ele.find(".ui-tabs-active a").attr("href");
		$container.find(target_content).find("textarea").val( $(this).val() ).trigger("change");
	}).val( $container.find("textarea.content.sync-input").get(0).defaultValue ).trigger("change");
	$widget_tabs_ele.on( "tabsactivate", function( event, ui ) {
		$container.find(esc_content_idf).val( ui.newPanel.find("textarea").val() ).trigger("change");
	} );
	$container.find("input[type=submit]").on("mousedown", function() {
		/* Switch to the default to use More-Lang's value instead of the "sync-input"'s value. */
		$widget_tabs_ele.find(".ui-tabs-nav li:first-child a").trigger("click", {notSyncLang:true});
	});
	mlRecoverLangSelectionById( $widget_tabs_ele.attr("id") );

	$(esc_content_idf).attr("data-handled", "true");
}

})(jQuery);


(function($) { $(document).ready( function() {

$(".ml-title-input").on("focus", function() {
	$(this).prev(".ml-title-prompt-text").addClass("screen-reader-text");
});
$(".ml-title-input").on("blur", function() {
	if ('' === this.value) $(this).prev(".ml-title-prompt-text").removeClass("screen-reader-text");
});
$(".ml-title-input").each(function() {
	if ('' != this.value) $(this).prev(".ml-title-prompt-text").addClass("screen-reader-text");
});

if ( $("form[action='options.php'] input#blogname").length > 0 && mlLangReady() ) {
	mlChangeInputPosImpl("blogname", ml_uid + "blogname_");
	mlChangeInputPosImpl("blogdescription", ml_uid + "blogdescription_");
}

if ( $("form[action='edit-tags.php']").length > 0 && mlLangReady()) { // Taxonomy editor
	if ( $("#tag-name").length ) {
		mlChangeInputPosImpl("tag-name", "tag-name_");
	}
	if ( $("#tag-description").length ) {
		mlChangeInputPosImpl("tag-description", "tag-description_");
	}
	// There is also a "#name" on the Add page
	if ( $("#edittag #name").length ) {
		mlChangeInputPosImpl("name", "name_");
	}
	if ( $("#edittag #description").length ) {
		mlChangeInputPosImpl("description", "description_");
	}
}

if ( $("form[action='post.php'] #titlediv").length > 0 && mlLangReady() ) {
	var $title_tabs = $('<div id="title-tabs"></div>');
	var $title_ul = $('<ul></ul>');
	$title_tabs.append( $title_ul );
	$("div[id^=titlediv_]").each(function() {
		var $title_li = $('<li>');
		$('<a>').attr("href", ('#' + this.id)).text($(this).attr("data-langname")).appendTo( $title_li );
		$title_ul.append( $title_li );
		$title_tabs.append(this);
		mlChangeTabEmpty($(this).find("input").get(0), $title_li);
		$(this).find(":input").each( function() {
			mlSetInputDirection(this);
		} );
	});
	$title_tabs.tabs();
	mlReplaceUiClass( $title_tabs.get(0) );
	$("#titlediv").append( $title_tabs );
}

var $postExcerpt = $('form[action="post.php"] div#postexcerpt div.inside');
if ( $postExcerpt.length > 0 && mlLangReady()
		&& $postExcerpt.find(".wp-editor-tools").length === 0 ) { // not rich editor
	var $excerpt_tabs = $('<div id="excerpt-tabs"></div>');
	var $excerpt_ul = $('<ul></ul>');
	$excerpt_tabs.append( $excerpt_ul );
	$("textarea[id^=excerpt_]").each(function() {
		var $excerpt_li = $('<li>');
		$('<a>').attr("href", ('#' + this.id + '-panel')).text($(this).prev("label").text()).appendTo( $excerpt_li );
		$(this).parent().remove();
		mlSetInputDirection(this);
		var $ta_pane = $('<div></div>').attr("id", this.id + '-panel');
		$ta_pane.append(this);
		$excerpt_ul.append( $excerpt_li );
		$excerpt_tabs.append( $ta_pane );
		mlChangeTabEmpty(this, $excerpt_li);
	});
	$excerpt_tabs.tabs({ classes: { "ui-tabs": "" } });
	mlReplaceUiClass( $excerpt_tabs.get(0) );
	$("div#postexcerpt div.inside").append( $excerpt_tabs );
}

$(document).on("input change", ".ml-ui-tabs :input", function() {
	// though 'input' is enough in therory, it's not consistent in different browsers
	var $p = $(this).parent();
	var pid = $p.attr("id") || "";
	if ( pid.indexOf("titlewrap_") === 0) { // in the Post Editor
		pid = $p.parent().attr("id") || "";
	}
	var $tab_li = $p.closest(".ml-ui-tabs").find("a[href='#" + pid + "']").closest("li");
	mlChangeTabEmpty(this, $tab_li);
});
$(document).on("change", "textarea.ml-local-ta, .wp-editor-container textarea.ml-dft-ta", function() {
	var pid = $(this).attr("id") || "";
	var $tab_a = $(".wp-editor-tools").find("a.ml-switch-editor[data-for='#" + pid + "']");
	mlChangeTabEmpty(this, $tab_a);
});

$(document).on("click", ".ml-ui-tabs .ui-tabs-nav li a", function() {
	$( $(this).attr("href") ).find(":input").focus();
});

var oldTinymceSetup = null; // It will be set to the 'mceInit[editorId].setup' in "morelang.js"
/* Sync inputs language. */
$("body.wp-admin").on("click", ".ml-ui-tabs li > a, .ml-editor-switcher a.ml-switch-editor", function(evt, argObj) {
	if ( argObj && argObj.notSyncLang ) return;
	var notChgLangsel = ($(this).attr("data-not_chg_langsel") === "true");
	var that = this;

	$("body.wp-admin .ml-ui-tabs li a").each(function() {
		if ( notChgLangsel && ($(this).attr("data-not_chg_langsel") != "true") ) return;
		if ( this !== that && $(this).text() === $(that).text() && ! $(this).closest("li").hasClass("ui-tabs-active") ) {
			var idx = $(this).parent().parent().find("li").index( $(this).parent() );
			$(this).closest(".ml-ui-tabs").tabs().tabs( "option", "active", idx );
		}
	});

	/* Add|remove RTL related attributes on a TinyMCE editor */
	function setRichEditorDirection( editorId, isRTL ) {
		if ( ! window.tinyMCE ) return;
		var editor = tinyMCE.get( editorId );
		if ( editor ) { // By default, once the Post editor is created, the instance will be kept even when it is hidden
			doSetting( editor );
		}
		else {
			// 'tinyMCEPreInit.mceInit[editorId].init_instance_callback' is not Guaranteed to be called!?
			if ( ! oldTinymceSetup ) oldTinymceSetup = tinyMCEPreInit.mceInit[editorId].setup;
			tinyMCEPreInit.mceInit[editorId].setup = function(ed) { // 'ed' is the TinyMCE Editor
				if ( oldTinymceSetup ) oldTinymceSetup( ed );
				ed.on("load", function() {
					doSetting( ed );
				});
			};
		}
		/* Note that only the last '.setup' will be in effect, so the 'isRTL' is safe to be used. */
		function doSetting( editor ) {
			var $bodyEle = $( editor.dom && editor.dom.doc && editor.dom.doc.body );
			$bodyEle.css("direction", isRTL ? "rtl" : "" );
			$bodyEle.removeClass("ml-dir-ltr ml-dir-rtl");
			$bodyEle.addClass( isRTL ? "ml-dir-rtl" : "ml-dir-ltr" );
			$bodyEle.closest("html").attr("dir", isRTL ? "rtl" : null );
		}
	}

	/* Process the rich editor tabs */
	$("body.wp-admin .ml-editor-switcher a.ml-switch-editor").each( function() {
		if ( notChgLangsel && ($(this).attr("data-not_chg_langsel") != "true") ) return;
		if ( $(this).text() === $(that).text() ) {
			if ( this !== that && ! $(this).hasClass("ml-active-lang") ) {
				$(this).trigger("click");
			}
			if ( mlLangReady() ) {
				for ( var i = 0; i < ml_registered_langs.length; i++ ) {
					var langObj = ml_registered_langs[i];
					var editorId = $(this).attr("data-editorId");
					var targetId = editorId + "_" + langObj.locale;
					if ( i === 0 ) targetId = editorId + "_dft";
					if ( $(this).attr("data-for") === "#" + targetId ) {
						var isRTL = langObj.moreOpt && langObj.moreOpt.isRTL === true;
						var $editorTA = $("textarea[name='" + editorId + "_working']");
						$editorTA.removeClass("ml-dir-ltr ml-dir-rtl");
						$editorTA.addClass( isRTL ? "ml-dir-rtl" : "ml-dir-ltr" );
						setRichEditorDirection( editorId, isRTL );
						break;
					}
				}
			}
		}
	} );

	if ( ! notChgLangsel ) {
		window.wpCookies && wpCookies.set("morelang-langtab-sel", $(this).text(), 100*24*3600); // Save the selection
	}
} );

/* Recover the previous language selection */
window.mlRecoverLangSelection = function() {
	var mlLangtabSel = window.wpCookies && wpCookies.get("morelang-langtab-sel");
	if ( mlLangtabSel ) {
		var clickTriggered = false;
		$("body.wp-admin .ml-ui-tabs li > a, body.wp-admin .ml-editor-switcher a.ml-switch-editor").each( function() {
			if ( !clickTriggered && mlLangtabSel === $(this).text() ) {
				$(this).trigger("click");
				clickTriggered = true;
			}
		} );
	}
}
mlRecoverLangSelection();

} ); } )(jQuery); // end '$(document).ready'
