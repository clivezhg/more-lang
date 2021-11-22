More-Lang
=======

More-Lang is a multilingual support plugin for Wordpress. With clean design & simple admin UI, it is very easy to use.

More-Lang has some excellent features:

* Clean design. More-Lang saves each localized post text as a custom field, this brings some benefits: it will not leave any trace on the UI after deactivation; it can minimize the DB space usage.
* More-Lang works in such a way: it treats the configured languages as a default language plus any number of extra lanuages, the default language works in the default way of Wordpress, More-Lang handles the extra languages. That's why it is named __More__-Lang.
* Simple & easy to use admin UI.
* In-place language switchers & editors on the admin panels.
* There are built-in editors for: posts, taxonomy terms, menus, widgets, medias, general settings.
* RTL languages support.
* Hreflang tags generation for multilingual SEO.
* ClassicPress is supported.

Installation
-----

1. Upload the plugin files to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the 'More-Lang Plugin Settings' screen to configure the plugin.
4. The "Settings -> Permalinks -> Permalink Settings" should not be "Plain".
5. If you want to clear all the More-Lang data, firstly, you need to set __Clear the More-Lang data when "Delete" More-Lang on the Plugins panel__ on the 'More-Lang Plugin Settings' screen, then delete the More-Lang plugin through the 'Plugins' screen.

Usage
-----
1. The configuration page. Three languages are configured here("English" & "Deutsch" & "中文", "English" is the default). You can add more languages from the "Add Locale" section. You can get help by hovering on the "?" icon.
<img src="https://user-images.githubusercontent.com/22025586/137051739-ee393523-1601-48fe-917a-2e449b5adca4.png">
2. Multilingual "Site Title" & "Tagline" editor.
<img src="https://user-images.githubusercontent.com/22025586/137051744-78f062df-d973-4d92-adac-168475e77664.png">
3. Posts: Multilingual "Title" & "Content" editor.
<img src="https://user-images.githubusercontent.com/22025586/137051745-552ea626-7f6d-4298-994e-b8d753c9c9ec.png">
4. Posts: Multilingual "Excerpt" & "Custom Fields" editor. Note: the Names of Custom Fields should be translated in the "Translations" page.
<img src="https://user-images.githubusercontent.com/22025586/137051746-ce2a9124-faad-4bf3-aae1-323222b3a498.png">
5. Multilingual taxonomy terms editor.
<img src="https://user-images.githubusercontent.com/22025586/137051749-3422773a-e6c5-4ca2-bea0-d4bb0833b146.png">
6. Multilingual menu items editor. More-Lang provides a language switcher menu item.
<img src="https://user-images.githubusercontent.com/22025586/137051751-17eb4fd9-9ff4-48e3-b46c-f44378215859.png">
7. Multilingual widgets editor. More-Lang provides a language switcher widget. Note: for a newly added widget, the More-Lang editors will not get activated; only after the widget is saved, the More-Lang editors get activated.

Since Wordpress 5.8, the block widget editor was introduced, which is not directly supported by More-Lang currently. You can choose one of the following solutions:
  --Get More-Lang Pro, open the "Translate Options" screen, add "widget_block" to the expected list.
  --Use the https://wordpress.org/plugins/classic-widgets/ plugin to resore the previous WordPress widgets settings screens.
<img src="https://user-images.githubusercontent.com/22025586/137051755-b02d3b84-6ba7-4bfb-8fc9-70b950ac45bb.png">
8. Autosave & revision management. More-Lang provides excellent support for autosave & revision.
<img src="https://user-images.githubusercontent.com/22025586/137051757-6784d76b-f0f4-4428-b3cf-1d985cf8cef8.png">
9. Translating of any text. If an in-place editor is not present, you can translate the text here, see more details in the FAQ.
<img src="https://user-images.githubusercontent.com/22025586/137051761-1cc37c3f-b395-4165-adce-fd2c127ce5a2.png">
10. The support for the Gutenberg editor introduced in Wordpress 5.0. The "Update"/"Publish" buttons will only update the default language. The "Update Translation" button will update all the localized versions.
<img src="https://user-images.githubusercontent.com/22025586/137051762-130aab6a-d704-44b9-b41a-3297ff1afee5.png">
11. The support for the medias.
<img src="https://user-images.githubusercontent.com/22025586/137051763-d53a42ce-16c4-4677-85f2-dffe32c547d8.png">
12. The support for compatibility with Plugins & Themes in some special cases. The "Special" menu item will not be shown if the option is not checked.
<img src="https://user-images.githubusercontent.com/22025586/137051764-67de6bd2-39eb-415a-bb06-436af01aaf53.png">

Constraints
-----------

Copyright and license
---------------------
The license is available within the repository in the license.txt file.
