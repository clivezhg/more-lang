/* The localization of the medias.
 */


(function($) { $(document).ready( function() {


if ( window.wp && wp.media && mlLangReady() ) {

	var changes = ["alt", "title", "caption", "description", "artist", "album"];
	var dataCache = {};


	/* The main Media Library editor */
	if ($('#wp-media-grid').length && wp.media.frame) {
		wp.media.frame.on( "edit:attachment", function() {
			processAttachmentMetas( wp.media.frame.model, true );

			wp.media.frame.on( "refresh", function() { // handle the navigation (not work if out of the nesting function)
				processAttachmentMetas( wp.media.frame.model, true );
			} );
		} );

		return;
	}


	/* Handle the Post editors.
	   In the classic editor, 'wp.media.frame' is not instantiated before opening the media modal,
	      and there will be only one instance.
	   In the block editor, 'wp.media.frame' can refer to multiple instances during the lifecycle.
	   The Widgets(and most other panels?) are similar to the block editor.
	 */
	var handledFrames = [];
	wp.media.view.Modal.prototype.on( "ready", function() {
		var frame = wp.media.frame;
		if ( handledFrames.indexOf(frame) < 0 ) {
			processMediaFrame( frame );
			handledFrames.push( frame );
		}

		/* In some non-classic editors, 'wp.media.frame' are not always set to the expected value - the lastest opened?
		   If so, can add 'wp.media.frame=this.frame;' before 'this.frame.open()' in the "media-utils.js" to fix it.
		   After fixing, the following workaround block can be removed	*/
		if ( (! $("form#post #poststuff").length) &&
				wp.media.frame.modal && wp.media.frame.modal._events && wp.media.frame.modal._events.ready ) {
			var ready= wp.media.frame.modal._events.ready;
			for (var i = 0; i < ready.length; i++) {
				var obj = ready[i];
  				if (obj.ctx && obj.ctx.controller && obj.ctx.controller._state === "library") {
					if ( handledFrames.indexOf(obj.ctx.controller) >= 0 ) continue;
					processMediaFrame( obj.ctx.controller );
					handledFrames.push( obj.ctx.controller );
				}
			}
		}
	} );


	var lastMediaButtonClickTime = 0; // To record the time of ".media-button" being clicked.

	/* Process a media frame instance */
	function processMediaFrame( frame ) {
		if ( ! (frame instanceof Object) || ! (frame["$el"] instanceof jQuery) ) {
			console.log("More-Lang processMediaFrame: invalid frame", frame);
			return;
		}

		/* Not use the "click" event, because the handler needs to be fired before the "close" event */
		frame["$el"].find(".media-button").on("mouseup", function() {
			lastMediaButtonClickTime = Date.now();
		});


		var selection = getSelectionObj();
		if ( selection ) {
			selection.on("selection:single", selectionHandler);
			// "selection:toggle" also works
		}


		/* Get the selection object */
		function getSelectionObj() {
			var state = null;
			state = frame.state();
			var selection = null;
			if (state) selection = state.get("selection");

			return selection;
		}


		/* When a media item is selected */
		function selectionHandler( model ) {
			processAttachmentMetas( model, false );
		}


		/* The "close" event will be triggered after clicking Select | Insert..., or closing without selection */
		frame.on( "close", function() {
			/* "Select"|"Insert" is not clicked */
			if (Date.now() - lastMediaButtonClickTime > 600) {
				return;
			}

			if ( frame["$el"].hasClass("media-widget") ) {
				return;
			}

			insertionHandler();
		} );


		/* Localize the data of selected medias */
		function localizeSelectedModels(selection, data, locSel) {
			for (var k = 0; k < selection.models.length; k++) {
				var model = selection.models[k];
				if (! isValidModel( model )) continue;

				var mid = model.get('id');
				if ( (data[mid] instanceof Object) && (data[mid][locSel] instanceof Object)
						&& (model.attributes instanceof Object) ) {
					var tranObj = data[mid][locSel];
					for (var n = 0; n < changes.length; n++) {
						var chg = changes[n];
						if ((typeof tranObj[chg] === "string") && tranObj[chg]) {
							model.attributes[chg] = tranObj[chg];
						}
					}
				}
			}
		}


		/* Process after the Selection or Insertion of medias */
		function insertionHandler() {
			var nameSel = window.wpCookies && wpCookies.get("morelang-langtab-sel");
			var locSel = "";
			for (var i = 1; i < ml_registered_langs.length; i++) {
				if ( ml_get_admin_lang_label(ml_registered_langs[i]) === nameSel ) {
					locSel = ml_registered_langs[i].locale;
				}
			}
			if (! locSel) return; // Skip for the default language

			var selection = getSelectionObj();
			if ( selection ) {
				if (selection.models instanceof Array && selection.models.length > 0) {
					var postIds = [];
					for (var i = 0; i < selection.models.length; i++) {
						var pid = parseInt( selection.models[i].id );
						if ( pid ) {
							if( dataCache[pid] ) continue;
							postIds.push( pid );
						}
					}
					if ( postIds.length === 0 ) {
						localizeSelectedModels(selection, dataCache, locSel);
						return;
					}

					var optGet = {};
					optGet.data = { action: 'ml-get-attachment-metas', post_ids: postIds };
					optGet.async = false;
					optGet.timeout = 3000;

					optGet.success = function ( data ) {
						$.extend(true, dataCache, data);
						localizeSelectedModels(selection, dataCache, locSel);
					}

					optGet.error = ajaxErrorHandler;

					wp.media.ajax( optGet );
				}
			}
		}

	} // end 'processMediaFrame'


	function ajaxErrorHandler( xmlhttprequest, textstatus, message ) {
		console.log("More-Lang error:", textstatus, message);
	}


	/* Check if the model is valid */
	function isValidModel( model ) {
		return (model instanceof Object) && (typeof model.get === "function") && parseInt(model.get('id'));
	}


	/* Process the attachment metas on the current page */
	function processAttachmentMetas( model, twoColumn ) {
		if (! isValidModel( model )) return;

		var id = model.get('id');
		if ( dataCache[id] ) {
			createLocalizedInputs(dataCache[id], twoColumn, model);
			return;
		}

		var optGet = {};
		optGet.data = { action: 'ml-get-attachment-metas', post_id: id };

		optGet.success = function ( arg ) {
			if (arg instanceof Object) {
				var newData = {};
				newData[id] = arg;
				$.extend(true, dataCache, newData);
			}
			else return;
			createLocalizedInputs(arg, twoColumn, model);
		}

		optGet.error = ajaxErrorHandler;

		wp.media.ajax( optGet );
	}


	/* Create localized attachment inputs */
	function createLocalizedInputs( savedData, twoColumn, model ) {
		if (! (savedData instanceof Object)) savedData = {};
		var $media_setting_container = $(".edit-attachment-frame .attachment-info, .media-modal .attachment-details");
		if ( $media_setting_container.length > 0 ) {
			var inpIds = ["attachment-details-two-column-alt-text", "attachment-details-two-column-title",
					"attachment-details-two-column-caption", "attachment-details-two-column-description",
					"attachment-details-two-column-artist", "attachment-details-two-column-album"];
			for (var memb in inpIds) {
				var inpId = inpIds[memb];
				if ( ! twoColumn ) {
					inpId = inpId.replace("two-column-", "");
				}
				var $inp = $media_setting_container.find("#"+inpId);
				if ($inp.length > 0 && $inp.attr("data-ml-handled") !== "true") {
					$inp.parent().find(".ml-ui-tabs").remove(); // in case the function is called multiple times
					var newInps = [];
					for ( var i = ml_registered_langs.length-1; i >= 1; i-- ) {
						var regLang = ml_registered_langs[i];
						var $newInp = mlCloneInputWrap( $inp.eq(0).parent(), regLang, ml_uid ).find(":input");
						$newInp.val('');
						if ( savedData[regLang.locale] && savedData[regLang.locale][changes[memb]] ) {
							$newInp.val( savedData[regLang.locale][changes[memb]] );
						}
						$newInp.attr("data-ml-locale", regLang.locale);
						$newInp.attr("data-ml-change", changes[memb]);
						newInps.push( $newInp );
					}
					for (var k = 0; k < newInps.length; k++) {
						$inp.after( newInps[k] );
					}
					mlChangeInputPosImpl(inpId, ml_uid+inpId);
					$inp.attr("data-ml-handled", true);

					$media_setting_container.find("#ml-widget-tabs-" + inpId).on("change", ":input", function(evt) {
						inputChangeHandler(evt, model);
						return false; // otherwise the default will be processed
					});

					/* Clicking should not swtich tabs in other panels, and set the "morelang-langtab-sel" cookie */
					$media_setting_container.find(".ml-ui-tabs li a").attr("data-not_chg_langsel", true);
				}
			}
		}
	}


	/* Save localized attachment data via AJAX after input changing */
	function inputChangeHandler( evt, model ) {
		if ( $(evt.target).closest(".media-widget").length ) {
			return;
		}

		if ( ! model.get('nonces') || ! model.get('nonces').update ) {
			console.log("ml-save-attachment: the model is not ready.")
			return;
		}

		var optSave = {};
		optSave.data = {
			action: "ml-save-attachment",
			changes: {},
			id: model.get('id'),
			nonce: model.get('nonces').update,
			locale: $(evt.target).attr("data-ml-locale"),
			post_id: 0
		}
		optSave.data.changes[ $(evt.target).attr("data-ml-change") ] = $(evt.target).val();

		optSave.success = function() {
			var postData = {};
			postData[optSave.data.id] = {};
			postData[optSave.data.id][optSave.data.locale] = optSave.data.changes;
			$.extend(true, dataCache, postData);
			$(".attachment-details").removeClass("save-waiting save-ready").addClass("save-complete");
			setTimeout( function() {
				$(".attachment-details").removeClass("save-waiting save-complete").addClass("save-ready");
			}, 3000 );
		}

		optSave.error = ajaxErrorHandler;

		$(".attachment-details").removeClass("save-ready save-complete").addClass("save-waiting");

		wp.media.ajax( optSave );
	}

} // end 'if'


} ); } )(jQuery); // end '$(document).ready'
