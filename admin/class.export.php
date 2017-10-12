<?php

class YIKES_Custom_Product_Tabs_Export {

	public function __construct() {

		add_filter( 'woocommerce_product_export_product_default_columns', array( $this, 'add_custom_product_tabs_header_to_product_export' ), 10, 1 );

		add_filter( 'woocommerce_product_export_product_column_yikes_woo_products_tabs', array( $this, 'add_custom_product_tabs_data_to_product_export' ), 10, 3 );
	}

	public function add_custom_product_tabs_header_to_product_export( $columns ) {

		if ( apply_filters( 'yikes-woo-do-not-export-tabs', false ) === true ) {
			return $columns;
		}

		$columns['yikes_woo_products_tabs'] = 'yikes_woo_products_tabs';
		return $columns;
	}

	public function add_custom_product_tabs_data_to_product_export( $value, $product, $column_id ) {

		$tabs = get_post_meta( $product->get_id(), 'yikes_woo_products_tabs', true );

		$tabs = ! empty( $tabs ) ? serialize( $tabs ) : '';

		return $tabs;
	}
}

new YIKES_Custom_Product_Tabs_Export();