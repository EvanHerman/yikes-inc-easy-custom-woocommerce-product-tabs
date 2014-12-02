=== WooCommerce Custom Repeatable Product Tabs ===
Contributors: YIKES Inc, eherman24
Tags: woocommerce, product tabs, repeatable, duplicate, customize, custom, tabs, product, woo, commerce
Requires at least: 3.8
Tested up to: 4.0
Requires WooCommerce at least: 2.0
Tested WooCommerce up to: 2.2.6
Stable tag: 1.0

This plugin extends the WooCommerce e-commerce plugin by allowing a custom product tab to be created with arbitrary content.

== Description ==

This plugin extends the [WooCommerce](www.woothemes.com/woocommerce/) e-commerce plugin by allowing a custom product tab to be added to product view pages with arbitrary content, which may contain text, html or shortcodes.

The custom tab, if defined, will appear in between the 'additional information' and 'reviews' tabs.

To easily add multiple tabs, share tabs between products, and more features, consider upgrading to the premium [Tab Manager](http://www.woothemes.com/products/woocommerce-tab-manager/)

= Plugin/Theme Support =
*This plugin should be compatible with any WooCommerce theme and has been tested with the following*

* Theme: ["Wootique" (free, by WooThemes)](www.woothemes.com/2011/09/wootique/)
* Theme: ["Woostore"](www.woothemes.com/2011/09/woostore/) (thanks to Dusan Belescak)

= Feedback =
* We are open to your suggestions and feedback - Thank you for using or trying out one of our plugins!
* Drop us a line at [www.skyverge.com](http://www.skyverge.com)

= More =
* [Also see our other plugins](http://www.skyverge.com) or see [our WordPress.org profile page](http://profiles.wordpress.org/skyverge/)
* Upgrade to the premium Tab Manager now available from [WooThemes](http://www.woothemes.com/products/woocommerce-tab-manager/)

== Installation ==

1. Upload the entire 'yikes-inc-woocommerce-custom-product-tabs' folder to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Edit a product, then click on 'Custom Tab' within the 'Product Data' panel

== Screenshots ==

1. Adding a custom tab to a product in the admin
2. The custom tab displayed on the frontend

== Frequently Asked Questions ==

= Can I add more than tab, or change the order of the tabs? =

This free version does not have that functionality, but now you can with the paid [WooCommerce Tab Manager](http://www.woothemes.com/products/woocommerce-tab-manager/)

= Can I share tab content between more than one tab? =

This free version does not have that functionality, but now you can with the paid [WooCommerce Tab Manager](http://www.woothemes.com/products/woocommerce-tab-manager/)

= I already use the free plugin and now I want to upgrade to the premium Tab Manager, is that possible? =

Yes, the upgrade process form the free to the premium Tab Manager plugin is painless and easy.

= How do I hide the tab heading? =

The tab heading is shown before the tab content and is the same string as the tab title.  An easy way to hide this is to add the following to the bottom of your theme's functions.php:

`
add_filter( 'yikes_woocommerce_custom_repeatable_product_tabs_heading', 'hide_custom_yikes_woo_product_tab_heading' );
function hide_custom_yikes_woo_product_tab_heading( $heading ) { 
	return ''; // return nothing 
}
`

== Changelog ==

= 1.2.6 - 2014.09.05 =
 * Misc - WooCommerce 2.2 Compatibility

= 1.2.5 - 2014.01.22 =
 * Misc - WooCommerce 2.1 support
 * Localization - Text domain is now yikes-inc-woocommerce-custom-product-tabs

= 1.2.4 - 2013.08.26 =
 * Fix - Shortcode support in custom tab content

= 1.2.3 - 2013.06.06 =
 * Tweak - Changed admin field names to improve compatibility with other custom tab plugins

= 1.2.2 - 2013.05.15 =
 * Fix - Unicode characters supported in tab title

= 1.2.1 - 2013.04.26 =
 * Tweak - Minor code and documentation update

= 1.2.0 - 2013.02.16 =
 * WooCommerce 2.0 Compatiblity

= 1.1.0 - 2012.04.23 =
 * Feature - Shortcodes enabled for tab content

= 1.0.3 - 2012.03.19 =
 * Feature - Tab content textarea is larger for easier input

= 1.0.2 - 2012.03.19 =
 * Fix - Fixes an admin bug introduced by the 1.0.1 release (thanks to Cabbola)

= 1.0.1 - 2012.03.15 =
 * Fix - Fixes T_PAAMAYIM_NEKUDOTAYIM error (thanks daveshine)

= 1.0.0 - 2012.03.15 =
 * Misc - Code cleanup

= 0.1 - 2012.03.07 =
 * Initial release
