=== YIKES Custom Product Tabs for WooCommerce  ===
Contributors: yikesinc, eherman24, liljimmi
Donate link: http://yikesinc.com
Tags: woocommerce, product tabs, repeatable, duplicate, customize, custom, tabs, product, woo, commerce
Requires at least: 3.8
Tested up to: 4.6.1
Requires WooCommerce at least: 2.0
Tested WooCommerce up to: 2.5.2
Stable tag: 1.4.4
License: GPLv2 or later

This plugin extends WooCommerce to allow site admins to add custom tabs to products. 

== Description ==

This plugin extends the [WooCommerce](www.woothemes.com/woocommerce/) eCommerce plugin to allow site admins to add custom tabs to products.

Tabs are managed on the Edit Product screen and can be added on a per product basis. Tabs can be easily added, deleted and rearranged.

Tab content areas use the standard WYSIWYG WordPress editor, and may contain text, html or shortcodes. 

If you experience any problems, please submit a New Issue on our [Github Issue Tracker](https://github.com/yikesinc/yikes-inc-easy-custom-woocommerce-product-tabs/issues) and we'll look in to it as soon as possible.

<i>Originally a fork of <a href="https://wordpress.org/plugins/woocommerce-custom-product-tabs-lite/" target="_blank">SkyVerge WooCommerce Custom Product Tabs Lite</a>, and customized for a <a href="http://www.yikesinc.com" target="_blank">YIKES</a> client site.</i>

== Installation ==

1. Upload the entire 'yikes-inc-custom-repeatable-woo-product-tabs' folder to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Edit a product, then click on 'Custom Tab' within the 'Product Data' panel

== Screenshots ==

1. Custom Tabs manager on the 'Edit Product' screen
2. Custom product tab content displayed on the front end
3. Contact Form 7 shortcode rendered in custom product tab

== Frequently Asked Questions ==

= Where do I go to add tabs to a product? =
When editing a product in WooCommerce, you will find "Custom Tabs" in the bottom left corner of the Product Data box. Click on "Custom Tabs" to reveal the custom tab manager.

= Where will these tabs appear? =
When the product is viewed on your website you will see the tabs you created to the right of the default "Description" tab. 

= I added tabs, but they didn't appear on the front-end of the site. What's up? =
Make sure you hit the Product's main Update button to save all the edits you made to custom tabs or they will not be saved.

= How do I change the order of the tabs? =
To change the order of the custom tabs use the up and down arrows to the right of the Tab Title.

= Does the custom tab data get exported with the standard WooCommerce product data? =
Yes! Since v1.4 we've added the necessary hooks and filters to ensure the custom tab data is exported with all of the other standard WooCommerce data. This ensures a smooth transition of products between sites.


== Changelog ==

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
* Enhancement: Added the 'YIKES Custom Product Tabs for WooCommerce ' data to the standard WooCommerce export file, so custom tab data can be transferred between sites smoothly.

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