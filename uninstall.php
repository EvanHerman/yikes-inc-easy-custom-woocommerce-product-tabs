<?php

	if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
		exit;
	}


	// When the plugin is uninstalled, brush away all footprints; there shall be no trace.
	global $wpdb;

	// Remove all of our 'yikes_woo_products_tabs' post meta
	$wpdb->delete(

		// Table
		"{$wpdb->prefix}postmeta",

		// Where
		array( 'meta_key' => 'yikes_woo_products_tabs' )
	);

	// Remove our 'yikes_woo_reusable_products_tabs' option
	delete_option( 'yikes_woo_reusable_products_tabs' );

	// Remove our 'yikes_woo_reusable_products_tabs_applied' option
	delete_option( 'yikes_woo_reusable_products_tabs_applied' );

	// Remove our DB version option (legacy)
	delete_option( 'yikes_woocommerce_custom_product_tabs_db_version' );