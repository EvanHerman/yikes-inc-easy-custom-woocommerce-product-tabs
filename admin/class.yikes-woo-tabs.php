<?php

if ( ! class_exists( 'YIKES_Custom_Product_Tabs_Custom_Tabs' ) ) {

	class YIKES_Custom_Product_Tabs_Custom_Tabs {


		public function __construct() {

			add_action( 'woocommerce_init', array( $this, 'init' ) );
		}

		public function init() {

			// Add our Custom Product Tabs panel to the WooCommerce panel container
			add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'render_custom_product_tabs' ) );
			add_action( 'woocommerce_product_data_panels', array( $this, 'product_page_yikes_custom_tabs_panel' ) );

			// Save custom tab data
			add_action( 'woocommerce_process_product_meta', array( $this, 'product_save_data' ), 10, 2 );

			// Enqueue our JS / CSS files
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ), 10, 1 );

			// Define our AJAX calls
			add_action( 'wp_ajax_yikes_woo_get_wp_editor', array( $this, 'yikes_woo_get_wp_editor' ) );
			add_action( 'wp_ajax_yikes_woo_save_product_tabs', array( $this, 'yikes_woo_save_product_tabs' ) );

			// Duplicate any custom tabs when a product is duplicated
			add_action( 'woocommerce_product_duplicate', array( $this, 'yikes_woo_dupe_custom_product_tabs' ), 10, 2 );
		}


		/**
		* Enqueue all the required scripts and styles on the appropriate pages
		*
		* @param string | $hook | The current page slug
		*/
		public function enqueue_scripts_and_styles( $hook ) {
			global $post;
			global $wp_version;
			if ( $hook === 'post-new.php' || $hook === 'post.php' ) {
				if ( isset( $post->post_type ) &&  $post->post_type === 'product' ) {

					// Enqueue WordPress' built-in editor functions - added in WPv4.8
					if ( function_exists( 'wp_enqueue_editor' ) ) {
						wp_enqueue_editor();
					}

					$suffix = SCRIPT_DEBUG ? '' : '.min';

					// script
					wp_enqueue_script ( 'repeatable-custom-tabs', YIKES_Custom_Product_Tabs_URI . "js/repeatable-custom-tabs{$suffix}.js" , array( 'jquery' ) , YIKES_Custom_Product_Tabs_Version );
					wp_localize_script( 'repeatable-custom-tabs', 'repeatable_custom_tabs', array(
						'loading_gif'                   => '<img src="' . admin_url( 'images/loading.gif' ) . '" alt="preloader" class="loading-wp-editor-gif custom-tabs-preloader" />',
						'ajaxurl'                       => admin_url( 'admin-ajax.php' ),
						'get_wp_editor_security_nonce'  => wp_create_nonce( 'yikes_woo_get_wp_editor_nonce' ),
						'save_tab_as_reusable_nonce'    => wp_create_nonce( 'yikes_woo_save_tab_as_reusable_nonce' ),
						'fetch_reusable_tabs_nonce'     => wp_create_nonce( 'yikes_woo_fetch_reusable_tabs_nonce' ),
						'fetch_reusable_tab_nonce'      => wp_create_nonce( 'yikes_woo_fetch_reusable_tab_nonce' ),
						'delete_reusable_tab_nonce'     => wp_create_nonce( 'yikes_woo_delete_reusable_tab_nonce' ),
						'save_product_tabs_nonce'       => wp_create_nonce( 'yikes_woo_save_product_tabs_nonce' ),
						'global_post_id'                => $post->ID,
						'get_wp_editor_failure_message' => __( 'Sorry! An error has occurred while trying to retrieve the editor. Please refresh the page and try again.', 'yikes-inc-easy-custom-woocommerce-product-tabs' ),
						'wp_version_four_eight'         => $wp_version >= '4.8',
					) );

					wp_enqueue_script ( 'repeatable-custom-tabs-shared', YIKES_Custom_Product_Tabs_URI . "js/repeatable-custom-tabs-shared{$suffix}.js", array( 'jquery' ), YIKES_Custom_Product_Tabs_Version );
					wp_localize_script( 'repeatable-custom-tabs-shared', 'repeatable_custom_tabs_shared', array(
						'loading_gif'                   => '<img src="' . admin_url( 'images/loading.gif' ) . '" alt="preloader" class="loading-wp-editor-gif custom-tabs-preloader" />',
						'ajaxurl'                       => admin_url( 'admin-ajax.php' ),
						'get_wp_editor_security_nonce'  => wp_create_nonce( 'yikes_woo_get_wp_editor_nonce' ),
						'get_wp_editor_failure_message' => __( 'Sorry! An error has occurred while trying to retrieve the editor. Please refresh the page and try again.', 'yikes-inc-easy-custom-woocommerce-product-tabs' ),
					) );

					// styles
					wp_enqueue_style( 'repeatable-custom-tabs-styles' , YIKES_Custom_Product_Tabs_URI . "css/repeatable-custom-tabs{$suffix}.css", array(), YIKES_Custom_Product_Tabs_Version, 'all' );

					// JS lity modal library and CSS
					wp_enqueue_script( 'lity-js', YIKES_Custom_Product_Tabs_URI . "js/lity{$suffix}.js" , array( 'jquery' ) , YIKES_Custom_Product_LITY_Version );
					wp_enqueue_style( 'lity-css', YIKES_Custom_Product_Tabs_URI . "css/lity{$suffix}.css", array(), YIKES_Custom_Product_LITY_Version, 'all' );
				}
			}
		}

		/**
		 * Adds a new tab to the Product Data postbox in the admin product interface
		 */
		public function render_custom_product_tabs() {
			echo '<li class="yikes_wc_product_tabs_tab"><a href="#yikes_woocommerce_custom_product_tabs"><span>' . __( 'Custom Tabs', 'yikes-inc-easy-custom-woocommerce-product-tabs' ) . '</span></a></li>';
		}


		/**
		 * Adds the panel to the Product Data postbox in the product interface
		 */
		public function product_page_yikes_custom_tabs_panel() {

			// Require & instantiate our HTML class
			require_once( YIKES_Custom_Product_Tabs_Path . 'admin/class.yikes-woo-generate-html.php' );
			$HTML = new Yikes_Woo_Custom_Product_Tabs_HTML();

			// Call our function to generate the HTML
			$HTML->generate_html();
		}

		/**
		* Save the tab data.
		*
		* @param int    | $post_id
		* @param object | $post
		*/
		public function product_save_data( $post_id, $post ) {

			// Make sure we have a $post_id
			if ( empty( $post_id ) ) {
				return;
			}

			// Save our tabs!
			$this->save_tabs( $post_id, $is_ajax = false );
		}

		/**
		* Save the tabs to the database
		*
		* @since 1.5
		*
		* @param int  | $post_id 	  | The ID of the post that has custom tabs
		* @param bool | $is_ajax_flag | A flag signifying whether we're calling this function from an AJAX call
		*/
		protected function save_tabs( $post_id, $is_ajax_flag ) {
			$tab_data = array();

			$number_of_tabs = $_POST['number_of_tabs'];

			// Create an array for tab_ids that we will use later
			$current_saved_tab_id_array = array();
			$remove_a_tab_from_reusable = false;

			// Fetch the reusable tab options (we'll use this later)
			$reusable_tab_options_array = get_option( 'yikes_woo_reusable_products_tabs_applied', array() );

			// Check if this tab has reusable tabs and set flag
			$post_has_reusable_tabs = false;
			if ( isset ( $reusable_tab_options_array[$post_id] ) ) {
				$post_has_reusable_tabs = true;
			}

			$i = 1;
			while( $i <= $number_of_tabs ) {

				// Deal with saving the tab content

				$tab_title   = isset( $_POST['_yikes_wc_custom_repeatable_product_tabs_tab_title_' . $i] ) ? stripslashes( $_POST['_yikes_wc_custom_repeatable_product_tabs_tab_title_' . $i] ) : '';
				$tab_content = isset( $_POST['_yikes_wc_custom_repeatable_product_tabs_tab_content_' . $i] ) ? stripslashes( $_POST['_yikes_wc_custom_repeatable_product_tabs_tab_content_' . $i] ) : '';

				if ( empty( $tab_title ) && empty( $tab_content ) ) {

					// clean up if the custom tabs are removed
					unset( $tab_data[$i] );

				} else {

					$tab_id = '';

					if ( $tab_title ) {
						$tab_id = urldecode( sanitize_title( $tab_title ) );
					}

					// push the data to the array
					$tab_data[$i] = array( 'title' => $tab_title, 'id' => $tab_id, 'content' => $tab_content );
				}

				// Deal with saving / applying globally saved tabs

				if ( isset( $_POST['_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' . $i] ) && isset( $_POST['_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' . $i . '_action'] )
					&& ! empty( $_POST['_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' . $i] ) && ! empty( $_POST['_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' . $i . '_action'] ) ) {

					// Store the tab_id and action
					$reusable_tab_id     = $_POST['_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' . $i];
					$reusable_tab_action = $_POST['_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' . $i . '_action'];

					// If $reusable_tab_options_array is not empty, we've done this before
					if ( ! empty( $reusable_tab_options_array ) ) {

						// If action is 'add', add the tab!
						if ( $reusable_tab_action === 'add' ) {

							$reusable_tab_options_array[$post_id][$reusable_tab_id] = array(
								'post_id' => $post_id,
								'reusable_tab_id' => $reusable_tab_id,
								'tab_id' => $tab_id
							);

							// Update our applied tabs array
							update_option( 'yikes_woo_reusable_products_tabs_applied', $reusable_tab_options_array );

						} elseif ( $reusable_tab_action === 'remove' ) {

							// This tab will no longer be affected by the global/reusable tab changes
							unset( $reusable_tab_options_array[$post_id][$reusable_tab_id] );

							// Update our applied tabs array
							update_option( 'yikes_woo_reusable_products_tabs_applied', $reusable_tab_options_array );
						}
					} elseif ( $reusable_tab_action === 'add' ) {

						// First time adding a new tab
						$reusable_tab_options_array_to_save = array();
						$reusable_tab_options_array_to_save[$post_id][$reusable_tab_id] = array(
							'post_id' => $post_id,
							'reusable_tab_id' => $reusable_tab_id,
							'tab_id' => $tab_id
						);

						// Update our applied tabs array
						update_option( 'yikes_woo_reusable_products_tabs_applied', $reusable_tab_options_array_to_save );
					}


					// Add this ID to our array of saved tab IDs
					$current_saved_tab_id_array[] = $tab_id;
				}

				$i++;
			}

			// Let's check our $current_saved_tab_id_array and see if we need to remove any reusable tabs
			if ( $post_has_reusable_tabs === true ) {

				// If we have tabs...
				if ( ! empty( $current_saved_tab_id_array ) ) {

					// Loop through our reusable tab array
					foreach( $reusable_tab_options_array[ $post_id ] as $id => $reusable_tab_array ) {

						// If we find one of our reusable tabs is no longer part of this post, remove it
						if ( ! in_array( $reusable_tab_array['tab_id'], $current_saved_tab_id_array ) ) {

							unset( $reusable_tab_options_array[ $post_id ][ $id ] );
							$remove_a_tab_from_reusable = true;
						}
					}
				} else {

					// If we don't have any current tabs then we need to delete this post's option
					unset( $reusable_tab_options_array[ $post_id ] );
					$remove_a_tab_from_reusable = true;
				}

				// If we removed a tab, then update our applied tabs array
				if ( $remove_a_tab_from_reusable === true ) {
					update_option( 'yikes_woo_reusable_products_tabs_applied', $reusable_tab_options_array );
				}
			}

			// Reset the array count, when items are removed
			$tab_data = array_values( $tab_data );

			// update the post data
			update_post_meta( $post_id, 'yikes_woo_products_tabs', $tab_data );

			if ( $is_ajax_flag === true ) {
				return true;
			} else {

				// Fire off our Custom Product Tabs PRO action for taxonomy handling
				do_action( 'yikes-woo-handle-tabs-on-product-save', $post_id );
			}
		}

		/* AJAX Functions */

		/**
		* [AJAX] Return wp_editor HTML
		* (this is a bit funky, I know, but it's part of dynamically adding / removing the WYSIWYG )
		*
		* @since 1.5
		*
		* @param string $_POST['textarea_id'] ID of the textarea that we're initializing wp_editor with
		* @param string $_POST['tab_content'] content to pre-supply the text editor with
		* @return string wp_editor HTML
		*/
		public function yikes_woo_get_wp_editor() {

			// Verify nonce
			if ( ! check_ajax_referer( 'yikes_woo_get_wp_editor_nonce', 'security_nonce', false ) ) {
				wp_send_json_error();
			}

			// Get & sanitize the $_POST var textarea_id
			$textarea_id = htmlspecialchars( filter_var( $_POST['textarea_id'], FILTER_UNSAFE_RAW ) );

			// Check if we have tab content
			$tab_content = isset( $_POST['tab_content'] ) ? $_POST['tab_content'] : '';

			// Set up options
			$wp_editor_options = array(
				'textarea_name' => $textarea_id,
				'textarea_rows' => 8,
			);

			// Return wp_editor HTML
			wp_editor( stripslashes( $tab_content ), $textarea_id, $wp_editor_options );
			exit;
		}

		/**
		* [AJAX] Save all tabs for the current product
		*
		* @since 1.5
		*
		* @param  string $_POST['post_id']   	| ID of the current product (post)
		* @param  array  $_POST['product_tabs'] | array of all the tab data
		* @return object success w/ message || failure w/ message
		*/
		public function yikes_woo_save_product_tabs() {

			// Verify the nonce
			if ( ! check_ajax_referer( 'yikes_woo_save_product_tabs_nonce', 'security_nonce', false ) ) {
				wp_send_json_error();
			}

			// Get our product id
			if ( isset( $_POST['post_id'] ) ) {
				$post_id = filter_var( $_POST['post_id'], FILTER_SANITIZE_NUMBER_INT );
			} else {

				// Fail gracefully...
				wp_send_json_error( array( 'message' => __( 'Could not find the product!', 'yikes-inc-easy-custom-woocommerce-product-tabs' ) ) );
			}

			// Save our tabs!
			$success = $this->save_tabs( $post_id, $is_ajax = true );

			if ( $success === true ) {
				wp_send_json_success( array( 'message' => __( 'Your tabs have been saved', 'yikes-inc-easy-custom-woocommerce-product-tabs' ) ) );
			} else {
				wp_send_json_error( array( 'message' => __( 'Uh oh! Something went wrong with saving. Please try again.', 'yikes-inc-easy-custom-woocommerce-product-tabs' ) ) );
			}
		}

		/**
		* When a WooCommere product is duplicated, check for and duplicate the custom tabs.
		*
		* @param object	| $duplicated_product | The new, duplicated product
		* @param object | $original_product   | The original product
		*/
		public function yikes_woo_dupe_custom_product_tabs( $duplicate_product, $original_product ) {
			$old_post_id = method_exists( $original_product, 'get_id' ) === true ? $original_product->get_id() : $original_product->ID;
			$new_post_id = method_exists( $duplicate_product, 'get_id' ) === true ? $duplicate_product->get_id() : $duplicate_product->ID;

			$current_products_tabs = get_post_meta( $old_post_id, 'yikes_woo_products_tabs', true );

			if ( ! empty( $current_products_tabs ) ) {
				update_post_meta( $new_post_id, 'yikes_woo_products_tabs', $current_products_tabs );
			}
		}



	}

	new YIKES_Custom_Product_Tabs_Custom_Tabs();
}
