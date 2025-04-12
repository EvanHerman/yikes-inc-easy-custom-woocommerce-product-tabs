<?php

if ( ! class_exists( 'YIKES_Custom_Product_Tabs_Saved_Tabs' ) ) {

	class YIKES_Custom_Product_Tabs_Saved_Tabs {

		public function __construct() {

			// Add our custom settings page
			add_action( 'admin_menu', array( $this, 'yikes_woo_register_settings_page' ), 10 );

			// Define our options and filters
			add_action( 'admin_init', array( $this, 'init' ) );
		}

		public function init() {

			// Add our custom options for saved tabs
			add_option( 'yikes_woo_reusable_products_tabs', array() );
			add_option( 'yikes_woo_reusable_products_tabs_applied', array() );

			// Enqueue our JS / CSS files
			add_action( 'admin_enqueue_scripts' , array( $this , 'enqueue_scripts_and_styles' ), 10, 1 );

			// Define our AJAX calls
			add_action( 'wp_ajax_yikes_woo_save_tab_as_reusable', array( $this, 'yikes_woo_save_tab_as_reusable' ) );
			add_action( 'wp_ajax_yikes_woo_fetch_reusable_tabs', array( $this, 'yikes_woo_fetch_reusable_tabs' ) );
			add_action( 'wp_ajax_yikes_woo_fetch_reusable_tab', array( $this, 'yikes_woo_fetch_reusable_tab' ) );
			add_action( 'wp_ajax_yikes_woo_delete_reusable_tab_handler', array( $this, 'yikes_woo_delete_reusable_tab_handler' ) );

			// Duplicate any saved tabs when a product is duplicated
			add_filter( 'woocommerce_product_duplicate', array( $this, 'yikes_woo_dupe_saved_tabs_on_product_dupe' ), 11, 2 );

			// Delete any saved tabs when a product is deleted
			add_action( 'delete_post', array( $this, 'delete_saved_tabs_on_product_delete' ), 10, 1 );
		}

		/**
		* Enqueue our scripts and styles
		*
		* @param string | $hook | The current page
		*/
		public function enqueue_scripts_and_styles( $hook ) {

			if ( $hook === 'toplevel_page_' . YIKES_Custom_Product_Tabs_Settings_Page ) {

				$suffix = SCRIPT_DEBUG ? '' : '.min';

				// JavaScript
				wp_enqueue_script ( 'repeatable-custom-tabs-settings', YIKES_Custom_Product_Tabs_URI . "js/repeatable-custom-tabs-settings{$suffix}.js", array( 'jquery' ), YIKES_Custom_Product_Tabs_Version );
				wp_localize_script( 'repeatable-custom-tabs-settings', 'repeatable_custom_tabs_settings', array(
					'loading_gif'                  => '<img src="' . admin_url( 'images/loading.gif' ) . '" alt="preloader" class="loading-wp-editor-gif-settings" />',
					'ajaxurl'                      => admin_url( 'admin-ajax.php' ),
					'tab_list_page_url'            => esc_url_raw( add_query_arg( array( 'page' => YIKES_Custom_Product_Tabs_Settings_Page ), admin_url() ) ),
					'save_tab_as_reusable_nonce'   => wp_create_nonce( 'yikes_woo_save_tab_as_reusable_nonce' ),
					'delete_reusable_tab_nonce'    => wp_create_nonce( 'yikes_woo_delete_reusable_tab_nonce' ),
					'is_cptpro_enabled'            => defined( 'YIKES_Custom_Product_Tabs_Pro_Enabled' ) ? true : false,
					'confirm_delete_single_tab'    => __( 'Are you sure you want to delete this tab?', 'yikes-inc-easy-custom-woocommerce-product-tabs' ),
					'confirm_delete_bulk_tabs'     => __( 'Are you sure you want to delete these tabs? This cannot be undone.', 'yikes-inc-easy-custom-woocommerce-product-tabs' ),
				) );

				wp_enqueue_script ( 'repeatable-custom-tabs-shared', YIKES_Custom_Product_Tabs_URI . "js/repeatable-custom-tabs-shared{$suffix}.js", array( 'jquery' ), YIKES_Custom_Product_Tabs_Version );
				wp_localize_script( 'repeatable-custom-tabs-shared', 'repeatable_custom_tabs_shared', array(
						'loading_gif' 					=> '<img src="' . admin_url( 'images/loading.gif' ) . '" alt="preloader" class="loading-wp-editor-gif" />',
						'ajaxurl' 						=> admin_url( 'admin-ajax.php' ),
						'get_wp_editor_security_nonce' 	=> wp_create_nonce( 'yikes_woo_get_wp_editor_nonce' ),
						'get_wp_editor_failure_message' => __( 'Sorry! An error has occurred while trying to retrieve the editor. Please refresh the page and try again.', 'yikes-inc-easy-custom-woocommerce-product-tabs' ),
					) );

				// Twitter script for our Tweet Us button
				wp_enqueue_script( 'twitter-button', YIKES_Custom_Product_Tabs_URI . "js/twitter-embed{$suffix}.js", array(), YIKES_Custom_Product_Tabs_Version );

				// CSS
				wp_enqueue_style( 'repeatable-custom-tabs-styles' , YIKES_Custom_Product_Tabs_URI . "css/repeatable-custom-tabs{$suffix}.css", array(), YIKES_Custom_Product_Tabs_Version, 'all' );

			}

		}

		/* AJAX Functions */

		/**
		* [AJAX] Save a tab as reusable
		*
		* @since 1.5
		*
		* @param string  | $_POST['tab_title']   | Tab title to save
		* @param string  | $_POST['tab_content'] | Tab content to save
		* @param string  | $_POST['tab_id']      | Optional. Tab ID we're updating
		* @param string  | $_POST['tab_name']    | Tab name to save
		* @param array   | $_POST['taxonomies']  | Optional. Array of taxonomies.
		*
		* @return string | JSON response
		*/
		public function yikes_woo_save_tab_as_reusable() {

			// Verify the nonce
			if ( ! check_ajax_referer( 'yikes_woo_save_tab_as_reusable_nonce', 'security_nonce', false ) ) {
				wp_send_json_error();
			}

			// Define it now, because we may use this later...
			$return_redirect_url = '';

			// Get our $_POST vars
			if ( isset( $_POST['tab_title'] ) && ! empty( $_POST['tab_title'] ) ) {
				$tab_title     = stripslashes( $_POST['tab_title'] );
				$tab_string_id = urldecode( sanitize_title( $tab_title ) );
			} else {
				wp_send_json_error( array( 'reason' => 'no tab title', 'message' => 'Please fill out the tab title before saving.' ) );
			}

			$tab_content = isset( $_POST['tab_content'] ) && ! empty( $_POST['tab_content'] ) ? $_POST['tab_content'] : '';
			$tab_id      = isset( $_POST['tab_id'] ) && ! empty( $_POST['tab_id'] ) ? $_POST['tab_id'] : '';
			$tab_name    = isset( $_POST['tab_name'] ) ? $_POST['tab_name'] : '';
			$global_tab  = isset( $_POST['global_tab'] ) && $_POST['global_tab'] === 'true' ? true : false;

			// Remove taxonomies if we're using a global tab
			$taxonomies  = isset( $_POST['taxonomies'] ) && ! empty( $_POST['taxonomies'] ) && $global_tab === false ? $_POST['taxonomies'] : array();

			// Get our saved tabs array
			$yikes_custom_tab_data = get_option( 'yikes_woo_reusable_products_tabs', array() );

			// If the saved tabs array is empty, create a new array and save it (first time we've done this)
			if ( empty( $yikes_custom_tab_data ) ) {
				$yikes_custom_tab_options_array = array();
				$new_tab = array(
					'tab_title'   => $tab_title,
					'tab_name'    => $tab_name,
					'tab_content' => $tab_content,
					'tab_id'      => 1,
					'taxonomies'  => $taxonomies,
					'tab_slug'    => $tab_string_id,
					'global_tab'  => $global_tab,
				);

				$yikes_custom_tab_options_array[1] = $new_tab;

				do_action( 'yikes-woo-handle-tab-save', $new_tab, 'new' );

				update_option( 'yikes_woo_reusable_products_tabs', $yikes_custom_tab_options_array );

				// Return redirect URL
				$return_redirect_url = esc_url_raw( add_query_arg( array( 'page' => YIKES_Custom_Product_Tabs_Settings_Page, 'saved-tab-id' => 1 ), admin_url() ) );

				// Send response
				wp_send_json_success( array( 'tab_id' => 1, 'redirect' => true, 'redirect_url' => $return_redirect_url ) );
			} else {

				// this is a new tab and we need to create a unique ID
				if ( $tab_id === 'new' ) {

					// Get the max ID we have saved
					foreach ( $yikes_custom_tab_data as $tab_data ) {
						if ( ! isset( $highest_tab_id ) ) {
							$highest_tab_id = $tab_data['tab_id'];
						} else {
							if ( $tab_data['tab_id'] > $highest_tab_id ) {
								$highest_tab_id = $tab_data['tab_id'];
							}
						}
					}

					// Add 1 to the max ID
					$new_tab_id = (int) $highest_tab_id + 1;

					$new_tab =  array(
						'tab_title'   => $tab_title,
						'tab_name'    => $tab_name,
						'tab_content' => $tab_content,
						'tab_id'      => $new_tab_id,
						'taxonomies'  => $taxonomies,
						'tab_slug'    => $tab_string_id,
						'global_tab'  => $global_tab,
					);

					$yikes_custom_tab_data[$new_tab_id] = $new_tab;

					do_action( 'yikes-woo-handle-tab-save', $new_tab, 'new' );

					update_option( 'yikes_woo_reusable_products_tabs', $yikes_custom_tab_data );

					// Return redirect URL
					$return_redirect_url = esc_url_raw( add_query_arg( array( 'page' => YIKES_Custom_Product_Tabs_Settings_Page, 'saved-tab-id' => $new_tab_id ), admin_url( 'admin.php' ) ) );

					// Send response
					wp_send_json_success( array( 'tab_id' => $new_tab_id, 'redirect' => true, 'redirect_url' => $return_redirect_url ) );

				} else {

					// This is an existing tab, so just update it
					$saved_tab = array(
						'tab_title'   => $tab_title,
						'tab_name'    => $tab_name,
						'tab_content' => $tab_content,
						'tab_id'      => $tab_id,
						'taxonomies'  => $taxonomies,
						'tab_slug'    => $tab_string_id,
						'global_tab'  => $global_tab,
					);

					$yikes_custom_tab_data[$tab_id] = $saved_tab;

					do_action( 'yikes-woo-handle-tab-save', $saved_tab, 'existing' );

					update_option( 'yikes_woo_reusable_products_tabs', $yikes_custom_tab_data );

					// Now apply this updated tab's data to custom product tabs that use it

					// Get the array of applied product tabs
					$reusable_tab_options_array = get_option( 'yikes_woo_reusable_products_tabs_applied', array() );

					// Flag so we know we found a post and we should update it
					$update_post_meta_flag = false;

					// Flag so we know we have to update the yikes_woo_reusable_products_tabs_applied
					$update_applied_products_array = false;

					foreach( $reusable_tab_options_array as $post_id => $reusable_tab_data ) {

						// Fetch the relevant postmeta field
						$custom_tab_data = yikes_custom_tabs_maybe_unserialize( get_post_meta( $post_id, 'yikes_woo_products_tabs', true ) );

						// If we don't have custom tab data then continue
						if ( empty( $custom_tab_data ) ) {
							continue;
						}

						// Loop through $custom_tab_data and find the custom tab that was just updated
						foreach( $custom_tab_data as $index => $tab ) {
							if ( isset( $reusable_tab_data[ $tab_id ] ) && $tab['id'] === $reusable_tab_data[ $tab_id ]['tab_id'] ) {
								$custom_tab_data[$index]['title']   = $tab_title;
								$custom_tab_data[$index]['name']    = $tab_name;
								$custom_tab_data[$index]['content'] = $tab_content;
								$custom_tab_data[$index]['id']      = $tab_string_id;

								// We may need to update the tab_string_id too
								if ( $tab_string_id !== $tab['id'] ) {
									$reusable_tab_options_array[$post_id][$tab_id]['tab_id'] = $tab_string_id;
									$update_applied_products_array = true;
								}
								$update_post_meta_flag = true;
							}
						}

						// If we updated a tab, save it
						if ( $update_post_meta_flag === true ) {
							update_post_meta( $post_id, 'yikes_woo_products_tabs', $custom_tab_data );
						}
					}

					// If we updated the tab_string_id in the yikes_woo_reusable_products_tabs_applied array, save it
					if ( $update_applied_products_array === true ) {
						update_option( 'yikes_woo_reusable_products_tabs_applied', $reusable_tab_options_array);
					}
				}
			}

			wp_send_json_success( array( 'redirect' => false ) );
		}

		/**
		* [AJAX] Fetch a single reusable
		*
		* @since 1.5
		*
		* @return object success w/ tab data || failure w/ message
		*/
		public function yikes_woo_fetch_reusable_tab() {

			// Verify the nonce
			if ( ! check_ajax_referer( 'yikes_woo_fetch_reusable_tab_nonce', 'security_nonce', false ) ) {
				wp_send_json_error();
			}

			$tab_id = isset( $_POST['tab_id'] ) ? filter_var( $_POST['tab_id'], FILTER_SANITIZE_NUMBER_INT ) : '';

			// Get the array of saved tabs
			$saved_tabs = get_option( 'yikes_woo_reusable_products_tabs' );

			$tab = isset( $saved_tabs[$tab_id] ) ? $saved_tabs[$tab_id] : false;

			if ( $tab !== false ) {

				$tab['tab_content'] = stripslashes( $tab['tab_content'] );

				wp_send_json_success( $tab );
			} else {
				wp_send_json_success( array( 'message' => __( 'Could not find the tab. Please try again.', 'yikes-inc-easy-custom-woocommerce-product-tabs' ) ) );
			}

			// If we get this far, send error
			wp_send_json_error( array( 'message' => __( 'Uh oh. Something went wrong.', 'yikes-inc-easy-custom-woocommerce-product-tabs' ) ) );
		}

		/**
		* [AJAX] Fetch all reusable tabs
		*
		* @since 1.5
		*
		* @return object success w/ saved_tabs || failure w/ message
		*/
		public function yikes_woo_fetch_reusable_tabs() {

			// Verify the nonce
			if ( ! check_ajax_referer( 'yikes_woo_fetch_reusable_tabs_nonce', 'security_nonce', false ) ) {
				wp_send_json_error();
			}

			// Get the array of saved tabs
			$saved_tabs = get_option( 'yikes_woo_reusable_products_tabs' );

			$fetch_tab_content = isset( $_POST['fetch_tab_content'] ) ? filter_var( $_POST['fetch_tab_content'], FILTER_VALIDATE_BOOLEAN ) : false;

			// We don't need to pass the content back, and it could be a LOT, so let's just remove it
			if ( $fetch_tab_content === false && ! empty( $saved_tabs ) ) {
				foreach( $saved_tabs as $key => $tab ) {
					unset( $saved_tabs[$key]['tab_content'] );
				}
			}

			if ( ! empty( $saved_tabs ) ) {
				wp_send_json_success( json_encode( $saved_tabs ) );
			} else {
				wp_send_json_success( array( 'message' => 'No saved tabs were found.' ) );
			}

			// If we get this far, send error
			wp_send_json_error( array( 'message' => 'Uh oh. Something went wrong.' ) );
		}

		/**
		* [AJAX] Delete a reusable tab
		*
		* @since 1.5
		*
		* @param  string $_POST['tab_id']
		* @return object success w/ saved_tabs || failure w/ message
		*/
		public function yikes_woo_delete_reusable_tab_handler() {

			// Verify the nonce
			if ( ! check_ajax_referer( 'yikes_woo_delete_reusable_tab_nonce', 'security_nonce', false ) ) {
				wp_send_json_error();
			}

			// Get our tab_id
			if ( isset( $_POST['tab_id'] ) && ! empty( $_POST['tab_id'] ) ) {

				$tab_ids = $_POST['tab_id'];

				// if $tab_ids isn't an array, turn it into one
				if ( ! is_array( $tab_ids ) ) {
					$tab_ids = array( $tab_ids );
				}

				// Set this up before we use it
				$return_vars = array();

				foreach( $tab_ids as $tab_id ) {

					// Delete the tab, store the return values in $return_vars
					$return_vars = $this->yikes_woo_delete_reusable_tab( $tab_id );

					// Make sure $return_vars is what we think it is, and check if our delete failed
					if ( is_array( $return_vars ) && isset( $return_vars['success'] ) && $return_vars['success'] === false ) {

						// If something failed, let's return
						wp_send_json_error( $return_vars );
					}
				}
			} else {

				// Fail if we don't have a tab_id
				wp_send_json_error( array( 'reason' => 'no tab id' ) );
			}

			// Make sure $return_vars is what we think it is, and check if our delete was successful
			if ( is_array( $return_vars ) && isset( $return_vars['success'] ) && $return_vars['success'] === true ) {

				// If nothing failed, let's return
				wp_send_json_success( $return_vars );
			}

			// We shouldn't have gotten this far, but if we did let's return failure
			wp_send_json_error();
		}

		/* End AJAX Functions */

		/* AJAX Helper Functions */

		/**
		* Delete a saved tab from the options array and delete the tab from the product's tabs array
		*
		* @since 1.5
		*
		* @param  int 	 | $tab_id 	 | unique identifier of a tab
		* @return array  | $response | array of data signifying success, message, reason, and other needed data
		*/
		protected function yikes_woo_delete_reusable_tab( $tab_id ) {

			// The following code will remove the tab from our array of saved tabs

			// Fetch all saved tabs
			$saved_tabs = get_option( 'yikes_woo_reusable_products_tabs' );

			// Check some stuff, then remove the tab from our array of saved tabs
			if ( ! empty( $saved_tabs ) ) {
				if ( isset( $saved_tabs[$tab_id] ) ) {
					unset( $saved_tabs[$tab_id] );
					update_option( 'yikes_woo_reusable_products_tabs', $saved_tabs );
				} else {
					$response = array(
						'success' => false,
						'tab_id' => $tab_id,
						'message' => 'No saved tab with id ' . $tab_id . ' found!',
						'reason' => 'no saved tab found'
					);
					return $response;
				}
			} else {
				$response = array(
					'success' => false,
					'tab_id' => $tab_id,
					'message' => 'No saved tabs found!',
					'reason' => 'no saved tabs found'
				);
				return $response;
			}

			// The following code will remove the tab from our array of applied tabs and postmeta

			// Fetch all applied tabs
			$applied_tabs = get_option( 'yikes_woo_reusable_products_tabs_applied', array() );

			// Set a flag so we know if we need to update the database
			$unset_applied_tabs_flag = false;

			if ( ! empty( $applied_tabs ) ) {

				// Run through all the applied tabs and remove the ones with this ID
				foreach( $applied_tabs as $post_id => $applied_tabs_array ) {

					// If this ID is set, unset the array
					if ( isset( $applied_tabs_array[$tab_id] ) ) {

						// Now we also need to remove this entry from our postmeta
						$post_tabs = yikes_custom_tabs_maybe_unserialize( get_post_meta( $post_id, 'yikes_woo_products_tabs', true ) );

						// Make sure it exists
						if ( ! empty( $post_tabs ) ) {
							foreach ( $post_tabs as $index => $post_tab ) {
								if ( $applied_tabs[$post_id][$tab_id]['tab_id'] === $post_tab['id'] ) {
									unset( $post_tabs[$index] );
									update_post_meta( $post_id, 'yikes_woo_products_tabs', $post_tabs );
								}
							}
						}

						// Unset the array of our applied saved tabs
						unset( $applied_tabs[ $post_id ][ $tab_id ] );

						// If that was the only saved tab for this product, unset the product's array key
						if ( empty( $applied_tabs[ $post_id ] ) ) {
							unset( $applied_tabs[ $post_id ] );
						}
						$unset_applied_tabs_flag = true;
					}
				}

				// If we unset an applied tab, update the database
				if ( $unset_applied_tabs_flag === true ) {
					update_option( 'yikes_woo_reusable_products_tabs_applied', $applied_tabs );
				}
			}

			// If we're on the single tab edit screen, we want to redirect back to tab list so let's return a var
			$return_redirect_url = esc_url_raw( add_query_arg( array( 'page' => YIKES_Custom_Product_Tabs_Settings_Page, 'delete-success' => true ), admin_url() ) );

			$response = array(
				'success' => true,
				'tab_id' => $tab_id,
				'message' => 'Tab successfully deleted!',
				'reason' => 'tab successfully deleted',
				'redirect_url' => $return_redirect_url
			);

			return $response;
		}

		/* End AJAX Helper Functions */


		/* Plugin Settings Page */

		/**
		* Register our settings page
		*
		* @since 1.5
		*
		*/
		public function yikes_woo_register_settings_page() {

			// Add our custom settings page
			add_menu_page(
				apply_filters( 'yikes-woo-settings-menu-title', __( 'Custom Product Tabs', 'yikes-inc-easy-custom-woocommerce-product-tabs' ) ),     // Tab title name (HTML title)
				apply_filters( 'yikes-woo-settings-menu-title', __( 'Custom Product Tabs', 'yikes-inc-easy-custom-woocommerce-product-tabs' ) ),     // Menu page name
				apply_filters( 'yikes-woo-settings-menu-capability', 'publish_products' ),                                                // Capability required
				YIKES_Custom_Product_Tabs_Settings_Page,                                                                                  // Page slug (?page=slug-name)
				array( $this, 'generate_yikes_settings_page' ),                                                                           // Function to generate page
				'dashicons-exerpt-view',                                                                                                  // Icon
				apply_filters( 'yikes-woo-settings-menu-priority', 100 )                                                                  // Position
			);
		}

		/**
		* Include our settings page
		*
		* @since 1.5
		*
		*/
		public function generate_yikes_settings_page() {

			// Get our saved tabs array
			$yikes_custom_tab_data = get_option( 'yikes_woo_reusable_products_tabs', array() );

			// New tab URL - used to supply the 'Add Tab' href
			$new_tab_url = esc_url( add_query_arg( array( 'saved-tab-id' => 'new' ), '?page=' . YIKES_Custom_Product_Tabs_Settings_Page ), admin_url() );

			// If saved_tab_id is set, we should show the single saved tab // add new tab page
			if ( isset( $_GET['saved-tab-id'] ) && ! empty( $_GET['saved-tab-id'] ) ) {

				// Are we trying to add a new tab?
				$new_tab = ( $_GET['saved-tab-id'] === 'new' ) ? true : false;

				// Sanitize saved_tab_id
				$saved_tab_id = filter_var( $_GET['saved-tab-id'], FILTER_SANITIZE_NUMBER_INT );

				// Get the tab
				$tab = isset( $yikes_custom_tab_data[$saved_tab_id] ) ? $yikes_custom_tab_data[$saved_tab_id] : array();

				// Redirect URL
				$redirect = esc_url_raw( add_query_arg( array( 'page' => YIKES_Custom_Product_Tabs_Settings_Page ), admin_url( 'admin.php' ) ) );

				require_once( YIKES_Custom_Product_Tabs_Path . 'admin/page.yikes-woo-saved-tabs-single.php' );

			} else {
				$delete_message_display = 'display: none;';

				// Check if our $_GET variable 'delete-success' is set so we can display a nice success message
				if ( isset( $_GET['delete-success'] ) && $_GET['delete-success'] === '1' ) {
					$delete_message_display = '';
				}

				// Show tab table list
				require_once( YIKES_Custom_Product_Tabs_Path . 'admin/page.yikes-woo-saved-tabs.php' );
			}
		}

		/* End Plugin Settings Page */

		/* Misc. */

		/**
		* When a WooCommere product is duplicated, check for and duplicate the saved tabs.
		*
		* @param object	| $duplicated_product | The new, duplicated product
		* @param object | $original_product   | The original product
		*/
		public function yikes_woo_dupe_saved_tabs_on_product_dupe( $duplicate_product, $original_product ) {

			// First, let's grab our applied saved tab options array
			$saved_tabs_array = get_option( 'yikes_woo_reusable_products_tabs_applied' );

			// Grab the old post's ID
			$old_post_id = method_exists( $original_product, 'get_id' ) === true ? $original_product->get_id() : $original_product->ID;

			// (1) Make sure we have a non-empty array of saved tabs,
			// (2) Makre sure we have the ID of the old post, and then
			// (3) Check for the old post's saved tabs. (If we don't find any, do nothing)
			if ( ! empty( $saved_tabs_array ) && is_array( $saved_tabs_array ) && ! empty( $old_post_id ) && isset( $saved_tabs_array[$old_post_id] ) ) {

				// Grab the new post's ID
				$new_post_id = method_exists( $duplicate_product, 'get_id' ) === true ? $duplicate_product->get_id() : $duplicate_product->ID;

				// Loop through the $saved_tabs_array and update the post_id item
				$new_products_saved_tabs = $saved_tabs_array[$old_post_id];
				foreach ( $new_products_saved_tabs as $saved_tab_id => $saved_tab ) {
					$new_products_saved_tabs[$saved_tab_id]['post_id'] = $new_post_id;
				}

				// Add the old post's saved tabs, with the new post's ID as the key
				$saved_tabs_array[$new_post_id] = $new_products_saved_tabs;

				// Update the saved tab's option
				update_option( 'yikes_woo_reusable_products_tabs_applied', $saved_tabs_array );
			}
		}

		/**
		* When a WooCommerce product is deleted, delete any saved tabs from the saved tabs option
		*
		* @param int | $post_id
		*/
		public function delete_saved_tabs_on_product_delete( $post_id ) {

			$post = get_post( $post_id );

			if ( $post->post_type !== 'product' ) {
				return;
			}

			// Get our saved tabs option
			$saved_tabs = get_option( 'yikes_woo_reusable_products_tabs_applied' );

			if ( empty( $saved_tabs ) ) {
				return;
			}

			if ( ! empty( $saved_tabs[ $post_id ] ) ) {
				unset( $saved_tabs[ $post_id ] );
				update_option( 'yikes_woo_reusable_products_tabs_applied', $saved_tabs );
			}
		}

		/* End Misc. */

	}

	new YIKES_Custom_Product_Tabs_Saved_Tabs();
}
