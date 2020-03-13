<?php 

if ( ! class_exists( 'YIKES_Custom_Product_Tabs_Display' ) ) {

	class YIKES_Custom_Product_Tabs_Display {


		public function __construct() {

			add_action( 'woocommerce_init', array( $this, 'init' ) );
		}

		public function init() {

			// Add our custom product tabs section to the product page
			add_filter( 'woocommerce_product_tabs', array( $this, 'add_custom_product_tabs' ) );

			// Allow the use of shortcodes within the tab content
			add_filter( 'yikes_woocommerce_custom_repeatable_product_tabs_content', 'do_shortcode' );
		}

		/**
		 * Add the custom product tab to the front-end single product page
		 * 
		 * $tabs structure:
		 * Array(
		 *   id => Array(
		 *     'title'    => (string) Tab title,
		 *     'priority' => (string) Tab priority,
		 *     'callback' => (mixed) callback function,
		 *   )
		 * )
		 *
		 * @param array  | $tabs | Array representing this product's current tabs
		 *
		 * @return array | Array of this product's current tabs plus our custom tabs
		 */
		public function add_custom_product_tabs( $tabs ) {
			global $product;

			$product_id = method_exists( $product, 'get_id' ) === true ? $product->get_id() : $product->ID;

			$product_tabs = maybe_unserialize( get_post_meta( $product_id, 'yikes_woo_products_tabs' , true ) );

			if ( is_array( $product_tabs ) && ! empty( $product_tabs ) ) {

				// Setup priorty to loop over, and render tabs in proper order
				$i = 25; 

				foreach ( $product_tabs as $tab ) {

					// Do not show tabs with empty titles on the front end
					if ( empty( $tab['title'] ) ) {
						continue;
					}

					$tab_key = $tab['id']; 

					$tabs[ $tab_key ] = array(
						'title'		=> $tab['title'],
						'priority'	=> $i++,
						'callback'	=> array( $this, 'custom_product_tabs_panel_content' ),
						'content'	=> $tab['content']
					);
				}
				if ( isset( $tabs['reviews'] ) ) {

					// Make sure the reviews tab remains on the end (if it is set)
					$tabs['reviews']['priority'] = $i;
				}
			}

			/**
			* Filter: 'yikes_woo_filter_all_product_tabs'
			*
			* Generic filter that passes all of the tab info and the corresponding product. Cheers.
			*
			* Note: This passes all of the tabs for the current product, not just the Custom Product Tabs created by this plugin.
			*
			* @param array  | $tab		| Array of $tab data arrays.
			* @param object | $product	| The WooCommerce product these tabs are for
			*/
			$tabs = apply_filters( 'yikes_woo_filter_all_product_tabs', $tabs, $product );

			return $tabs;
			
		}


		/**
		 * Render the custom product tab panel content for the given $tab
		 *
		 * $tab structure:
		 * Array(
		 *   'title'    => (string) Tab title,
		 *   'priority' => (string) Tab priority,
		 *   'callback' => (mixed) callback function,
		 *   'id'       => (int) tab post identifier,
		 *   'content'  => (sring) tab content,
		 * )
		 *
		 **/
		public function custom_product_tabs_panel_content( $key, $tab ) {

			$content = '';			

			$use_the_content_filter = apply_filters( 'yikes_woo_use_the_content_filter', true );

			if ( $use_the_content_filter === true ) {
				$content = apply_filters( 'the_content', $tab['content'] );
			} else {
				$content = apply_filters( 'yikes_woo_filter_main_tab_content', $tab['content'] );
			}

			$tab_title_html = '<h2 class="yikes-custom-woo-tab-title yikes-custom-woo-tab-title-' . urldecode( sanitize_title( $tab['title'] ) ) . '">' . $tab['title'] . '</h2>';
			echo apply_filters( 'yikes_woocommerce_custom_repeatable_product_tabs_heading', $tab_title_html, $tab );
			echo apply_filters( 'yikes_woocommerce_custom_repeatable_product_tabs_content', $content, $tab );
		}

	}

	new YIKES_Custom_Product_Tabs_Display();
}