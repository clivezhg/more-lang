/* This More-Lang Javascript file includes the management GUIs of posts, taxonomy-terms and general options, and some utility functions
 */


/** The languages setting is ready or not */
function mlLangReady() {
	if ( typeof ml_disable_morelang === "function" && ml_disable_morelang() ) {
		if ( typeof pagenow === "string" && pagenow.indexOf("morelang") < 0 ) {
			return false;
		}
	}
	return ( typeof(ml_registered_langs) != 'undefined' && ml_registered_langs != null && ml_registered_langs.length > 0);
}


/** Change the empty state of a Tab group */
function mlChangeTabEmpty(inpEle, $tabEle) {
	if (!inpEle || !$tabEle) return;
	var notEmpty = (inpEle.value && (""+inpEle.value).trim().length > 0);
	notEmpty ? $tabEle.removeClass("ml-tab-empty") : $tabEle.addClass("ml-tab-empty");	
}


/* Similar to the 'ml_get_admin_lang_label' in ml_pub.php */
function ml_get_admin_lang_label(lang) {
	var label = ml_get_admin_lang_label_base(lang);
	var idx = 0;
	if ( mlLangReady() ) {
		for ( var i = 0; i < ml_registered_langs.length; i++ ) {
			if( label === ml_get_admin_lang_label_base(ml_registered_langs[i]) ) idx++;
			if ( lang.locale === ml_registered_langs[i].locale ) break;
		}
	}
	if (idx > 1) label = label + idx;
	return label;
}
function ml_get_admin_lang_label_base(lang) {
	if ( window["ml_opt_obj"] && ml_opt_obj.ml_short_label ) {
		var locale = lang.locale || "";
		if ( mlLangReady() && ml_registered_langs.length > 9 ) {
			var parts = locale.split('_');
			return parts[0];
		}
		else return locale;
	}
	else return lang.name;
}


/** Clone a standard input group in Wordpress, and localize it. */
function mlCloneInputWrap($inputWrap, regLang, prefix) {
	if (! prefix) prefix = "";
	var $newInputWrap = $inputWrap.clone();
	$newInputWrap.find("p").remove();
	var $input = $newInputWrap.find(":input");
	$input.attr( "name", prefix + $input.attr("name") + "_" + regLang.locale );
	$input.attr( "id", prefix + $input.attr("id") + "_" + regLang.locale );
	$input.attr( "data-langname", ml_get_admin_lang_label(regLang) );
	$input.val('');
	var $label = $newInputWrap.find("label");
	$label.text( $label.text() + " (" + regLang.name + ")" );
	$label.attr( "for", prefix + $label.attr("for")  + "_" + regLang.locale );
	return $newInputWrap;
}


if ( typeof ml_uid === 'undefined' ) var ml_uid = 'morelang_nml_';

