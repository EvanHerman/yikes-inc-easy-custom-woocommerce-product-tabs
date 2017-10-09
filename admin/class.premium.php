<?php

	class YIKES_Custom_Product_Tabs_Premium {

		/**
		* Constructah >:^)
		*/
		public function __construct() {

			// Add our custom settings page
			add_action( 'admin_menu', array( $this, 'register_premium_subpage' ), 30 );

			// Enqueue scripts & styles
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10, 1 );
		}

		/**
		* Enqueue our scripts and styes
		*
		* @param string | $page | The slug of the page we're currently on
		*/
		public function enqueue_scripts( $page ) {

			if ( $page === 'custom-product-tabs-pro_page_' . YIKES_Custom_Product_Tabs_Premium_Page ) {

				wp_enqueue_style ( 'lightslider-styles', YIKES_Custom_Product_Tabs_URI . 'slider/css/lightslider.min.css' );
				wp_enqueue_script( 'lightslider-scripts', YIKES_Custom_Product_Tabs_URI . 'slider/js/lightslider.min.js', array( 'jquery' ), YIKES_Custom_Product_Tabs_Version );
				wp_enqueue_script( 'premium-scripts', YIKES_Custom_Product_Tabs_URI . 'js/premium.js', array( 'jquery' ), YIKES_Custom_Product_Tabs_Version );
			}
		}

		/**
		* Register our premium page
		*/
		public function register_premium_subpage() {

			// if ( defined( 'YIKES_Custom_Product_Tabs_Pro_Enabled' ) ) {
			// 	return;
			// }

			// Add our custom settings page
			add_submenu_page(
				YIKES_Custom_Product_Tabs_Settings_Page,                            // Parent menu item slug
				__( 'Get Pro', YIKES_Custom_Product_Tabs_Text_Domain ),             // Tab title name (HTML title)
				__( 'Get Pro', YIKES_Custom_Product_Tabs_Text_Domain ),             // Menu page name
				apply_filters( 'yikes-woo-premium-capability', 'manage_options' ),  // Capability required
				YIKES_Custom_Product_Tabs_Premium_Page,                             // Page slug (?page=slug-name)
				array( $this, 'premium_page' )                                      // Function to generate page
			);
		}

		/**
		* Include our settings page
		*/
		public function premium_page() {

			require_once YIKES_Custom_Product_Tabs_Path . 'admin/page.premium.php';
		}
		
	}

	new YIKES_Custom_Product_Tabs_Premium();