/* Autosave support for the localized title, content & excerpt.
 * They are used by the "ext/autosave/*.js".
 */
function mlInitAutosave( $ ) {
	$(document).ready( function() { window.mlDocumentReady = true; } );
	if ( mlLangReady() ) {
		window.morelang = {};
		window.morelang.getPostDataHack = function( data ) {
			if ( typeof data !== 'object' ) return;
			if ( $('#content_dft').length ) data["content"] = $('#content_dft' ).val() || '';
			if ( $('#excerpt_dft').length ) data["excerpt"] = $('#excerpt_dft' ).val() || '';
			for ( var idx = 1; idx < ml_registered_langs.length; idx++ ) {
				var locale = ml_registered_langs[idx].locale;
				data["title_" + locale] = $("#title_" + locale).val() || '';
				data["content_" + locale] = $("#content_" + locale).val() || '';
				data["excerpt_" + locale] = $("#excerpt_" + locale).val() || '';
			}
			if ( $( '#auto_draft' ).val() !== '1' ) {
				/* "auto-draft" is skipped, otherwise a newly created Post will be set the "post_content_filtered" value. */
				data["post_content_filtered"] = "morelang_avoid_unexpected_autosave_deletion"; // See the ".php" for more details
			}
		}

		window.morelang.getCompareStringHack = function( postData ) {
			/* Wordpress autosave may submit before '$(document).ready' if the '#post-lock-dialog' exists (looks unnecessary!?),
			   by returning 'undefined' here it can only submit after '$(document).ready' */
			if ( ! window.mlDocumentReady ) return undefined; // The localized Excerpts are not ready
			var compStr = "";
			if ( typeof postData === 'object' ) {
				if ( postData === null ) return ""; // A legacy issue
				compStr = ( postData.post_title || '' ) + '::' + ( postData.content || '' ) + '::' + ( postData.excerpt || '' );
				for ( var idx = 1; idx < ml_registered_langs.length; idx++ ) {
					var locale = ml_registered_langs[idx].locale;
					compStr += '::' + ( postData["title_" + locale] || '' ) + '::' + ( postData["content_" + locale] || '' ) + '::' + ( postData["excerpt_" + locale] || '' );
				}
			}
			else { // Only the initial will enter this branch, the result must be the same as another
				compStr = ( $('#title').val() || '' ) + '::' + ( $('#content').val() || '' ) + '::' + ( $('#excerpt').val() || '' );
				for ( var idx = 1; idx < ml_registered_langs.length; idx++ ) {
					var locale = ml_registered_langs[idx].locale;
					compStr += '::' + ( $('#title_' + locale).val() || '' ) + '::' + ( $('#content_' + locale).val() || '' ) + '::' + ( $('#excerpt_' + locale).val() || '' );
				}
			}
			return compStr;
		}
	}
}
/* Run it out of '$(document).ready', because Wordpress autosave may call the related functions before '$(document).ready' */
mlInitAutosave( jQuery );



