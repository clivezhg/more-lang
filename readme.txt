=== More-Lang ===
Contributors: Clive Zheng
Donate link: https://www.paypal.me/CliveZheng
Tags: multilingual, multilanguage, i18n, localization, translation, ClassicPress, language, bilingual, international, language switcher
Requires at least: 3.8
Tested up to: 5.8
Stable tag: 2.5.9
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

More-Lang is a multilingual support plugin. With clean design & simple admin UI, it is very easy to use.


== Description ==

More-Lang has some excellent features:

* Clean design. More-Lang saves each localized post text as a custom field, this brings some benefits: it will not leave any trace on the UI after deactivation; it can minimize the DB space usage.
* More-Lang works in such a way: it treats the configured languages as a default language plus any number of extra lanuages, the default language works in the default way of Wordpress, More-Lang handles the extra languages. That's why it is named __More__-Lang.
* Simple & easy to use admin UI.
* In-place language switchers & editors on the admin panels.
* There are built-in editors for: posts, taxonomy terms, menus, widgets, medias, general settings.
* RTL languages support.
* Hreflang tags generation for multilingual SEO.
* ClassicPress is supported.


== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/more-lang` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the 'More-Lang Plugin Settings' screen to configure the plugin.
4. The "Settings -> Permalinks -> Permalink Settings" should not be "Plain".
5. If you want to clear all the More-Lang data, firstly, you need to set __Clear the More-Lang data when "Delete" More-Lang on the Plugins panel__ on the 'More-Lang Plugin Settings' screen, then delete the More-Lang plugin through the 'Plugins' screen.


== Frequently Asked Questions ==

= How to customize the frontend language switcher styles? =

More-Lang provides two filters:

* 'morelang_front_cssurl', filters the css url styling the language switcher. Parameters: '$front_css_url', the built-in css url.
* 'morelang_flag_url', filters the flag icon urls. Parameters: '$img_src', the original url; '$locale', the current requested locale; '$position', where to be placed, it can be "menu" or "panel".
After the release 1.8.5, you can also check "Do not use the default styles for the language switchers", then 'morelang_front_cssurl' filter is not needed.

= How to localize external plugins? =

* The "Enable compatibility with Plugins & Themes in some special cases" option provides solutions for some compatibility issues.
* If the plugin uses short codes, it's usually very simple: create short code for every language, then paste the short codes to the corresponding contents.
* The premium add-on [More-Lang Pro](https://www.morelang.com/tutorial/ "More-Lang Pro") provides support for WooCommerce, translations of options, and URI-map, etc.
* If the plugin uses widgets, try this way: find out the filters used for the texts, add your filter handlers in your theme to return localized texts.

= How to translate text without an in-place editor? =

More-Lang provides in-place editors as far as possible. If it does not provide in certain cases(i.e., the Name of any Custom Field, or the Name of any non-taxonomy Product attribute), you can translate in the "Translations" page,
 and in most cases(the only exception is the `the_meta()` function), you need to make code change to get the localized text, there are 2 approaches:

* Use the translating filter:&nbsp;&nbsp; `apply_filters('morelang_translate_text', $default_text)` &nbsp;&nbsp;.
* Use the translating function:&nbsp;&nbsp; `_ml_t($default_text, $any='')` &nbsp;&nbsp;. This approach is more concise. Its shortcoming is that it is a function,
 if you deactivate the More-Lang plugin, your site may fail. To avoid the fail, you can add a fallback function in your theme's functions.php file, e.g.:

	`if (! function_exists( '_ml_t' )) { function _ml_t($default_text, $any='') { return $default_text; }; }`

> The `$any` parameter is optional, `_ml_t()` doesn't use it at all. You can simply ignore it, or set it to the `$domain` of the Wordpress `__($text, $domain)` function,
   then if one day you would like to uninstall the More-Lang plugin, you can simply replace "_ml_t" with "__" to use the default approach of Wordpress.

After you make code change in the above ways, you will have some texts needed to be translated. You can add the texts to the translation page automatically:

* Implement the filter `morelang_texts_to_translate` (parameter: $arr), then More-Lang will fill the translation table with the texts returned by the filter, e.g.:

	`add_filter( 'morelang_texts_to_translate', function( $arr ) { return array_merge($arr, ['text 1', 'text 2', 'text 3']); } );`

> There's very good reason for not providing in-place editors for the Names of Custom Fields(and similar scenarios):
  the in-place editors can appear in many Posts, which can lead to inconsistent translation.

= What about reordering or modifying the supported languages? =

* No additional work is required for reordering the non-default languages.
* If you change the default language to another language after entering any content for either of the two languages,
 the link from the content to the language will be broken, you need to re-enter the content.
* No additional work is required adding new languages.
* No additional work is required for modifying all the fields except the Locale.
* If you modify a Locale after entering any content for the language, the link from the content to the language will be broken, you need to re-enter the content.

= Where are the More-Lang data saved? =

* The setting data is saved in the "wp_options" table, with "morelang_nml_option" as the option_name.
* The localized post title, post content, post excerpt, custom fields and menu items are saved in "wp_postmeta" table.
* All the other localized options are saved in the "wp_options" table.

All the More-Lang related data have "morelang_nml_" in their keys or names, which can be used to identify them.


== Screenshots ==

1. The configuration page. Three languages are configured here("English" & "Deutsch" & "中文", "English" is the default). You can add more languages from the "Add Locale" section. You can get help by hovering on the "?" icon.
2. Multilingual "Site Title" & "Tagline" editor.
3. Posts: Multilingual "Title" & "Content" editor.
4. Posts: Multilingual "Excerpt" & "Custom Fields" editor. Note: the Names of Custom Fields should be translated in the "Translations" page.
5. Multilingual taxonomy terms editor.
6. Multilingual menu items editor. More-Lang provides a language switcher menu item.
7. Multilingual widgets editor (see Known Issues for Wordpress 5.8+). More-Lang provides a language switcher widget. Note: for a newly added widget, the More-Lang editors will not get activated; only after the widget is saved, the More-Lang editors get activated.
8. Autosave & revision management. More-Lang provides excellent support for autosave & revision.
9. Translating of any text. If an in-place editor is not present, you can translate the text here, see more details in the FAQ.
10. The support for the Gutenberg editor introduced in Wordpress 5.0. The "Update"/"Publish" buttons will only update the default language. The "Update Translation" button will update all the localized versions.
11. The support for the medias.
12. The support for compatibility with Plugins & Themes in some special cases. The "Special" menu item will not be shown if the option is not checked.


== Known Issues ==

* Autosave is not supported for the Wordpress releases before 4.6 or after 5.0.
* Array values(same meta_key for multiple metas in a post) are not supported for the Custom Fields.
  Array values in the Custom Fields are seldom used, if you do need this, try another approach: save Array values in a field, delimited by special characters.
* Since Wordpress 5.8, the block widget editor was introduced, which is not supported by More-Lang. You can choose one of the following solutions:
  1. Get More-Lang Pro, open the "Translate Options" screen, add "widget_block" to the expected list.
  2. Use the https://wordpress.org/plugins/classic-widgets/ plugin to resore the previous WordPress widgets settings screens.


== Changelog ==

= 2.5.9 (2021-10-08) =

* Some trivial changes.

= 2.5.8 (2021-08-31) =

* Improved the More-Lang options fetching.

= 2.5.7 (2021-08-05) =

* Updated the "Tested up to" to Wordpress 5.8.

= 2.5.6 (2021-06-04) =

* Improved the autosaving handling in the block editor.
* Improved the medias insertion handler.
* Improved the styles of language switcher in the block editor.

= 2.5.5 (2021-03-31) =

* Added interface to translate Medias for the other plugins.

= 2.5.4 (2021-03-23) =

* Improved the Post Excerpt filter.

= 2.5.3 (2021-02-26) =

* Added support for the navigation in media library.

= 2.5.2 (2021-02-08) =

* Fixed a compatibility issue with newer WooCommerce Product Editor: Content change might be ineffective.

= 2.5.1 (2020-12-16) =

* Updated the "Tested up to" to Wordpress 5.6.

= 2.5.0 (2020-11-01) =

* Added horizontal switcher style support.

= 2.4.3 (2020-10-21) =

* Improved the documentation.
* Improved the language switcher labels in the "Translate" page.

= 2.4.2 (2020-09-30) =

* Fixed a capability issue: A user without the "manage_options" capability can't open the admin dashboard.

= 2.4.1 (2020-09-16) =

* Improved the Post content filter.

= 2.4.0 (2020-08-21) =

* Added link filters input for links missing language.
* Added shortcode for adding language to links.
* Improved the browser redirection by preferred languages.
* Improved the search SQL compatibility.

= 2.3.0 (2020-08-01) =

* Added support for compatibility with Plugins & Themes in some special cases.

= 2.2.0 (2020-07-21) =

* Added hreflang tags generation.

= 2.1.2 (2020-07-15) =

* Added more pre-defined locales.

= 2.1.1 (2020-07-11) =

* Added more pre-defined locales.

= 2.1.0 (2020-07-01) =

* Added more pre-defined locales, and improved the selection display.

= 2.0.1 (2020-06-19) =

* Fixed the deprecated 'dbx_post_advanced' & 'category_link' warnings in the 'WP_DEBUG' mode.
* Improved the saving notices on the block editor.

= 2.0.0 (2020-06-09) =

* Fixed the "not allowed to edit the ... custom field" issue arising when woking with some plugins.
* Improved the styles of the pop-up language switcher on the block editor.

= 1.9.3 (2020-05-19) =

* Added tooltips to the "Update..." buttons in the block editor.
* Added invalid Permalink checking.

= 1.9.2 (2020-04-10) =

* Improved the styles of More-Lang components in the block editor (since Wordpress 5.4 changed the related styles).

= 1.9.1 (2020-03-19) =

* Improved the handler of the media selection.
* Improved the styles of the custom fields in the block editor.
* Improved the styles of More-Lang components in the block editor.

= 1.9.0 (2020-03-11) =

* Added support for the medias.
* Added custom fields support for the block editor.

= 1.8.10 (2020-02-03) =

* Improved the performance of the custom fields request.
* Improved the compatibility with some plugin & theme.
* Improved the plugin deletion processing.
* Improved some labels.
* Added the Pro link.

= 1.8.9 (2019-10-09) =

* Fixed a menu item issue: Custom Link with relative URL didn't work for the default language.
* Added HTML entity support to the "CSS Selector" input on the settiong page.
* Improved the setting page tooltips & readme.txt.

= 1.8.8 (2019-08-01) =

* Fixed the deprecated function 'create_function' issue in dev mode of PHP 7.2+.
* Some other trivial improvements.

= 1.8.7 (2019-06-07) =

* Fixed a Setting page issue: not working in specific environment.
* Some other trivial improvements.

= 1.8.6 (2019-05-01) =

* Improved the interfaces for extension.
* Improved the UI.

= 1.8.5 (2019-03-31) =

* Added the "Do not use the default styles for the language switchers" option.
* Improved the usability of the Translating page.
* Improved the interfaces for extension.

= 1.8.4 (2019-03-15) =

* Fixed a WP_Widget_Text issue: not showing toolbar & language switcher.
* Improved the plugin setting page.
* Improved the loading of js files.

= 1.8.3 (2019-03-03) =

* Improved RTL support for the Gutenberg editor.
* Improved revision creation for the Gutenberg editor.
* Some other trivial improvements(autosave control, empty tip, faq link).

= 1.8.2 (2019-02-16) =

* Added RTL support for the Gutenberg editor.
* Added revision creation for the Gutenberg editor.

= 1.8.1 (2019-01-26) =

* Added the support for the Gutenberg editor meta blocks.

= 1.8.0 (2019-01-01) =

* Added the support for the Gutenberg editor (the support for the meta blocks was not completed).

= 1.7.3 (2018-11-16) =

* Improved the search results.
* Improved the data format of taxonomy-terms.

= 1.7.2 (2018-11-08) =

* Added the localization of term-description.
* Added the translating function: `_ml_t($default_text, $any='')`.

= 1.7.1 (2018-10-26) =

* Some minor changes: document improvement, and little code refactor.

= 1.7.0 (2018-10-10) =

* Added the support for translating of any text, which can be used if an in-place editor is not present.

= 1.6.8 (2018-08-11) =

* Improved the plugin extension interface (this change has no impact on the previous installations).

= 1.6.7 (2018-06-28) =

This version fixed 2 compatibility issues for older Wordpress:
* Fixed missing argument warnings for the 'get_terms' & '_wp_post_revision_fields' filters in pre Wordpress 4.6.

= 1.6.6 (2018-06-26) =

* Fixed an 'is_rtl()' calling issue (function undefined) in pre Wordpress 4.6.

= 1.6.5 (2018-06-18) =

This version includes some minor usability changes:
* Changed the revision comparing lang-heading from locale to language name.
* Changed the rich editor language switcher styles to display scroll-y-bar only when the height overflows.
* Changed the action button styles.

= 1.6.4 (2018-06-13) =

* Added the "pop-up language switcher on the rich editors" option.
* Fixed a WP_Widget_Text display issue: the default value is displayed after saving, & not sync with the returned values.
* Fixed a WP_Widget_Text saving issue: not save the inputed in text mode.

= 1.6.3 (2018-06-03) =

* Added support for the "wp-login.php" URLs.
* Fixed a RTL styling issue in the case that the default language is RTL.

= 1.6.2 (2018-06-01) =

* Added setting RTL style if the default language is RTL.
* Added the language switcher for the "login" and "register" pages.
* Added the "submit" button state(enabled/disabled) change according to the setting change.
* Improved the recovery of previous language selection.

= 1.6.1 (2018-05-18) =

* Added setting RTL direction even when the corresponding language pack is not installed.
* Added language selection recovery for async-widgets: "Image", "Audio", "Video", "Gallery", "Custom HTML" and "Text".

= 1.6.0 (2018-05-08) =

* Added the RTL languages support.
* Keeping language info when searching.
* Added the recovery function of previous language selection.

= 1.5.1 (2018-03-31) =

* The improvement of autosave support.
* The improvement of "Missing Content Placeholder" option.

= 1.5.0 (2018-03-19) =

* Added "Language" column to the Posts lists to show the translation status of each Post.
* Fixed an overwriting issue when creating new autosave record.
* Few other trivial changes.

= 1.4.0 (2018-03-11) =

* Added the support for Autosave (the Wordpress releases before 4.6 are not supported).
* Added the management of localized fields in revisions.

= 1.3.2 (2018-02-26) =

* Fixed the error of '$this' parameter in PHP7+.
* Added the support for WP_Widget_Custom_HTML in WP4.8.X.
* Added the support for the WP_Widget_Text in WP4.8~WP4.8.1.

= 1.3.1 (2018-02-11) =

* Added the localization of the WP_Widget_Custom_HTML.

= 1.3.0 (2018-01-31) =

This version added support for the widgets upgrade in WP4.8~WP4.9:
* Added the localization of the WP_Widget_Text rich editor.
* Added the localization of widget titles of Image, Audio, Video, Gallery, Custom HTML.
