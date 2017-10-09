<?php

if ( ! class_exists( 'YIKES_Custom_Product_Tabs_Export' ) ) {

	class YIKES_Custom_Product_Tabs_Export {


		public function __construct() {

			add_action( 'init', array( $this, 'load_custom_export_filters' ) );
		}

		/**
		* Add our custom filters to the export
		*/
		public function load_custom_export_filters() {			
			global $pagenow;
			
			if( 'export.php' === $pagenow ) {

				// add our data to the woocommerce export
				add_filter( 'wc_customer_order_csv_export_order_headers', array( $this, 'yikes_wootabs_wc_csv_export_modify_column_headers' ) );
				add_filter( 'wc_customer_order_csv_export_order_row', array( $this, 'yikes_wootabs_wc_csv_export_modify_row_data' ), 10, 3 );
			}
		}
		
		/**
		 *	Add our data to the standard WooCommerce Export Functionality
		 *	@since 1.4
		**/
		public function yikes_wootabs_wc_csv_export_modify_column_headers( $column_headers ) { 
 
			$new_headers = array(
				'yikes_woo_products_tabs' => 'Yikes Inc. Custom WooCommerce Tabs',
			);
		 
			return array_merge( $column_headers, $new_headers );
		}
		
		/**
		*	Append our yikes woo product tab data
		*	@since 1.4
		**/
		public function yikes_wootabs_wc_csv_export_modify_row_data( $order_data, $order, $csv_generator ) {
		 
			$custom_data = array(
				'yikes_woo_products_tabs' => get_post_meta( $order->id, 'yikes_woo_products_tabs', true ),
			);
		 
			$new_order_data = array();
			if ( isset( $csv_generator->order_format ) && ( 'default_one_row_per_item' == $csv_generator->order_format || 'legacy_one_row_per_item' == $csv_generator->order_format ) ) {
				foreach ( $order_data as $data ) {
					$new_order_data[] = array_merge( (array) $data, $custom_data );
				}
			} else {
				$new_order_data = array_merge( $order_data, $custom_data );
			}
			return $new_order_data;
		}
	}

	new YIKES_Custom_Product_Tabs_Export();
}