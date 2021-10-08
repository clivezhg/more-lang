(function($) { $(document).ready( function() {
/* More-Lang translating page */


var ML_TRANS_CHANGE = "ml_trans_change";

if ( $('#ml-trans-tbl').length > 0 ) {
	if ( ! mlLangReady() ) { // Not work if the languages are not configured
		var notreadyMsg = $('#ml-trans-wrap #ml-msg-notready').text();
		$('.ml-btn-panel').remove();
		$('#ml-trans-form').replaceWith( $('<div class="ml-err-msg"></div>').text(notreadyMsg) );
		return;
	}

	var $tblBody = $('#ml-trans-tbl tbody#ml-trans-tbody');
	var inputEle = "<input type='text'>";
	var textareaEle = "<textarea></textarea>";
	var rowIdx = 0;

	/* Adds a row for translating */
	function addTransRow(dftText, transTexts, isMulti) {
		var existed = false;
		$tblBody.find("td:first-child :input").each( function() {
			if ( $(this).val() === dftText && dftText !== "" ) existed = true;
		});
		if ( existed ) return; // avoid duplication

		var $newRow = $("<tr>").prependTo($tblBody);
		rowIdx++;
		var id_base = ml_uid + "keyname" + rowIdx;
		var inputHtml = isMulti ? textareaEle : inputEle;
		var $dftInput = $(inputHtml);
		$dftInput.attr("id", id_base).attr("name", id_base).appendTo("<td>").parent().appendTo($newRow);
		if (dftText) $dftInput.val( dftText );
		if ($dftInput.val() === "") $dftInput.focus();
		var $ntd = $("<td>");
		$newRow.append($ntd);
		$(inputEle).attr("id", id_base + "-placeholder").css("display", "none").appendTo($ntd);
		/* Adds localized inputs */
		for (var i = 1; i < ml_registered_langs.length; i++) {
			var lang = ml_registered_langs[i];
			var nameid = id_base + "_" + lang.locale;
			var $newInp = $(inputHtml);
			if ( typeof transTexts === "object" && transTexts[lang.locale] ) $newInp.val( transTexts[lang.locale] );
			$newInp.attr("id", nameid).attr("name", nameid).attr("data-langname", ml_get_admin_lang_label(lang)).attr("data-locale", lang.locale);
			mlSetInputDirection( $newInp.get(0) );
			$ntd.append( $("<p></p>").append($newInp) );
		}
		$newRow.append("<td><input type='checkbox'></td>");
		mlChangeInputPosImpl(id_base + "-placeholder", id_base + "_"); // Creates the tabs
		updSelAll();
	}

	$("#ml-add-single").on("click", function() {
		addTransRow();
	});
	$("#ml-add-multi").on("click", function() {
		addTransRow("", undefined, true);
	});

	/* Initialize the form controls & values */
	function initForm() {
		if ( mlLangReady() && typeof ml_trans_obj === "object" ) {
			for (var memb in ml_trans_obj) {
				var isMulti = false;
				if ( ml_trans_obj[memb] && ml_trans_obj[memb]['m'] === 1 ) isMulti = true;
				addTransRow(memb, ml_trans_obj[memb], isMulti);
			}
		}
		if (window.ml_texts_to_translate instanceof Array) { // 'instanceof' may result in unexpected results in multiple context
			for (var i = 0; i < ml_texts_to_translate.length; i++) {
				var isMulti = false;
				if (ml_texts_to_translate[i].search(/[\n\r]/) > 0) isMulti = true;
				addTransRow(ml_texts_to_translate[i], "", isMulti);
			}
		}
		if ( $tblBody.find("tr").length < 1 ) addTransRow('', {}, false);

		/* Create a language switcher for all the rows, on the top of the Translation column */
		addTransRow('', {}, false);
		var $tmpRow = $tblBody.find("tr:first-child");
		var $mlUiTabs = $tmpRow.find(".ml-ui-tabs");
		$mlUiTabs.appendTo("#ml-trans-tbl thead th:nth-child(2)");
		// $mlUiTabs.find(":input").remove(); // not do it, otherwise the <li> can get focused, and unexpected line will be shown
		$mlUiTabs.find(":input, .ui-tabs-panel").css( {width:0,height:0,padding:0,margin:0,borderWidth:0} );
		$mlUiTabs.find(".ml-tab-empty").removeClass("ml-tab-empty");
		$tmpRow.remove();
	}
	initForm();


	/* Prepares the input data for submitting */
	$("#ml-trans-form").on("submit", function() {
		var inputVal = getInputVal();
		if (invalidInput) {
			if (typeof invalidMsg === "string" && invalidMsg) {
				alert( invalidMsg );
			}
			return false;
		}
		$("#morelang_translation").val( inputVal );
		return true;
	});

	/* Updates the state of controls when changed */
	$("#ml-trans-tbl").on("change input " + ML_TRANS_CHANGE, "*", function() {
		var inputVal = getInputVal();
		if ( JSON.stringify( window.ml_trans_obj ) !== inputVal ) {
			$("input[type=submit]").prop("disabled", false);
		}
		else {
			$("input[type=submit]").prop("disabled", true);
		}
		$("#ml-del-selected").prop("disabled", $("#ml-trans-tbl tbody input[type='checkbox']:checked").length < 1);
	});
	$("#ml-trans-tbl").find("thead").trigger( ML_TRANS_CHANGE );

	/* Delete the selected rows */
	$("#ml-del-selected").on("click", function() {
		$("#ml-trans-tbl tbody input[type='checkbox']:checked").each( function(){ $(this).closest("tr").remove(); } );
		$("#ml-del-selected").prop("disabled", true);
		$("#ml-trans-tbl").find("tbody").trigger( ML_TRANS_CHANGE );
	} );

	$("#ml-trans-tbl").on("change input", "input[type='checkbox']", function(evt, rootSrc) {
		$(this).parent().parent()[$(this).prop("checked")? "addClass":"removeClass"]("ml-row-checked");
		$(this).parent().parent().find(":input:not(input[type=checkbox])").prop("disabled", $(this).prop("checked"));
		if (rootSrc != "from-sel-all" && $(this).attr("id") != "ml-sel-all") {
			updSelAll();
		}
	});
	$("#ml-sel-all").on("click", function() {
		var checked = $(this).prop("checked");
		$("#ml-trans-tbl tbody input[type='checkbox']").each( function() {
			if ( $(this).prop("checked") !== checked ) {
				$(this).prop("checked", checked).trigger("change", "from-sel-all");
			}
		} );
	}).prop("checked", false); // Firefox likes to remember the state
	function updSelAll() {
		var allChecked = $("#ml-trans-tbl tbody input[type='checkbox']:not(:checked)").length == 0
				&& $("#ml-trans-tbl tbody tr").length > 0;
		$("#ml-sel-all").prop("checked", allChecked).trigger("change");
	}

	var invalidInput = false;
	var invalidMsg = "";

	/* Gets the inputs as a JSON string */
	function getInputVal() {
		invalidInput = false;
		invalidMsg = "";
		var transObj = {};
		var idx = 0;

		$( $("#ml-trans-tbl tbody tr").get().reverse() ).each( function() {
			if (invalidInput) return;
			var $dftInput = $(this).find("td").eq(0).find("input, textarea");
			var $transInputs = $(this).find("td").eq(1).find("input, textarea"); // ":input" will get unexpected elements like 'button'.
			var dftText = $dftInput.eq(0).val().trim();
			if (! dftText) return; // Empty default text is not allowed
			var transTexts = {};
			if ( $dftInput.is("textarea") ) transTexts['m'] = 1;
			$transInputs.each( function() {
				var val = $(this).val().trim();
				if ( val ) {
					transTexts[$(this).attr("data-locale")] = $(this).val().trim();
				}
			});
			if ( transObj[dftText] ) {
				$dftInput.focus();
				invalidMsg = $('#ml-trans-wrap #ml-msg-duplicate').text();
				invalidInput = true;
				return;
			}

			transObj[dftText] = transTexts;
		});

		return JSON.stringify( transObj );
	};
}


} ); } )(jQuery); // end '$(document).ready'