(function($) { $(document).ready( function() {


/* The localization of the custom meta fields in a post.
 */
if ( $('form#post #postcustom').length && mlLangReady() ) { // the classic Post editor
	localizeCustomFields();
}
else if ( $('.block-editor #editor').length && mlLangReady() ) { // the block Post editor
	var checkTimes = 0;
	var intId = setInterval( function() {
		if( checkTimes++ > 60 ) clearInterval(intId);
		if ( $('.block-editor #editor #poststuff').length ) {
			localizeCustomFields();
			clearInterval(intId);
		}
	}, 300);
}


/* Localize the custom meta fields on the current page */
function localizeCustomFields() {
	var pre_id_val = {};
	var postId = $("form#post input#post_ID, .block-editor input#post_ID").val();
	var ajaxurl = window.ajaxurl || window.url.pathname.match( /.*[/]wp-admin[/]/ )[0] + "admin-ajax.php";


	/* Processing the metas list section */
	function processMetaListRows() {
		/* Using a single request to improve performance if the count is greater than 3 */
		if ( $("tbody#the-list tr[data-handled!=true]").length > 3 ) {
			processMetaListRowsMerged();
			return;
		}

		$("tbody#the-list tr").each( function() {
			if ( $(this).attr("data-handled") ) return;
			processMetaListRow(this);
			$(this).attr("data-handled", true);
		} );
	}


	/* Creating localized inputs */
	function createLocaleInputs(the_tr) {
		$inp = $(the_tr).find("td:last").find(":input");
		var tabwrap_id = $inp.attr("id") + "_tabwrap";
		for ( var i = ml_registered_langs.length-1; i >= 1; i-- ) {
			var $newInp = $inp.clone().text("");
			var regLang = ml_registered_langs[i];
			$newInp.attr("id", $inp.attr("id") + "_" + regLang.locale);
			$newInp.attr("name", 'morelang_' + regLang.locale + "_" + $inp.attr("name") );
			$newInp.addClass("morelang-meta-locale");
			$newInp.attr("data-locale", regLang.locale);
			$newInp.attr( "data-langname", ml_get_admin_lang_label(regLang) );
			$newInp.val(pre_id_val[ $newInp.attr("id") ] || "");
			$inp.after( $("<div><label for='" + $newInp.attr("id") + "'>"
					+ ml_get_admin_lang_label(regLang) + ":</label></div>").append($newInp) );
		}
		$inp.after("<div><span id='" + tabwrap_id + "'></span></div>");
		mlChangeInputPosImpl(tabwrap_id, $inp.attr("id") + "_");
	}


	/* Submitting localized meta value using Wordpress Ajax */
	function submitLocaleMetas(btn, the_tr, postId) {
		function initDataobj() {
			var dataobj = {};
			dataobj.action = 'ml-upd-meta';
			dataobj.post_id = postId;
			var datawp = $(btn).attr('data-wp-lists');
			dataobj.dflt_meta_id = $(btn).attr("id").replace("meta-", "").replace("-submit", ""); // The default meta
			var add_nonce_match = datawp.match( /.*_ajax_nonce-add-meta=([a-z0-9]+)/i );
			if (add_nonce_match && add_nonce_match.length >= 2) dataobj["_ajax_nonce-add-meta"] = add_nonce_match[1];
			return dataobj;
		}
		$(the_tr).find(":input.morelang-meta-locale").each(function() {
			var dataobj = initDataobj();
			var val = $(this).val();
			pre_id_val[ $(this).attr("id") ] = val;
			var meta_id = $(this).attr("data-mid");
			if ( meta_id ) {
				if ( val != "" ) {
					dataobj["meta[" + meta_id + "][key]"] = $(this).attr("data-mkey");
					dataobj["meta[" + meta_id + "][value]"] = val;
				}
				else { // More-Lang doesn't keep empty meta record
					dataobj["_ajax_nonce"] = $(this).attr("data-del_nonce");
					dataobj["id"] = meta_id;
					dataobj["delete-empty"] = "true";
				}
			}
			else if ( val != "" ) {
				dataobj["meta_key"] = $(the_tr).find("input[name^=meta]").val();
				dataobj["meta_value"] = val;
				dataobj["locale"] = $(this).attr("data-locale");
				dataobj["add-new"] = "true";
			}
			else return;
			var $container = $(this).parent().parent();
			jQuery.ajax({url:ajaxurl, type:"POST", data:dataobj, async:false, success: function(response) {
				if (typeof response === 'string') {
					if ( isNaN(response) ) { // non-number means error message returned
						console.error('(ml-upd-meta) the server error: ' + response);
						$container.prepend( $('<div class="error"></div>').text(response) );
					}
					else if (response === '-1') console.error('(ml-upd-meta) the server stopped!');
				}
				else {
					console.log('(ml-upd-meta) Got this from the server:', response);
				}
			}});
			/* Sync is used here, to make sure the built-in call runs at last. Because if the meta name is changed,
			   the 'ml_update_post_meta(...)' needs to be invoked later to change the meta_keys of locale metas. */
		});
	}


	/* Processing a meta row */
	function processMetaListRow(the_tr) {
		createLocaleInputs(the_tr);
		$(the_tr).find(".morelang-meta-locale").prop("readonly", true);
		var meta_name = $(the_tr).find("input[name^=meta]").val();
		jQuery.post(ajaxurl, {action: 'ml-get-meta', post_id: postId, metaname: meta_name,
				_ajax_nonce: $(the_tr).find("#_ajax_nonce").val()}, function(resp) {
			if (! (resp instanceof Object)) resp = {};

			$(the_tr).find(".morelang-meta-locale").each( function() {
				var curLoc = $(this).attr("data-locale");
				var curObj = resp[curLoc];
				if ( curObj instanceof Object ) {
					$(this).val( curObj.value );
					if (curObj.meta_id) {
						$(this).attr("data-mid", curObj.meta_id);
						$(this).attr("data-mkey", curObj.meta_key);
						$(this).attr("data-del_nonce", curObj.del_nonce);
						var $midInp = $("<input type='hidden'>").val(curObj.meta_id);
						var midInpName = $(this).attr("name").replace("[value]", "[mid]");
						$midInp.attr("name", midInpName);
						$(this).after($midInp);
					}
				}

				var inpId = $(this).attr("id");
				mlChangeTabEmpty(this, $("a[href='#" + inpId + "-wrap']").parent());
			});
		}, 'json').always( function() { $(the_tr).find(".morelang-meta-locale").prop("readonly", false); } );

		$(the_tr).find("input.updatemeta[type='submit']").on("click", function() {
			submitLocaleMetas(this, the_tr, postId);
		});
	}


	/* Processing all meta rows with single request */
	function processMetaListRowsMerged() {
		jQuery.post(ajaxurl, {action: 'ml-get-all-metas', post_id: postId,
				_ajax_nonce: $("tbody#the-list tr").find("#_ajax_nonce").val()}, function(resp) {
			if (! (resp instanceof Object)) resp = {};

			$("tbody#the-list tr").each( function() {
				var the_tr = this;
				if ( $(the_tr).attr("data-handled") ) return;

				createLocaleInputs(the_tr);
				var metaName = $(the_tr).find("input[name^=meta]").val();

				/* Processing each meta row */
				$(the_tr).find(".morelang-meta-locale").each( function() {
					var curLoc = $(this).attr("data-locale");
					var curObj = undefined;
					if ( resp[curLoc] && resp[curLoc][metaName] ) {
						curObj = resp[curLoc][metaName];
					}
					if ( curObj instanceof Object ) {
						$(this).val( curObj.value );
						if (curObj.meta_id) {
							$(this).attr("data-mid", curObj.meta_id);
							$(this).attr("data-mkey", curObj.meta_key);
							$(this).attr("data-del_nonce", curObj.del_nonce);
							var $midInp = $("<input type='hidden'>").val(curObj.meta_id);
							var midInpName = $(this).attr("name").replace("[value]", "[mid]");
							$midInp.attr("name", midInpName);
							$(this).after($midInp);
						}
					}

					var inpId = $(this).attr("id");
					mlChangeTabEmpty(this, $("a[href='#" + inpId + "-wrap']").parent());
				});

				$(the_tr).find("input.updatemeta[type='submit']").on("click", function() {
					submitLocaleMetas(this, the_tr, postId);
				});

				$(the_tr).attr("data-handled", true);
			} );
		}, 'json' );
	}


	processMetaListRows();

	/* Processing the "Add New Custom Field" section */
	$("table#newmeta tbody tr:first-child").each(function() {
		createLocaleInputs(this);
	});

	$("tbody#the-list").on("wpListAddEnd", function(evt, s, wpList) { // See "wp-includes/js/wp-lists.js"
		processMetaListRows();
	});
}


} ); } )(jQuery); // end '$(document).ready'