(function($) { $(document).ready( function() {

if ( $("form[action='options.php'] input#blogname").length && mlLangReady() ) {
	$optForm = $("form[action='options.php']");
	function genLocaleOpt(fldName) {
		var $fldTr = $optForm.find("input#" + fldName).closest("tr");
		for ( var i = ml_registered_langs.length-1; i >= 1; i-- ) {
			var regLang = ml_registered_langs[i];
			var $newFldTr = mlCloneInputWrap( $fldTr, regLang, ml_uid );
			if ( window["ml_opt_bloginfo"] ) {
				var optVal = ml_opt_bloginfo[ml_uid+fldName+"_"+regLang.locale] || "";
				$newFldTr.find("input").val( optVal );
			}
			$fldTr.after( $newFldTr );
		}
	}
	genLocaleOpt("blogname");
	genLocaleOpt("blogdescription");
}


/* Add localized inputs to taxonomy-terms editor.
 */
if ( $("form[action='edit-tags.php']").length && mlLangReady() ) {
	function createNewTermFldWrap( $termFldWrap, fld_vals ) {
		for ( var i = ml_registered_langs.length-1; i >= 1; i-- ) {
			var regLang = ml_registered_langs[i];
			var $newTermFldWrap = mlCloneInputWrap( $termFldWrap, regLang );
			$termFldWrap.after( $newTermFldWrap );
			if ( typeof fld_vals === 'object' && fld_vals ) {
				$newTermFldWrap.find(":input").val( fld_vals[regLang.locale] ); // 'undefined' & 'null' will get "".
			}
		}
	}

	var $addtagForm = $("form#addtag");
	var $edittagForm = $("form#edittag");
	if ( $addtagForm.length > 0 ) {
		var $nameWrap = $addtagForm.find(".term-name-wrap");
		if ( $nameWrap.length === 0 ) { // older WPs
			$nameWrap = $addtagForm.find(".form-field.form-required");
		}
		createNewTermFldWrap( $nameWrap, window.ml_taxonomy_names );
		var $descWrap = $addtagForm.find(".term-description-wrap");
		createNewTermFldWrap( $descWrap, window.ml_taxonomy_descs );
	}
	else if ( $edittagForm.length > 0 ) {
		var $nameTr = $edittagForm.find("tr.term-name-wrap");
		if ( $nameTr.length === 0 ) { // older WPs
			$nameTr = $edittagForm.find("tr.form-field.form-required");
		}
		createNewTermFldWrap( $nameTr, window.ml_taxonomy_names );
		var $descTr = $edittagForm.find("tr.term-description-wrap");
		createNewTermFldWrap( $descTr, window.ml_taxonomy_descs );
	}
}


/* Add localized inputs for the post_excerpt.
 */
var $postExcerpt = $('form[action="post.php"] div#postexcerpt div.inside');
if ( $postExcerpt.length && mlLangReady()
		&& $postExcerpt.find(".wp-editor-tools").length === 0 ) { // not rich editor
	function addLangInfo($textArea, info) {
		// $textArea.before( $("<label class='ml-excerpt-label'>").text("(" + info + ")").attr("for", $textArea.attr("id")) );
		$textArea.before( $("<label class='ml-excerpt-label'>").text(info).attr("for", $textArea.attr("id")) );
	}
	for ( var idx = ml_registered_langs.length - 1; idx >= 1; idx-- ) {
		var curLang = ml_registered_langs[idx];
		var $newPostExcerpt = $postExcerpt.clone();
		$newPostExcerpt.find("p").remove();
		$newPostExcerpt.find("label").remove();
		var $textArea = $newPostExcerpt.find("textarea");
		$textArea.attr("id", $textArea.attr("id") + "_" + curLang.locale);
		$textArea.attr("name", $textArea.attr("name") + "_" + curLang.locale);
		addLangInfo($textArea, ml_get_admin_lang_label(curLang));
		$textArea.val("");
		if ( typeof window["ml_excerpt_" + curLang.locale] !== 'undefined' ) {
			$textArea.val(window["ml_excerpt_" + curLang.locale]);
		}
		
		$postExcerpt.after( $newPostExcerpt );
	}
	// addLangInfo($postExcerpt.find("textarea"), ml_registered_langs[0].name);
}


/* Localize the TinyMCE editor according to the More-Lang configuration.
 */
if ( $("form[action='post.php'] #postdivrich textarea#content").length && mlLangReady() && window.tinyMCE ) {
	localizeRichEditor("content");
}

function localizeRichEditor(editorId) { // 'editorId': e.g., "content", "excerpt"
	if (! mlLangReady()) return;

	var curIdx = 0;

	function createSwitchFunc(idxToShow) {
		return function() {
			if ( idxToShow === curIdx ) return;
			$("#wp-" + editorId + "-editor-tools a.ml-switch-editor").each( function() { $(this).removeClass('ml-active-lang'); } );
			$("#wp-" + editorId + "-editor-tools a.ml-switch-editor").eq(idxToShow).addClass('ml-active-lang');
			var editor = tinyMCE.get(editorId);
			var curSelector, toShowSelector;
			curSelector = toShowSelector = "textarea#" + editorId + "_dft";
			if ( curIdx > 0 ) curSelector = "textarea#" + editorId + "_" + ml_registered_langs[curIdx].locale;
			if ( idxToShow > 0 ) toShowSelector = "textarea#" + editorId + "_" + ml_registered_langs[idxToShow].locale;
			if ( editor && ! editor.isHidden() ) {
				if ( editor.initialized ) editor.save(); // editor.on( 'SaveContent'... ) will do the replacing
				var prevContent = $(toShowSelector).val();
				if ( typeof ml_wp_vernum === 'number' && ml_wp_vernum < 40300 ) { // WP version < "4.3"
					if ( tinyMCEPreInit.mceInit[editorId] && tinyMCEPreInit.mceInit[editorId].wpautop
							  && switchEditors && switchEditors.wpautop ) {
						prevContent = switchEditors.wpautop(prevContent);
					}
				}
				$workingTA.val( prevContent );
				if ( ! editor.initialized ) { // "Is set to true after the editor instance has been initialized"
					/* In the case of WP4.8.X's WP_Widget_Text, the first 'mlRecoverLangSelection' call will run to here. */
					editor.on("init", function() { editor.load(); });
				}
				else {
					editor.load(); // editor.on( 'BeforeSetContent'... ) will do the replacing
				}
			}
			else {
				$(curSelector).val( $workingTA.val() ); // '.trigger("change")', unnecessary, will trigger automatically in this context
				$workingTA.val( $(toShowSelector).val() );
			}
			curIdx = idxToShow;
		}
	}

	function createLangSwitcher($mlEditorTabs, langName) {
		for (var i = 0; i < ml_registered_langs.length; i++) {
			var curLang = ml_registered_langs[i];
			var $but = $('<a>').text( ml_get_admin_lang_label(curLang) );
			$but.addClass( 'ml-switch-editor' );
			$but.attr( "data-editorId", editorId );
			$but.on('click', createSwitchFunc(i));
			if (curIdx === i) {
				$but.addClass( 'ml-active-lang');
			}
			$mlEditorTabs.append($but);
			var tmpId = i === 0 ? $newDftTA.attr("id") : (editorId + "_" + ml_registered_langs[i].locale);
			$but.attr("data-for", "#" + tmpId);
			mlChangeTabEmpty($("textarea#" + tmpId).get(0), $but);
		}
	}

	var $workingTA = $("textarea#" + editorId);
	var $newDftTA = $workingTA.clone().attr("id", editorId + "_dft").addClass("ml-dft-ta");
	$workingTA.attr("name", editorId + "_working");
	$newDftTA.insertAfter($workingTA);
	$newDftTA.css("display", "none");
	var $mlEditorTabs = $('<div class="ml-editor-tabs"></div>');
	var $mlEditorTabsContainer = $('#wp-' + editorId + '-editor-tools');
	/* In the case of "WP_Widget_Text", $mlEditorTabsContainer is already nested inside ".wp-editor-tabs". */
	var $wpEditorTabs = $mlEditorTabsContainer.find(".wp-editor-tabs").eq(0);
	if ( $wpEditorTabs.length ) { // Except for "WP_Widget_Text"
		$mlEditorTabsContainer = $('<div class="ml-editor-switcher"></div>');
		$wpEditorTabs.prepend( $mlEditorTabsContainer );
		if ( window["ml_opt_obj"] && ml_opt_obj.ml_switcher_popup ) {
			$mlEditorTabsContainer.addClass("ml-switcher-popup");
		}
	}
	$mlEditorTabsContainer.prepend( $mlEditorTabs );
	createLangSwitcher( $mlEditorTabs, ml_registered_langs[0].name);
	if ( ! tinyMCEPreInit.mceInit[editorId] ) // in the case of WP4.8+'s WP_Widget_Text, it's undefined, which will cause error
		tinyMCEPreInit.mceInit[editorId] = {};
	tinyMCEPreInit.mceInit[editorId].setup = function(ed) { // 'ed' is the TinyMCE Editor
		if ( ! ed.on ) { // the older tinyMCEs.
			ed.on = function(event, handler) {
				ed["on" + event.substr(0,1).toUpperCase() + event.substr(1)].add(handler);
			}
		}
		var called_times = 0;
		ed.on("load ml_tinymce_load", function() { // make sure the WP plugin is already loaded
			if (called_times++ > 1) return;
			ed.on("SaveContent", function(edOrEvt) { // autosave, Publish/Update, switch lang
				var theEvt = edOrEvt;
				if ( arguments.length > 1 && edOrEvt.editorId ) { // the older tinyMCEs.
					theEvt = arguments[1];
				}
				if ( ! ed.isHidden() )  {
					var curSelector = "textarea#" + editorId + "_" + (( curIdx > 0 ) ? ml_registered_langs[curIdx].locale : "dft");
					$( curSelector ).val( theEvt.content ).trigger("change"); // already replaced ("<p>", "&nbsp;"...)
				}
			});
		});
		if ( ed.iframeElement ) { // In newer WPs, "load" might be triggred before this blcok is called.
			ed.fire("ml_tinymce_load");
		}
	};
	/* If the Editor was created already, the 'setup' callback will not get called. */
	var ed = tinyMCE.get(editorId);
	if (ed) tinyMCEPreInit.mceInit[editorId].setup(ed);

	$workingTA.on("input change", function() {
		if (curIdx > 0) {
			$("textarea#" + editorId + "_" + ml_registered_langs[curIdx].locale).val( $workingTA.val() ).trigger("change");
		}
		else {
			$newDftTA.val( $workingTA.val() ).trigger("change");
		}
	});
} // end "localizeRichEditor"

window.mlLocalizeRichEditor = localizeRichEditor;

} ); } )(jQuery); // end '$(document).ready'
