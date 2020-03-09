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

			// Display an ad for CPTPRO
			add_action( 'yikes-woo-saved-tabs-list-ad', array( $this, 'display_cptpro_ad' ), 10 );
		}

		/**
		* Enqueue our scripts and styes
		*
		* @param string | $page | The slug of the page we're currently on
		*/
		public function enqueue_scripts( $page ) {			
			if ( $page === 'custom-product-tabs_page_' . YIKES_Custom_Product_Tabs_Premium_Page ) {

				wp_enqueue_style ( 'lightslider-styles', YIKES_Custom_Product_Tabs_URI . 'slider/css/lightslider.min.css' );
				wp_enqueue_style ( 'repeatable-custom-tabs-styles', YIKES_Custom_Product_Tabs_URI . 'css/repeatable-custom-tabs.min.css' );
				wp_enqueue_script( 'lightslider-scripts', YIKES_Custom_Product_Tabs_URI . 'slider/js/lightslider.min.js', array( 'jquery' ), YIKES_Custom_Product_Tabs_Version );
				wp_enqueue_script( 'premium-scripts', YIKES_Custom_Product_Tabs_URI . 'js/premium.min.js', array( 'lightslider-scripts' ), YIKES_Custom_Product_Tabs_Version );
				wp_enqueue_style ( 'repeatable-custom-tabs-styles' , YIKES_Custom_Product_Tabs_URI . 'css/repeatable-custom-tabs.min.css', '', YIKES_Custom_Product_Tabs_Version, 'all' );
			}
		}

		/**
		* Register our premium page
		*/
		public function register_premium_subpage() {

			if ( defined( 'YIKES_Custom_Product_Tabs_Pro_Enabled' ) ) {
				return;
			}

			// Add our custom settings page
			add_submenu_page(
				YIKES_Custom_Product_Tabs_Settings_Page,                            // Parent menu item slug
				__( 'Go Pro', 'yikes-inc-easy-custom-woocommerce-product-tabs' ),             // Tab title name (HTML title)
				__( 'Go Pro', 'yikes-inc-easy-custom-woocommerce-product-tabs' ),             // Menu page name
				apply_filters( 'yikes-woo-premium-capability', 'publish_products' ),  // Capability required
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

		/**
		* Display an ad for CPTPRO on the saved tabs list and saved tabs single pages
		*/
		public function display_cptpro_ad() {

			if ( defined( 'YIKES_Custom_Product_Tabs_Pro_Enabled' ) ) {
				return;
			}
			?>
			<div class="yikes-woo-all-about-us">
				<div class="postbox yikes-woo-review-us">

					<h3 class="yikes-woo-review-us-title">Show Us Some Love</h3>
					<div class="yikes-woo-review-us-body">
						<div class="yikes-woo-review-us yikes-woo-all-about-us-box" id="yikes-woo-review-us">

							<p><?php _e( 'Leave a review!', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?> </p>
							<p class="star-container">
								<a href="https://wordpress.org/support/plugin/yikes-inc-easy-custom-woocommerce-product-tabs/reviews/?rate=5#new-post" target="_blank">
									<span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span>
								</a>
							</p>
						</div>	

						<div class="yikes-woo-all-about-us-separator"></div>

						<div class="yikes-woo-tweet-us yikes-woo-all-about-us-box" id="yikes-woo-tweet-us">

							<p><?php _e( 'Tweet about us!', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?></p>
							<a class="twitter-share-button"
							  href="https://twitter.com/intent/tweet?text=I use Custom Product Tabs for WooCommerce by @yikesinc to help sell products online. Awesome #WordPress #plugins &url=https://wordpress.org/plugins/yikes-inc-easy-custom-woocommerce-product-tabs/"
							  data-size="large">
							<?php _e( 'Tweet', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?></a>
						</div>

						<p class="yikes-woo-review-us-footer">This plugin made with <span class="dashicons dashicons-heart yikes-love"></span> by <a href="http://www.yikesinc.com" target="_blank" title="YIKES Inc.">YIKES Inc.</a></p>
					</div><!-- .yikes-woo-review-us-body -->
				</div>

				<div class="postbox yikes-woo-buy-us yikes-woo-all-about-us-box" id="yikes-woo-buy-us">
					<h3 class="yikes-woo-buy-us-title">Custom Product Tabs Pro</h3>
					<div class="yikes-woo-buy-us-body">
						<h4><?php _e( 'Check out Custom Product Tabs Pro!', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?> </h4>
						<p><?php _e( 'Create global tabs, add tabs to products based on categories or tags, add tab content to search results, and more!', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?></p>
						<a class="button button-primary" href="https://yikesplugins.com/plugin/custom-product-tabs-pro/" target="_blank">
							<?php _e( 'Custom Product Tabs Pro', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?>
						</a>
					</div><!-- .yikes-woo-buy-us-body -->
				</div>

				<?php do_action( 'yikes-woo-settings-area' ); ?>
			</div>
			<?php
		}
		
	}

	new YIKES_Custom_Product_Tabs_Premium();