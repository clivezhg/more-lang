<style type="text/css">
<?php
global $ml_registered_mlocales;
foreach ( $ml_registered_mlocales as $mlocale ) {
	$echoL = function() use($mlocale) { echo '_' . $mlocale; };
?>

#excerpt<?php $echoL(); ?> {
    display: block;
    /*margin: 12px 0 0;*/
    margin: 3px 0 0;
    height: 4em;
    width: 100%;
}

<?php
}

/*
 * Begin - TinyMCE language switch button styles. Cloned from "wp-includes/css/editor.css".
 */
if ( version_compare( $GLOBALS['wp_version'], '4.1', '>=' ) ):
?>
.ml-switch-editor {
	float: left;
	-webkit-box-sizing: content-box;
	-moz-box-sizing: content-box;
	box-sizing: content-box;
	position: relative;
	top: 1px;
	background: #ebebeb;
	color: #777;
	cursor: pointer;
	font: 13px/19px "Open Sans", sans-serif;
	height: 20px;
	margin: 5px 0 0 5px;
	padding: 3px 8px 4px;
	border: 1px solid #e5e5e5;
}

.ml-switch-editor:focus {
	-webkit-box-shadow:
		0 0 0 1px #5b9dd9,
		0 0 2px 1px rgba(30, 140, 190, .8);
	box-shadow:
		0 0 0 1px #5b9dd9,
		0 0 2px 1px rgba(30, 140, 190, .8);
	outline: none;
	color: #23282d;
}

.ml-switch-editor:active,
.html-active .switch-html:focus,
.tmce-active .switch-tmce:focus {
	-webkit-box-shadow: none;
	box-shadow: none;
}

.ml-switch-editor:active {
	background-color: #f5f5f5;
	-webkit-box-shadow: none;
	box-shadow: none;
}
<?php
	if ( version_compare( $GLOBALS['wp_version'], '4.2', '<' ) ):
?>
.ml-switch-editor:focus {
	color: #222;
}
<?php
	endif; // < 4.2
endif; // >= 4.1
?>
<?php
if ( version_compare( $GLOBALS['wp_version'], '4.1', '<' ) ):
?>
<?php
	if ( version_compare( $GLOBALS['wp_version'], '3.8', '>=' ) ):
?>
.ml-switch-editor {
	background: #ebebeb;
	border: 1px solid #dedede;
	color: #777;
	cursor: pointer;
	float: right;
	font: 13px/19px "Open Sans", sans-serif;
	height: 19px;
	margin: 5px 0 0 5px;
	padding: 3px 8px 4px;
	position: relative;
	top: 1px;
}

.ml-switch-editor:active {
	background-color: #f1f1f1;
}

.ml-switch-editor:hover {
	text-decoration: none !important;
	background: #fff;
}
<?php
	else:
?>
.ml-switch-editor {
	height: 18px;
	font: 13px/18px Arial,Helvetica,sans-serif normal;
	margin: 5px 5px 0 0;
	padding: 4px 5px 2px;
	float: right;
	cursor: pointer;
	border-width: 1px;
	border-style: solid;
	-webkit-border-top-right-radius: 3px;
	-webkit-border-top-left-radius: 3px;
	border-top-right-radius: 3px;
	border-top-left-radius: 3px;
	background-color: #f1f1f1;
	border-color: #dfdfdf #dfdfdf #ccc;
	color: #999;
}

html[dir="rtl"] .ml-switch-editor {
	float: left;
}

.ml-switch-editor:active {
	background-color: #f1f1f1;
}

.ml-switch-editor:hover {
	text-decoration: none !important;
}
<?php
	endif;
?>
<?php
endif; // < 4.1
?>
<?php /* The following are in 3.8~4.4, not in 3.6 ＆ 3.7 */ ?>
/* =Localization
-------------------------------------------------------------- */
.rtl .ml-switch-editor {
	font-family: Tahoma, sans-serif;
}

html:lang(he-il) .rtl .ml-switch-editor {
 	font-family: Arial, sans-serif;
}
<?php /* The following will fix the issues */ ?>
.ml-switch-editor { float: left; }
<?php
/*
 * End - TinyMCE language switch button styles.
 */
?>

table#ml-langcfg-tbl tbody tr:first-child td:last-child::before {
	content: "<?php _e( '← (Default)', 'morelang' ) ?>";
	color: darkred;
}
</style>
