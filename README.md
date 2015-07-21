Easy Custom WooCommerce Product Tabs v1.3
============================================

This plugin extends the [WooCommerce](www.woothemes.com/woocommerce/) eCommerce plugin to allow admins to add custom tabs to products. 

Tabs are managed on the Edit Product screen and can be added on a per product basis. Tabs can be easily added, deleted and rearranged.

Tab content areas may contain text, html or shortcodes. 

If you experience any problems, please submit a New Issue on our [Github Issue Tracker](https://github.com/yikesinc/yikes-inc-easy-custom-woocommerce-product-tabs/issues) and we'll look in to it as soon as possible.

Installation
===========

1. Upload the entire 'yikes-inc-custom-repeatable-woo-product-tabs' folder to the '/wp-content/plugins/' directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Edit a product, then click on 'Custom Tab' within the 'Product Data' panel

Frequently Asked Questions
===========

#### Where do I go to add tabs to a product?
When editing a product in WoCommerce you will find "Custom Tabs" in the bottom left corner of the Product Data box. Click on "Custom Tabs" and you will see the custom tab manager.

#### Where will these tabs appear?
When the product is viewed on your website you will see the tabs you created to the right of the default "Description" tab. 

#### How do I change the order of the tabs?
To change the order of the custom tabs use the up and down arrows to the right of the Tab Title.

#### I added tabs, but they didn't appear on the front-end of the site. What's up?
Make sure you hit the Product's main Update button to save all the edits you made to custom tabs or they will not be saved.


Changelog
===========

### 1.3 - July 21st, 2015
* Enhancement: Enabled WYSIWYG editor on tab content containers (enables shortcode and content to pass through the_content() filter)
* Updated repo screenshots and descriptions

### 1.2 - March 18th, 2015
* Enhancement: fixed issue where non utf8 characters in tab titles caused front end not to generate the tabs
* Enhancement: When user doesn't have WooCommerce installed, they are now redirected to the plugin install search page, with WooCommerce at the top.

### 1.1
* Added class to the Woo tabs content title, for targeting via CSS ( `.yikes-custom-woo-tab-title` )

### 1.0
* Initial Release