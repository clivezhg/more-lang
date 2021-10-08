(function($) { $(document).ready( function() {
/* The configuration of compatibility with Plugins & Themes in some special cases */


if ( $('.ml-special-cfg #ml-langcfg-tbl').length > 0 ) {

	/* Initialize the values */
	function initForm() {
		var postMetaKeys = [];
		if (typeof ml_special_opt === "object" && ml_special_opt != null) {
			$("#ml-special-menu").prop("checked", ml_special_opt.ml_special_menu || false);
			$("#ml-special-widget").prop("checked", ml_special_opt.ml_special_widget || false);
			$("input[name=ml-special-delimiter][value=" + ml_special_opt.ml_special_delimiter + "]").prop("checked", true);
			if (ml_special_opt.ml_special_post_meta_keys instanceof Array) {
				postMetaKeys = ml_special_opt.ml_special_post_meta_keys;
			}

			$("#ml-special-filter-links").val(ml_special_opt.ml_special_filter_links || "");
		}

		$postMetaSel = $("#ml-special-post-meta-keys");
		var metaItems = [];
		if (window.ml_special_protected_post_meta_keys instanceof Object) {
			for (var m in ml_special_protected_post_meta_keys) {
				metaItems.push( ml_special_protected_post_meta_keys[m] );
			}
		}
		/* If a value is not in the '<option>'s, it cannot be set */
		for (var i = 0; i < postMetaKeys.length; i++) {
			if (metaItems.indexOf(postMetaKeys[i]) < 0) metaItems.push( postMetaKeys[i] );
		}
		for (var k = 0; k < metaItems.length; k++) {
			var $opt = $("<option></option>");
			var key = metaItems[k];
			$opt.val( key );
			$opt.text( key );
			$postMetaSel.append($opt);
		}
		$postMetaSel.val( postMetaKeys );
		var s2MetaInst = $postMetaSel.select2({tags: true});
	}

	initForm();


	/* Prepare the input data for submitting */
	$("#ml-special-form").on("submit", function() {
		var inputVal = getInputVal( true );
		$("#morelang_special_opt").val( inputVal );
		return true;
	});


	/* Update the state of "submit" button when changed */
	$(".ml-special-cfg #ml-langcfg-tbl").on("change input", "*", function(evt) {
		var inputVal = getInputVal();
		if ( JSON.stringify( window.ml_special_opt ) !== inputVal ) {
			$("input#submit").prop("disabled", false);
		}
		else {
			$("input#submit").prop("disabled", true);			
		}
	});

	$(".ml-special-cfg #ml-langcfg-tbl").find("tfoot").trigger( "change" );


	/* Gets the inputs as a JSON string */
	function getInputVal( focusInvalid ) {
		var ml_special_opt = {};

		ml_special_opt.ml_special_post_meta_keys = $("#ml-special-post-meta-keys").val();

		ml_special_opt.ml_special_menu = $("#ml-special-menu").prop("checked");
		ml_special_opt.ml_special_widget = $("#ml-special-widget").prop("checked");
		ml_special_opt.ml_special_delimiter = $("input[name=ml-special-delimiter]:checked").val();

		ml_special_opt.ml_special_filter_links = $("#ml-special-filter-links").val().trim();

		return JSON.stringify( ml_special_opt );
	};

}


} ); } )(jQuery); // end '$(document).ready'
