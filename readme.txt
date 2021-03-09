=== Custom Product Tabs for WooCommerce  ===
Contributors: yikesinc, eherman24, liljimmi, yikesitskevin, metalandcoffee, mialevesque
Donate link: http://yikesinc.com
Tags: woocommerce, product tabs, repeatable, duplicate, customize, custom, tabs, product, woo, commerce
Requires at least: 3.8
Tested up to: 5.7
Stable tag: 1.7.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add custom tabs with content to products in WooCommerce. 

== Description ==

This plugin extends [WooCommerce](www.woothemes.com/woocommerce/) to allow shop owners to add custom tabs to products. The tabs are displayed on the individual product pages to the right of the default "Description" tab.

Individual product tabs are managed on the WooCommerce Edit Product screen and can be added on a per product basis. You can also create saved tabs and add them to multiple products as needed. Tabs can be easily added, deleted and rearranged.

Tab content areas use the standard WordPress text editor and may contain text, images, HTML or shortcodes. 

If you experience any problems, please submit a ticket on our [Free WordPress Support Forums](https://wordpress.org/support/plugin/yikes-inc-easy-custom-woocommerce-product-tabs) and we'll look in to it as soon as possible.

This plugin is compatible with WPML.

Upgrade to [Custom Product Tabs Pro](https://yikesplugins.com/plugin/custom-product-tabs-pro/) for great enhanced features!

== Installation ==

1. Download the plugin .zip file and make note of where on your computer you downloaded it to.
2. In the WordPress admin (yourdomain.com/wp-admin) go to Plugins > Add New or click the "Add New" button on the main plugins screen.
3. On the following screen, click the "Upload Plugin" button.
4. Browse your computer to where you downloaded the plugin .zip file, select it and click the "Install Now" button.
5. After the plugin has successfully installed, click "Activate Plugin" and enjoy!
6. Edit a product, then click on 'Custom Tab' within the 'Product Data' panel
7. Create saved, reusable tabs under Settings > Custom Product Tabs for WooCommerce

== Screenshots ==

1. Custom Tabs manager on the 'Edit Product' screen
2. Custom product tab content displayed on the front end
3. All of the saved tabs on the settings page
4. Editing a single saved tab

== Frequently Asked Questions ==

**All documentation can be found in [our Knowledge Base](https://yikesplugins.com/support/knowledge-base/product/easy-custom-product-tabs-for-woocommerce/).**

= Where do I go to add tabs to a product? =
When editing a product in WooCommerce, you will find "Custom Tabs" in the bottom left corner of the Product Data box. Click on "Custom Tabs" to reveal the custom tab manager.

= Where will these tabs appear? =
When the product is viewed on your website you will see the tabs you created to the right of the default "Description" tab. 

= How do I change the order of the tabs? =
To change the order of custom tabs use the up and down "Move tab order" arrows.

= How do saved tabs work? =
Saved tabs are tabs you can create and then add to as many products you would like. If you update a saved tab, the changes will be updated for all products using that tab.

= How do I create saved tabs? =
To create a saved tab, click on the 'Custom Product Tabs' item on the WordPress dashboard menu and click the "Add Tab" button.

= How do I add a saved tab to a product? =
To add a saved tab to a product, go to the custom tabs section on the edit product screen, click the 'Add a Saved Tab' button above the tab, and choose which tab you would like to add.

= What does overriding a saved tab do? =
When using a saved tab on a product, a checkbox appears with the message 'Override Saved Tab' If you click that checkbox, edit the tab and save, the tab will be changed for that product only. Any edits to that saved tab under 'Custom Product Tabs' will not be applied to that product.

= Why does the WYSIWYG editor default to the 'Visual' tab? =
This was added in version 1.5 to support the dynamic adding and removing of the wp_editor/WYSIWYG editor. Without this setting, the WYSIWYG editor does not load the correct toolbar and the editor can potentially break.

= Does custom tab data get exported with standard WooCommerce product data? =
Yes! Since v1.4 we've added the necessary code to ensure the custom tab data is exported with all of the other standard WooCommerce data. This ensures a smooth transition of products between sites.


== Changelog ==

= 1.7.7 - March 8th, 2021 =
* Housekeeping

= 1.7.6 – October 19th, 2020 =
* WooCommerce 4.6 tested.

= 1.7.5 – September 18th, 2020 =
* Swapping (deprecated) wp_make_content_images_responsive for wp_filter_content_tags in our content filter. Thanks @stephencd!

= 1.7.4 – September 12th, 2020 =
* WooCommerce 4.5.

= 1.7.3 – August 19th, 2020 =
* WooCommerce 4.4.
* Fixes issues related to WordPress 5.5.

= 1.7.1 – March 13th, 2020 =
* Fixes a bug with product display in certain conditions.

= 1.7.0 – March 10th, 2020 =
* Toggle the content filter on or off setting added. Use this to help with compatibility.
* Support WooCommerce 4.0.
* Support WordPress 5.4.

= 1.6.13 - January 22nd, 2020 =
* Support WooCommerce 3.9.

= 1.6.12 - November 20th, 2019 =
* Support WooCommerce 3.8.

= 1.6.11 - September 25th, 2019 =
* Adding additional checks to post global before enqueueing assets.

= 1.6.10 - April 19th, 2019 =
* Updating WC compatibility.
* Fixing JS issue with WP backwards compatibility for versions < 4.7.

= 1.6.9 - January 18th, 2019 =
* Fixing an issue where the visual editor shows a small portion of the content on product edit pages.

= 1.6.8 - January 2nd, 2019 =
* Fixing some HTML markup.
* Applying PHPCS fixes.

= 1.6.7 - December 18th, 2018 =
* Adding filter to help allow importing of custom tabs.
* Changing our export filters so custom tabs work with WooCommerce's native meta export/import features.
* The default capability for all admin pages is now `publish_products`.

= 1.6.6 - October 26th, 2018 =
* Bumping WooCo Compatibility.
* Changed `wp_send_json_failure()` to `wp_send_json_error()`.

= 1.6.5 - October 3rd, 2018 =
* Bumping WooCo Compatibility.

= 1.6.4 - January 9th, 2018 =
* Happy new year! 
* The editor is now vertically resizeable.
* The default capability for interacting with saved tabs is now Publish Products (publish_products)

= 1.6.3 - November 1st, 2017 =
* Declaring compatibility with WooCommerce and WordPress

= 1.6.2 - October 13th, 2017 =
* Fixed a PHP Fatal Error that was occurring for users with PHP versions < 5.5.
* Updated some of our documentation and language

= 1.6.1 - October 12th, 2017 =
* Fixed an issue with handling foreign characters. Foreign character tab titles should be working properly now. Sorry about that everyone!
* Added support for native WooCommerce exporting. You can now export and import your tabs with just WooCommerce!
* Fixed some styling issues
* Added a new "Support" page
* Added a new "Go Pro" page - check out [Custom Product Tabs Pro](https://yikesplugins.com/plugin/custom-product-tabs-pro/)

= 1.6.0 - October 9th, 2017 = 
* Complete re-organization of all plugin files and removal of legacy code
* Added a "name" field for saved tabs. This field is used only on the admin as a way of identifying tabs.
* Tab "slugs" are now created via the WP Core `sanitize_title()` function. This should allow meaningful tab slugs when foreign characters are used in a title.
* Re-added the "Add Media" button to the editor when it's first initialized. This had disappeared when WP4.8 was released.
* Fixed some issues with loading saved tab content into the editor. This should fix the issue that some users were experiencing where adding a saved tab would only work the second time.
* Setting the width of the editor to 100%. 
* Custom Product Tabs is now a top-level menu item instead of a sub-menu item.
* Cleaning up the saved tab's array so we don't leave orphaned data (e.g. added a hook so we delete a product's tabs when the product is deleted)
* Added a data update script to update all existing tab slugs to use `sanitize_title()` function.
* Generated new POT file.
* Added support and hooks for our new Custom Product Tabs Pro plugin! 

= 1.5.17 - August 23rd, 2017 = 
* Cleaning up some PHP Notices being thrown - thanks to @ZombiEquinox on GitHub for reporting this
* Updating readme compatibility values

= 1.5.16 - August 1st, 2017 = 
* Adding a proper deactivation hook. The plugin will leave no trace.

= 1.5.15 - June 8th, 2017 =
* WordPress 4.8 support - using the new JavaScript Editor API functions to instantiate the editor and removed requiring WordPress' wpembed plugin

= 1.5.14 - May 8th, 2017 =
* Updating some CSS for the admin tabs table - the table should now render correctly regardless of "Visual" or "Text" tab and the saved tabs list should include a scrollbar if necessary

= 1.5.13 - April 17th, 2017 =
* Updating a WooCommerce action - now using the proper one instead of a deprecated one

= 1.5.12 - April 10th, 2017 =
* Adding some CSS to allow the editor's text mode to function properly

= 1.5.11 - April 6th, 2017 =
* Checking for the existence of the `get_id()` method before using it.

= 1.5.10 - April 5th, 2017 =
* Duplicating a product now duplicates custom product tabs and saved tabs correctly

= 1.5.9 - April 4th, 2017 =
* Tested and updated the plugin for WooCommerce v3.0.0

= 1.5.8 - March 17th, 2017 =
* Replaced the saved tab's ID w/ an "Add New" button on the single saved tab page - it should be easier to add saved tabs in bulk now
* Added a filter for all of the custom tab content - it should allow you to apply custom logic such as permissions in one central location
* Changed the way saved tabs are applied on the edit product page - it should allow embed content (especially Google Maps Embed content) to function correctly in all instances.

= 1.5.7 - February 27th, 2017 =
* Duplicating a product now duplicates the corresponding saved tabs correctly
* Added two filters (`yikes_woo_use_the_content_filter` and `yikes_woo_filter_main_tab_content`) to help provide a work-around to using the standard `the_content` filter which has caused numerous issues due to plugin conflicts.

= 1.5.6 - February 16th, 2017 =
* Fixed an issue where the "Add a Saved Tab" modal was displaying YouTube videos if a saved tab had a YouTube URL in its content

= 1.5.5 - January 23rd, 2017 =
* Re-did 1.5.4 changes - checking for function existence before using it

= 1.5.4 - January 23rd, 2017 =
* Re-did 1.5.3 changes - `the_content` filter is reapplied and the specific Site Builder plugin's filters are

= 1.5.3 - January 23rd, 2017 =
* Replaced the use of `the_content` filter with the filter's associated functions (e.g. `wptexturize`, `wpautop`)

= 1.5.2 - December 23rd, 2016 =
* The editor should only default to the 'Visual' tab for our Custom Product Tabs (no other editors)
* Added all of the default WordPress settings to the editor

= 1.5.1 - December 22nd, 2016 =
* Fixed bug that caused content to be copied incorrectly when moving tabs up / down
* Only on the product page will the editor default to 'Visual' (instead of every page)

= 1.5 - December 20th, 2016 =
* Version 1.5 includes a brand new feature - saved tabs - as well as a number of bug fixes, style tweaks, code clean-up, and comments
* UI: Complete overhaul of the custom tab interface for an easier, responsive tab creating experience.
* Saved Tabs: A new settings page has been added for users to create / update / delete saved tabs (see FAQ for more information)
* Saved Tabs: On the product edit page, a new button ('Add a Saved Tab') has been added that allows you to choose one of your saved tabs and add it to the current product
* Adding a new tab initializes a new wp_editor (WYSIWYG) instead of a plain textarea
* Added warning message when two tabs have the same title
* Tabs with empty titles are no longer shown on the product page
* Added ability to remove the first tab
* Adding, moving, and removing tabs works as expected when the user's 'Visual Editor' option is checked
* On the product & settings pages, WYSIWYG editors will default to the visual tab (this helps prevent errors with dynamic wp_editor generation)
* Added a filter `yikes_woocommerce_default_editor_mode` that can change the default-to-visual-tab behavior (use at your own risk!)
* Updated the 'How To' text, and slight modification to the style
* Changed the JavaScript methods controlling how tabs were added, deleted, and moved up/down
* Cleaned up and commented on all PHP and JavaScript files
* Added proper i18n, with languages/ folder, .pot file, and `load_plugin_textdomain` hook
* Incremented version #

= 1.4.4 - March 1st, 2016 =
* Re-named the tab ID's to support URL's with query args (eg: http://www.example.com/shop#tab-reviews)

= 1.4.3 - February 18th, 2016 =
* Wrapped missing 'Custom Tab Title' in localization/translation functions. (Plugin is now 100% translatable)
* Removed i18n class files, and old .po/.mo files (less bloat)

= 1.4.2 - February 17th, 2016 =
* Updated the internationalization strings ( `yikes-inc-woocommerce-custom-product-tabs` to `yikes-inc-easy-custom-woocommerce-product-tabs` )

= 1.4.1 - August 20th, 2015 =
* Fixed conflict with other CSV export plugins for WooCommerce
* Now custom product tab and row data/headers only get exported via 'Tools > Export > Products'

= 1.4 - July 29th, 2015 =
* Enhancement: Added the 'Custom Product Tabs for WooCommerce ' data to the standard WooCommerce export file, so custom tab data can be transferred between sites smoothly.

= 1.3 - July 21st, 2015 =
* Enhancement: Enabled WYSIWYG editor on tab content containers (enables shortcode and content to pass through the_content() filter)
* Updated repo screenshots and descriptions

= 1.2 - March 18th, 2015 =
* Enhancement: Fixed issue where non utf8 characters in tab titles caused front end not to generate the tabs
* Enhancement: When user doesn't have WooCommerce installed, they are now redirected to the plugin install search page, with WooCommerce at the top.

= 1.1 =
* Added class to the Woo tabs content title, for targeting via CSS ( `.yikes-custom-woo-tab-title` )

= 1.0.0 =
* Initial Release

== Upgrade Notice ==
= 1.4.4 - March 1st, 2016 =
* Re-named the tab ID's to support URL's with query args (eg: http://www.example.com/shop#tab-reviews)
