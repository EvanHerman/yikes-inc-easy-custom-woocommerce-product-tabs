<?php

/**
 * Class YIKES_Custom_Product_Tabs_Export.
 */
class YIKES_Custom_Product_Tabs_Export {

	/**
	 * Define our hooks.
	 */
	public function __construct() {
		add_filter( 'woocommerce_product_export_meta_value', array( $this, 'prep_product_tabs_for_export' ), 10, 4 );
	}

	/**
	 * Prep our tabs for an export.
	 *
	 * @param mixed      $meta_value The meta value.
	 * @param object     $meta       The meta field.
	 * @param WC_Product $product    Product being exported.
	 * @param array      $row        Row data.
	 */
	public function prep_product_tabs_for_export( $meta_value, $meta, $product, $row ) {
		if ( isset( $meta->key ) && $meta->key === 'yikes_woo_products_tabs' ) {
			if ( is_array( $meta_value ) ) {
				return serialize( $meta_value );
			}
		}
		return $meta_value;
	}
}

new YIKES_Custom_Product_Tabs_Export();
