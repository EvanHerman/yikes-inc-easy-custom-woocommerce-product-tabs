<?php

	class YIKES_Custom_Product_Tabs_Support {

		/**
		* Constructah >:^)
		*/
		public function __construct() {

			// Add our custom settings page
			add_action( 'admin_menu', array( $this, 'register_support_subpage' ), 20 );

			// Enqueue scripts & styles
			// add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10, 1 );

			// Add our free support page HTML
			add_action( 'yikes-woo-support-page-free', array( $this, 'render_support_page' ), 100 );
		}

		/**
		* Register our settings page
		*/
		public function register_support_subpage() {

			// Add our custom settings page
			add_submenu_page(
				YIKES_Custom_Product_Tabs_Settings_Page,                            // Parent menu item slug
				__( 'Support', YIKES_Custom_Product_Tabs_Settings_Page ),           // Tab title name (HTML title)
				__( 'Support', YIKES_Custom_Product_Tabs_Settings_Page ),           // Menu page name
				apply_filters( 'yikes-woo-support-capability', 'manage_options' ),  // Capability required
				YIKES_Custom_Product_Tabs_Support_Page,                             // Page slug (?page=slug-name)
				array( $this, 'support_page' )                                      // Function to generate page
			);
		}

		/**
		* Include our settings page
		*/
		public function support_page() {

			require_once YIKES_Custom_Product_Tabs_Path . 'admin/page.support.php';
		}

		/**
		* Show our support page HTML
		*/
		public function render_support_page() { 
			if ( defined( 'YIKES_Custom_Product_Tabs_Pro_Enabled' ) ) {
				return;
			}
			?>
				<div class="cptpro-settings cptpro-settings-support-help-container">
					<p>
						<?php 
							_e( 
								'Users of the Custom Product Tabs free can post questions to our support forum on the WordPress Plugin Directory. We aim to respond to support requests for the free version of the plugin within a week..', 
								YIKES_Custom_Product_Tabs_Text_Domain  
							);
						?>
					</p>
					<p>
						<?php 
							echo sprintf( __( 'Custom Product Tabs Pro users qualify for premium support. Check out %1sCustom Product Tabs Pro%2s!', YIKES_Custom_Product_Tabs_Text_Domain  ), 
								'<a href="https://yikesplugins.com/plugin/custom-product-tabs-pro/" target="_blank">', '</a>' ); 
						?>
					</p>
					<p>
						<?php 
							echo sprintf( __( 'Before submitting a support request, please visit our %1sknowledge base%2s where we have step-by-step guides and troubleshooting help.', YIKES_Custom_Product_Tabs_Text_Domain  ), 
								'<a href="https://yikesplugins.com/support/knowledge-base/product/easy-custom-product-tabs-for-woocommerce/" target="_blank">', '</a>' ); 
						?>
					</p>

					<hr />
					

					<p>
						<h1><span class="dashicons dashicons-wordpress-alt"></span>&nbsp;<?php _e( 'WordPress.org Plugin Directory', YIKES_Custom_Product_Tabs_Text_Domain ); ?></h1>
						<a class="button button-primary" href="https://wordpress.org/support/plugin/yikes-inc-easy-custom-woocommerce-product-tabs#new-post" target="_blank">
							<?php _e( 'Submit a New WordPress.org Support Request', YIKES_Custom_Product_Tabs_Text_Domain ); ?>
						</a>
					</p>


					<img src="<?php echo YIKES_Custom_Product_Tabs_URI . 'images/support-screenshot.png' ?>" />



				</div>
			<?php
		}

		
	}

	new YIKES_Custom_Product_Tabs_Support();