/* The support for the Gutenberg editor introduced in Wordpress 5.0
 */


(function($) { $(document).ready( function() {
if ( $(".block-editor-page").length < 1 ) return;

/* The "#editor" doesn't have related ready notification event */
var checkTimes = 0;
var intId = setInterval( function() {
	$toolbar = $(".block-editor-page #editor .edit-post-header");
	if ( $toolbar.length ) {
		clearInterval(intId);
		mlLocalizeGutenbergEditor( $toolbar );
		mlRecoverLangSelection();
	}
	if( checkTimes++ > 100 ) clearInterval(intId);
}, 100 );

} ); } )(jQuery); // end '$(document).ready'


/**
 * Lozalize the Gutenberg editor based on the settings
 */
function mlLocalizeGutenbergEditor( $toolbar ) {
	if ( !  mlLangReady() ) return;

	var $ = jQuery;
	var core_editor = wp.data.select( 'core/editor' );
	var core_editor_d = wp.data.dispatch( 'core/editor' );
	var core_notices_d = wp.data.dispatch( 'core/notices' );
	var $blockEditor = $(".block-editor");
	var curIdx = 0;

	var localeTitles = {};
	var localeContents = {};
	var localeExcerpts = {};
	var localeMetas = {};
	if ( window.ml_post_data ) {
		if ( ml_post_data.locale_titles ) localeTitles = $.extend({}, ml_post_data.locale_titles);
		if ( ml_post_data.locale_contents ) localeContents = $.extend({}, ml_post_data.locale_contents);
		if ( ml_post_data.locale_excerpts ) localeExcerpts = $.extend({}, ml_post_data.locale_excerpts);
		if ( ml_post_data.locale_metas ) localeMetas = $.extend({}, ml_post_data.locale_metas);
	}

	/* Add a middleware to disable autosaving for non-default languages. */
	wp.apiFetch.use( function( options, next ) {
		if (options.path && options.path.match(/[/]autosaves[/]?/) && options.data && typeof options.data === "object") {
			if ($(".ml-editor-switcher a.ml-active-lang:first-child").length === 0) { // non-default
				options.data.morelang_non_default = true;
			}
		}

		return next( options );
	} );

	/* If no translation is present, resort to the default value */
	function undefinedToDft(val, dftVal, isObj) {
		if ( val === undefined ) {
			if (isObj) return $.extend({}, dftVal);
			else return dftVal;
		}
		return val;
	}

	/* Store the changes of the selected locale */
	function storeChanges() {
		var edited_content = core_editor.getEditedPostContent();
		var edited_title = core_editor.getEditedPostAttribute('title');
		var edited_excerpt = core_editor.getEditedPostAttribute('excerpt');
		var edited_meta = core_editor.getEditedPostAttribute('meta');
		var curLocale = ml_registered_langs[curIdx].locale;
		localeContents[curLocale] = edited_content;
		localeTitles[curLocale] = edited_title;
		localeExcerpts[curLocale] = edited_excerpt;
		localeMetas[curLocale] = $.extend(localeMetas[curLocale], edited_meta);
	}

	/* Create a function reacting to the language switching */
	function createSwitchFunc(idxToShow) {
		return function() {
			if ( core_editor.isAutosavingPost() ) {
				var notSwitchMsg = (window.ml_i18n_obj && window.ml_i18n_obj.not_switch_autosave) || "Not Switch";
				core_notices_d.createWarningNotice("More-Lang: " + notSwitchMsg);
				return;
			}

			if ( idxToShow === curIdx ) return;

			$("a.ml-switch-editor").each( function() { $(this).removeClass('ml-active-lang'); } );
			$("a.ml-switch-editor").eq(idxToShow).addClass('ml-active-lang');
			var toLocale = ml_registered_langs[idxToShow].locale;
			var curPost = core_editor.getCurrentPost();
			var sel_content = undefinedToDft( localeContents[toLocale], curPost.content );
			var sel_title = undefinedToDft( localeTitles[toLocale], curPost.status === "auto-draft" ? "" : curPost.title );
			var sel_excerpt = undefinedToDft( localeExcerpts[toLocale], curPost.excerpt );
			var sel_meta = undefinedToDft( localeMetas[toLocale], curPost.meta, true );
			storeChanges();

			curIdx = idxToShow;
			core_editor_d.resetBlocks( wp.blocks.parse( sel_content ) );
			// 'editPost' doesn't work for 'content'
			core_editor_d.editPost(
					{title:sel_title, excerpt:sel_excerpt, meta:sel_meta} );
			$(".editor-post-publish-button, .editor-post-publish-panel__toggle").prop("disabled", curIdx > 0)
					.attr("title", (window.ml_i18n_obj instanceof Object && curIdx > 0) ? ml_i18n_obj.pub_title : "");
			$("#ml-upd-translation").prop("disabled", curIdx === 0)
					.attr("title", (window.ml_i18n_obj instanceof Object && curIdx === 0) ? ml_i18n_obj.upd_title: "");
			setGutenbergDirection();

			$("body")[idxToShow ? "addClass":"removeClass"]('ml-not-default');
		}
	}

	/* Create the More-Lang language switcher */
	function createLangSwitcher($mlEditorTabs) {
		for (var i = 0; i < ml_registered_langs.length; i++) {
			var curLang = ml_registered_langs[i];
			var $but = $('<a>').text( ml_get_admin_lang_label(curLang) );
			$but.addClass( 'ml-switch-editor' );
			if ( curLang.moreOpt && curLang.moreOpt.isRTL === true ) $but.attr( 'data-rtl', true );
			$but.on('click', createSwitchFunc(i));
			var initContent = localeContents[curLang.locale];
			if (curIdx === i) {
				$but.addClass( 'ml-active-lang');
				initContent = core_editor.getEditedPostContent();
			}
			$mlEditorTabs.append($but);
			updateSwitchTab(initContent, $but);
		}
	}

	/* Create the More-Lang components container */
	var $mlEditorTabs = $('<div class="ml-editor-tabs"></div>');
	var $mlEditorTabsContainer = $('<div class="ml-editor-switcher"></div>');
	var $mlEditorPanel = $('<div class="ml-editor-panel"></div>');
	if ( window["ml_opt_obj"] && ml_opt_obj.ml_switcher_popup ) {
		$mlEditorTabsContainer.addClass("ml-switcher-popup");
	}
	$mlEditorTabsContainer.append( $mlEditorTabs );
	$mlEditorPanel.append( $mlEditorTabsContainer );
	$toolbar.children("div").first().after( $mlEditorPanel );

	/* Create the More-Lang components */
	createLangSwitcher( $mlEditorTabs );
	var $updBtn = $('<button id="ml-upd-translation" disabled></button>');
	if ( window.ml_i18n_obj instanceof Object ) {
		$updBtn.text( ml_i18n_obj.upd_label + " " );
		$updBtn.append( $("<span></span>").text(ml_i18n_obj.tran_label) );
		$updBtn.attr( "title", ml_i18n_obj.upd_title );
	}
	else {
		$updBtn.text( "Update Translation" );
	}
	$updBtn.addClass( "components-button is-button is-default is-primary is-large" );
	var $updContainer = $('<span class="ml-upd-container"></span>');
	$mlEditorPanel.append( $updContainer.append( $updBtn ) );

	/* Submit the translation changes to the server */
	$updBtn.on("click", submitTranslation);
	function createPostData(obj) {
		var newObj = $.extend({}, obj);
		delete newObj[ ml_registered_langs[0].locale ];
		return newObj;
	}
	function submitTranslation() {
		$updBtn.prop("disabled", true);
		storeChanges();
		wp.apiFetch({
			path: '/morelang/gutenberg-post/' + core_editor.getCurrentPostId(),
			method: 'PUT',
			data: { post_type: core_editor.getCurrentPostType(),
					title: createPostData( localeTitles ),
					content: createPostData( localeContents ),
					excerpt: createPostData( localeExcerpts ),
					meta: createPostData( localeMetas ) }
		} ).then( function(resp) {
			if ( typeof resp === "object" && resp && resp.message ) {
				if ( resp.code === 0 ) {
					core_notices_d.createSuccessNotice("More-Lang: " + resp.message, {type: "snackbar"});
				}
				else {
					core_notices_d.createErrorNotice("More-Lang: " + resp.message);
				}
			}
			else {
				core_notices_d.createErrorNotice("More-Lang: " + resp);
			}
		} ).finally( function() {
			$updBtn.prop("disabled", false);
		} );
	}

	/* The RTL support */
	var dftTextEditorDir = undefined;
	function setGutenbergDirection() {
		var isRTL = $(".ml-editor-switcher .ml-switch-editor.ml-active-lang").attr("data-rtl");
		var $textEditor = $(".editor-post-text-editor");
		if (isRTL) {
			$blockEditor.addClass("ml-gutenberg-dir-rtl");
			dftTextEditorDir = $textEditor.attr("dir");
			$textEditor.attr("dir", "rtl");
		}
		else {
			$blockEditor.removeClass("ml-gutenberg-dir-rtl");
			$textEditor.attr("dir", dftTextEditorDir || "auto"); // "auto" is the current default value
		}
	}

	setGutenbergDirection();
	/* Set RTL styles after "Visual Editor"|"Code Editor"|"Exit Code Editor" is clicked */
	$blockEditor.on("autosize:destroy", ".editor-post-title__input", function() {
		// The event is also fired on the present ".editor-post-text-editor"
		var oldTextEditor = $(".editor-post-text-editor").get(0);
		setGutenbergDirection();
		var rtlCheckTimes = 0;
		var rtlIntId = setInterval( function() { // New ".editor-post-text-editor" may be generated to replace the old
			if( $(".editor-post-text-editor").get(0) !== oldTextEditor ) {
				setGutenbergDirection();
				clearInterval(rtlIntId);
			}
			if ( rtlCheckTimes++ > 10 ) clearInterval(rtlIntId);
		}, 60 ); // The newer elements will replacte the olders
	});

	/* Update the swtich tab when 'focusout'. 'input' is not used because the edited value will not be updated for it. */
	$blockEditor.on("focusout", ".edit-post-visual-editor, .editor-post-text-editor", function() {
		updateSwitchTab(core_editor.getEditedPostContent(), $(".ml-switch-editor.ml-active-lang"));
	} );

	/* Update a swtich tab */
	function updateSwitchTab(content, $tabEle) {
		content = (content ? ""+content : "").replace(/(<([^>]+)>)/ig, "");
		var notEmpty = content.trim().length > 0;
		notEmpty ? $tabEle.removeClass("ml-tab-empty") : $tabEle.addClass("ml-tab-empty");	
	}
} // end "mlLocalizeGutenbergEditor"
