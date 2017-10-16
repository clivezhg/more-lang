(function($) { $(document).ready( function() {
/* The localization of the custom meta fields in a post*/


if ( $('form#post #postcustom').length && mlLangReady() ) {
	var pre_id_val = {};
	var postId = $("form#post input#post_ID").val();

	/* Processing the metas list section */
	function processMetaListRows() {
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
			var $newInp = $inp.clone();
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
	function submitLocaleMetas(btn, the_tr, postId, meta_name, nonce) {
		var dataobj = {};
		dataobj.action = 'ml-add-update-meta';
		dataobj.post_id = postId;
		dataobj.metaname = meta_name;
		if (! nonce) {
			var datawp = $(btn).attr('data-wp-lists');
			var add_nonce_match = datawp.match( /.*_ajax_nonce-add-meta=([a-z0-9]+)/i );
			if (add_nonce_match && add_nonce_match.length >= 2) dataobj["_ajax_nonce-add-meta"] = add_nonce_match[1];
		}
		else dataobj["_ajax_nonce-add-meta"] = nonce;
		$(the_tr).find(":input.morelang-meta-locale").each(function() {
			pre_id_val[ $(this).attr("id") ] = $(this).val();
			dataobj.locale = $(this).attr("data-locale");
			dataobj.metavalue = $(this).val();
			if ( $(this).attr("data-mid") ) dataobj.meta_id = $(this).attr("data-mid");
			jQuery.post(ajaxurl, dataobj, function(response) {
				console.log('(ml-add-update-meta) Got this from the server:', response);
			});
		});
	}

	/* Processing a meta row */
	function processMetaListRow(the_tr) {
		createLocaleInputs(the_tr);
		$(the_tr).find(".morelang-meta-locale").prop("readonly", true);
		var getMetaName = function() { return $(the_tr).find("input[name^=meta]").val(); };
		jQuery.post(ajaxurl, {action: 'ml-get-meta', post_id: postId, metaname: getMetaName()}, function(resp) {
			$(the_tr).find(".morelang-meta-locale").each(function() {
				var curLoc = $(this).attr("data-locale");
				var curObj = resp[curLoc];
				if ( typeof curObj === 'object' ) {
					$(this).val( curObj.value );
					if (curObj.meta_id) {
						$(this).attr("data-mid", curObj.meta_id);
						var $midInp = $("<input type='hidden'>").val(curObj.meta_id);
						var midInpName = $(this).attr("name").replace("[value]", "[mid]");
						$midInp.attr("name", midInpName);
						$(this).after($midInp);
					}
				}
				var inpId = $(this).attr("id");
				mlChangeTabEmpty(this, $("a[href='#" + inpId + "-wrap']").parent());
			});
		}, 'json').always(function() { $(the_tr).find(".morelang-meta-locale").prop("readonly", false); });
		$(the_tr).find("input.updatemeta[type='submit']").on("click", function() {
			submitLocaleMetas(this, the_tr, postId, getMetaName() )
		});
		$(the_tr).find("input.deletemeta[type='submit']").on("click", function() {
			var dataobj = {};
			dataobj.action = 'ml-del-meta';
			dataobj.meta_id = $(this).attr("id").replace(/deletemeta|(\[)|(\])/g, "");
			dataobj.post_id = postId;
			dataobj.metaname = getMetaName();
			var datawp = $(this).attr('data-wp-lists');
			var ajax_nonce_match = datawp.match( /.*_ajax_nonce=([a-z0-9]+)/i );
			if (ajax_nonce_match && ajax_nonce_match.length >= 2) dataobj["_ajax_nonce"] = ajax_nonce_match[1];
			jQuery.post(ajaxurl, dataobj, function(response) {
				console.log('(ml-del-meta) Got this from the server:', response);
			});
		});
	}

	processMetaListRows();

	/* Processing the "Add New Custom Field" section */
	$("table#newmeta tbody tr:first-child").each(function() {
		createLocaleInputs(this);
	});
	$("table#newmeta #newmeta-submit").on("click", function() {
		submitLocaleMetas( this,
				$("table#newmeta tbody tr:first-child").get(0),
				postId,
				$("table#newmeta").find("#metakeyselect:visible, #metakeyinput:visible").val(),
				$("table#newmeta").find("#_ajax_nonce-add-meta").val() );
	});

	$("tbody#the-list").on("wpListAddEnd", function(evt, s, wpList) { // See "wp-includes/js/wp-lists.js"
		processMetaListRows();
	});
}


} ); } )(jQuery); // end '$(document).ready'
