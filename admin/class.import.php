<?php

/**
 * Class YIKES_Custom_Product_Tabs_Import.
 */
class YIKES_Custom_Product_Tabs_Import {

	/**
	 * Define hooks.
	 */
	public function __construct() {
		add_filter( 'woocommerce_product_importer_formatting_callbacks', array( $this, 'remove_wp_kses_posts_callback_from_import' ), 10, 2 );
	}

	/**
	 * When importing our meta field via WooCommerce's native import, replace the wp_kses_post sanitization callback with trim().
	 *
	 * The wp_kses_post function gets ran on all meta and this breaks some serialized arrays.
	 */
	public function remove_wp_kses_posts_callback_from_import( $callbacks, $importer ) {

		$meta_key = false;

		// Go through the file's keys and look for our meta field.
		if ( method_exists( $importer, 'get_mapped_keys' ) ) {
			foreach ( $importer->get_mapped_keys() as $key_number => $field_name ) {
				if ( $field_name === 'meta:yikes_woo_products_tabs' ) {
					$meta_key = $key_number;
					break;
				}
			}
		}

		// Set our meta field's callback to `trim()`.
		if ( $meta_key !== false ) {
			foreach ( $callbacks as $key => $callback ) {
				if ( $key === $meta_key ) {
					$callbacks[ $key ] = 'trim';
				}
			}
		}

		return $callbacks;
	}
}

new YIKES_Custom_Product_Tabs_Import();
