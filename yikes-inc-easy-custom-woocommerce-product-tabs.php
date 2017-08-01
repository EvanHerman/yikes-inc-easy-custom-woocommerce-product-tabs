<?php
/**
 * Plugin Name: Custom Product Tabs for WooCommerce 
 * Plugin URI: http://www.yikesinc.com
 * Description: Extend WooCommerce to add and manage custom product tabs. Create as many product tabs as needed per product.
 * Author: YIKES, Inc
 * Author URI: http://www.yikesinc.com
 * Version: 1.5.16
 * Text Domain: yikes-inc-easy-custom-woocommerce-product-tabs
 * Domain Path: languages/
 *
 * Copyright: (c) 2014-2015 YIKES Inc.
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 */

	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) exit;
	
	// must include plugin.php to use is_plugin_active()
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		/**
		* The YikesWooCommerceCustomProductTabs global object
		* @name $yikes_woocommerce_custom_product_tabs
		* @global YikesWooCommerceCustomProductTabs $GLOBALS['yikes_woocommerce_custom_product_tabs']
		*/
		$GLOBALS['yikes_woocommerce_custom_product_tabs'] = new YikesWooCommerceCustomProductTabs();
	} else {
		/* Deactivate the plugin, and display our error notification */
		deactivate_plugins( '/yikes-inc-easy-custom-woocommerce-product-tabs/yikes-inc-easy-custom-woocommerce-product-tabs.php' );
		add_action( 'admin_notices' , 'display_admin_notice_error' );
	}
	
	/**
	* display_admin_notice_error()
	* Display our error admin notice if WooCommerce is not installed+active
	*/
	function display_admin_notice_error() {
		?>	
			<!-- hide the 'Plugin Activated' default message -->
			<style>
			#message.updated {
				display: none;
			}
			</style>
			<!-- display our error message -->
			<div class="error">
				<p><?php _e( 'Custom Product Tabs for WooCommerce could not be activated because WooCommerce is not installed and active.', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?></p>
				<p><?php _e( 'Please install and activate ', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?><a href="<?php echo admin_url( 'plugin-install.php?tab=search&type=term&s=WooCommerce' ); ?>" title="WooCommerce">WooCommerce</a><?php _e( ' before activating the plugin.', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?></p>
			</div>
		<?php
	}

	// When the plugin is uninstalled, brush away all footprints; there shall be no trace.
	register_uninstall_hook( __FILE__, 'uninstall_custom_product_tabs_for_woocommerce' );

	function uninstall_custom_product_tabs_for_woocommerce() {
		global $wpdb;

		// Remove all of our 'yikes_woo_products_tabs' post meta
		$wpdb->delete(

			// Table
			"{$wpdb->prefix}postmeta",

			// Where
			array( 'meta_key' => 'yikes_woo_products_tabs' )
		);

		// Remove our 'yikes_woo_reusable_products_tabs' option
		delete_option( 'yikes_woo_reusable_products_tabs' );

		// Remove our 'yikes_woo_reusable_products_tabs_applied' option
		delete_option( 'yikes_woo_reusable_products_tabs_applied' );
	}
	
	/**
	* Initialize the Custom Product Tab Class
	*/
	class YikesWooCommerceCustomProductTabs {

		private $tab_data = false;

		/** plugin version number */
		const VERSION = '1.5.16';

		/** plugin text domain */
		const TEXT_DOMAIN = 'yikes-inc-easy-custom-woocommerce-product-tabs';

		/** plugin version name */
		const VERSION_OPTION_NAME = 'yikes_woocommerce_custom_product_tabs_db_version';

		/**
		* Define the page slug for our plugin's custom settings page in one central location
		*
		* @since 1.5
		* @access protected
		* @var string | $settings_page_slug | The page slug for our plugin's custom settings page
		*/
		protected $settings_page_slug; 

		/**
		* Gets things started by adding an action to initialize this plugin once
		* WooCommerce is known to be active and initialized
		*/
		public function __construct() {
			// Installation
			if ( is_admin() && ! defined( 'DOING_AJAX' ) ) $this->install();
			add_action( 'woocommerce_init', array( $this, 'init' ) );
			global $typenow;
			add_action( 'init', array( $this, 'load_custom_export_filters' ) );

			// Add our custom options for saving tabs
			add_option( 'yikes_woo_reusable_products_tabs' );
			add_option( 'yikes_woo_reusable_products_tabs_applied' );

			//Set our variables
			$this->settings_page_slug = 'yikes-woo-settings';
		}
	
		public function load_custom_export_filters() {
			global $pagenow;
			if( 'export.php' == $pagenow ) {
				// add our data to the woocommerce export
				add_filter( 'wc_customer_order_csv_export_order_headers', array( $this, 'yikes_wootabs_wc_csv_export_modify_column_headers' ) );
				add_filter( 'wc_customer_order_csv_export_order_row', array( $this, 'yikes_wootabs_wc_csv_export_modify_row_data' ), 10, 3 );
			}
		}
		
		/**
		 *	Add our data to the standard WooCommerce Export Functionality
		 *	@since 1.4
		**/
		function yikes_wootabs_wc_csv_export_modify_column_headers( $column_headers ) { 
 
			$new_headers = array(
				'yikes_woo_products_tabs' => 'Yikes Inc. Custom WooCommerce Tabs',
			);
		 
			return array_merge( $column_headers, $new_headers );
		}
		
		/**
		*	Append our yikes woo product tab data
		*	@since 1.4
		**/
		function yikes_wootabs_wc_csv_export_modify_row_data( $order_data, $order, $csv_generator ) {
		 
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
		
		/**
		 * Init WooCommerce Product Tabs Lite extension once we know WooCommerce is active
		 */
		public function init() {
			// Backend stuff (show our custom product tabs on edit screen, handle saving data)
			add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'render_custom_product_tabs' ) );
			add_action( 'woocommerce_product_data_panels',     array( $this, 'product_page_yikes_custom_tabs_panel' ) );
			
			add_action( 'woocommerce_process_product_meta',     array( $this, 'product_save_data' ), 10, 2 );

			// Add our custom product tabs section to the product page
			add_filter( 'woocommerce_product_tabs', array( $this, 'add_custom_product_tabs' ) );

			// Allow the use of shortcodes within the tab content
			add_filter( 'yikes_woocommerce_custom_repeatable_product_tabs_content', 'do_shortcode' );
			
			// Enqueue our JS / CSS files
			add_action( 'admin_enqueue_scripts' , array( $this , 'enqueue_tab_scripts' ) );

			// Define our AJAX calls
			add_action( 'wp_ajax_yikes_woo_get_wp_editor', array( $this, 'yikes_woo_get_wp_editor' ) );
			add_action( 'wp_ajax_yikes_woo_save_tab_as_reusable', array( $this, 'yikes_woo_save_tab_as_reusable' ) ); 
			add_action( 'wp_ajax_yikes_woo_fetch_reusable_tabs', array( $this, 'yikes_woo_fetch_reusable_tabs' ) );
			add_action( 'wp_ajax_yikes_woo_fetch_reusable_tab', array( $this, 'yikes_woo_fetch_reusable_tab' ) );
			add_action( 'wp_ajax_yikes_woo_delete_reusable_tab_handler', array( $this, 'yikes_woo_delete_reusable_tab_handler' ) );
			add_action( 'wp_ajax_yikes_woo_save_product_tabs', array( $this, 'yikes_woo_save_product_tabs' ) );
			
			// Add our custom settings page
			add_action( 'admin_menu', array( $this, 'yikes_woo_register_settings_page' ) );

			// Default WYSIWYG to 'visual'
			add_filter( 'wp_default_editor', array( $this, 'yikes_woo_set_editor_to_visual' ), 10, 1 );

			// i18n
			add_action( 'plugins_loaded', array( $this, 'yikes_woo_load_plugin_textdomain' ) );

			// Add settings link to plugin on plugins page
			add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'add_plugin_action_links' ), 10, 1 );

			// Duplicate any custom tabs when a product is duplicated
			add_action( 'woocommerce_product_duplicate', array( $this, 'yikes_woo_dupe_custom_product_tabs' ), 10, 2 );

			// Duplicate any saved tabs when a product is duplicated
			add_filter( 'woocommerce_product_duplicate', array( $this, 'yikes_woo_dupe_saved_tabs_on_product_dupe' ), 11, 2 );
		}
		
		/**
		* Enqueue all the required scripts and styles on the appropriate pages
		*
		* @param string | $hook | The current page slug
		*/
		public function enqueue_tab_scripts( $hook ) {
			global $post;
			global $wp_version;
			if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
				if ( $post->post_type == 'product' ) {

					// Enqueue WordPress' built-in editor functions - added in WPv4.8
					if ( function_exists( 'wp_enqueue_editor' ) ) {
						wp_enqueue_editor();
					}

					// script
					wp_enqueue_script ( 'repeatable-custom-tabs', plugin_dir_url(__FILE__) . 'js/repeatable-custom-tabs.min.js' , array( 'jquery' ) , 'all' );
					wp_localize_script( 'repeatable-custom-tabs', 'repeatable_custom_tabs', array(
						'loading_gif'					=> '<img src="' . admin_url( 'images/loading.gif' ) . '" alt="preloader" class="loading-wp-editor-gif" />',
						'ajaxurl' 						=> admin_url( 'admin-ajax.php' ),
						'get_wp_editor_security_nonce' 	=> wp_create_nonce( 'yikes_woo_get_wp_editor_nonce' ),
						'save_tab_as_reusable_nonce' 	=> wp_create_nonce( 'yikes_woo_save_tab_as_reusable_nonce' ),
						'fetch_reusable_tabs_nonce' 	=> wp_create_nonce( 'yikes_woo_fetch_reusable_tabs_nonce' ),
						'fetch_reusable_tab_nonce' 		=> wp_create_nonce( 'yikes_woo_fetch_reusable_tab_nonce' ),
						'delete_reusable_tab_nonce' 	=> wp_create_nonce( 'yikes_woo_delete_reusable_tab_nonce' ), 
						'save_product_tabs_nonce' 		=> wp_create_nonce( 'yikes_woo_save_product_tabs_nonce' ),
						'global_post_id'				=> $post->ID,
						'get_wp_editor_failure_message' => __('Sorry! An error has occurred while trying to retrieve the editor. Please refresh the page and try again.', 'yikes-inc-easy-custom-woocommerce-product-tabs'),
						'wp_version_four_eight'			=> $wp_version >= '4.8' ? true : false
					) );

					wp_enqueue_script ( 'repeatable-custom-tabs-shared', plugin_dir_url(__FILE__) . 'js/repeatable-custom-tabs-shared.min.js' );
					wp_localize_script( 'repeatable-custom-tabs-shared', 'repeatable_custom_tabs_shared', array(
						'loading_gif'					=> '<img src="' . admin_url( 'images/loading.gif' ) . '" alt="preloader" class="loading-wp-editor-gif" />',
						'ajaxurl' 						=> admin_url( 'admin-ajax.php' ),
						'get_wp_editor_security_nonce'	=> wp_create_nonce( 'yikes_woo_get_wp_editor_nonce' ),
						'get_wp_editor_failure_message' => __('Sorry! An error has occurred while trying to retrieve the editor. Please refresh the page and try again.', 'yikes-inc-easy-custom-woocommerce-product-tabs'),
					) );

					// styles + font
					wp_register_style( 'repeatable-custom-tabs-styles' , plugin_dir_url(__FILE__) . 'css/repeatable-custom-tabs.min.css', '', self::VERSION, 'all' );
					wp_enqueue_style( 'repeatable-custom-tabs-styles' );

					// JS lity modal library and CSS
					wp_enqueue_script( 'lity-js', plugin_dir_url(__FILE__) . 'js/lity.min.js' , array( 'jquery' ) , 'all' );
					wp_enqueue_style( 'lity-css', plugin_dir_url(__FILE__) . 'css/dist/lity.min.css' );
				}
			}

			if ( $hook === 'settings_page_' . $this->settings_page_slug ) {
				// script
				wp_enqueue_script ( 'repeatable-custom-tabs-settings', plugin_dir_url(__FILE__) . 'js/repeatable-custom-tabs-settings.min.js' , array( 'jquery' ) , 'all' );
				wp_localize_script( 'repeatable-custom-tabs-settings', 'repeatable_custom_tabs_settings', array(
					'loading_gif' 					=> '<img src="' . admin_url( 'images/loading.gif' ) . '" alt="preloader" class="loading-wp-editor-gif-settings" />',
					'ajaxurl' 						=> admin_url( 'admin-ajax.php' ),
					'tab_list_page_url' 			=> admin_url( esc_url_raw( 'options-general.php?page=' . $this->settings_page_slug ) ),
					'save_tab_as_reusable_nonce' 	=> wp_create_nonce( 'yikes_woo_save_tab_as_reusable_nonce' ),
					'delete_reusable_tab_nonce' 	=> wp_create_nonce( 'yikes_woo_delete_reusable_tab_nonce' )
				) );

				wp_enqueue_script ( 'repeatable-custom-tabs-shared', plugin_dir_url(__FILE__) . 'js/repeatable-custom-tabs-shared.min.js' );
				wp_localize_script( 'repeatable-custom-tabs-shared', 'repeatable_custom_tabs_shared', array(
						'loading_gif' 					=> '<img src="' . admin_url( 'images/loading.gif' ) . '" alt="preloader" class="loading-wp-editor-gif" />',
						'ajaxurl' 						=> admin_url( 'admin-ajax.php' ),
						'get_wp_editor_security_nonce' 	=> wp_create_nonce( 'yikes_woo_get_wp_editor_nonce' ),
						'get_wp_editor_failure_message' => __('Sorry! An error has occurred while trying to retrieve the editor. Please refresh the page and try again.', 'yikes-inc-easy-custom-woocommerce-product-tabs'),
					) );

				// styles + font
				wp_register_style( 'repeatable-custom-tabs-styles' , plugin_dir_url(__FILE__) . 'css/repeatable-custom-tabs.min.css', '', self::VERSION, 'all' );
				wp_enqueue_style( 'repeatable-custom-tabs-styles' );
			}
		}

		/** Frontend methods ******************************************************/

		/**
		 * Add the custom product tab
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
		 * @since 1.0.0
		 * @param array $tabs array representing the product tabs
		 * @return array representing the product tabs
		 */
		public function add_custom_product_tabs( $tabs ) {
			global $product;

			$product_id = method_exists( $product, 'get_id' ) === true ? $product->get_id() : $product->ID;

			$product_tabs = get_post_meta( $product_id, 'yikes_woo_products_tabs' , true );

			if ( !empty( $product_tabs ) ) {
				$this->tab_data = $product_tabs;
				$i = 25; // setup priorty to loop over, andrender tabs in proper order
				foreach ( $this->tab_data as $tab ) {

					// Do not show tabs with empty titles on the front end
					if ( empty( $tab['title'] ) ) {
						continue;
					}

					$tab_key = $tab['id']; 


					$tabs[$tab_key] = array(
						'title'		=> $tab['title'],
						'priority'	=> $i++,
						'callback'	=> array( $this, 'custom_product_tabs_panel_content' ),
						'content'	=> $tab['content']
					);
				}
				if ( isset( $tabs['reviews'] ) ) {
					$tabs['reviews']['priority'] = $i; // make sure the reviews tab remains on the end (if it is set)
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

			// Hardcoding Site Origin Page Builder conflict fix - remove their the_content filter
			remove_filter( 'the_content', 'siteorigin_panels_filter_content' );

			$use_the_content_filter = apply_filters( 'yikes_woo_use_the_content_filter', true );

			if ( $use_the_content_filter === true ) {
				$content = apply_filters( 'the_content', $tab['content'] );
			} else {
				$content = apply_filters( 'yikes_woo_filter_main_tab_content', $tab['content'] );
			}

			// Hardcoding Site Origin Page Builder conflict fix - re-add their the_content filter
			if ( function_exists( 'siteorigin_panels_filter_content' ) ) add_filter( 'the_content', 'siteorigin_panels_filter_content' );

			echo apply_filters( 'yikes_woocommerce_custom_repeatable_product_tabs_heading', '<h2 class="yikes-custom-woo-tab-title yikes-custom-woo-tab-title-'.sanitize_title($tab['title']).'">' . $tab['title'] . '</h2>', $tab );
			echo apply_filters( 'yikes_woocommerce_custom_repeatable_product_tabs_content', $content, $tab );
		}


		/** Admin methods ******************************************************/

		/**
		* Add a link to the settings page to the plugin's action links
		*
		* @since 1.5
		*
		* @param array | $links | array of links passed from the plugin_action_links_{plugin_name} filter
		*/
		public function add_plugin_action_links( $links ) {
			$href = admin_url( esc_url_raw( 'options-general.php?page=' . $this->settings_page_slug ) );
			$links[] = '<a href="'. $href .'">Settings</a>';
			return $links;
		}

		/**
		 * Adds a new tab to the Product Data postbox in the admin product interface
		 */
		public function render_custom_product_tabs() {
			echo "<li class=\"yikes_wc_product_tabs_tab\"><a href=\"#yikes_woocommerce_custom_product_tabs\">" . __( 'Custom Tabs', 'yikes-inc-easy-custom-woocommerce-product-tabs' ) . "</a></li>";
		}


		/**
		 * Adds the panel to the Product Data postbox in the product interface
		 */
		public function product_page_yikes_custom_tabs_panel() {

			// Require & instantiate our HTML class
			require_once( plugin_dir_path( __FILE__ ) . 'admin/class.yikes-woo-generate-html.php' );
			$HTML = new Yikes_Woo_Custom_Product_Tabs_HTML();

			// Call our function to generate the HTML
			$HTML->generate_html();
		}

		/**
		* Saves the data inputed into the product boxes, as post meta data
		* identified by the name 'yikes_woo_products_tabs'
		*
		* @param int $post_id the post (product) identifier
		* @param stdClass $post the post (product)
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
			$current_tab_id_array = array();
			$post_has_reusable_tabs = false;
			$remove_a_tab_from_reusable = false;

			// Fetch the reusable tab options (we'll use this later)
			$reusable_tab_options_array = get_option( 'yikes_woo_reusable_products_tabs_applied', array() );
			
			$i = 1;
			while( $i <= $number_of_tabs ) {

				// Deal with saving the tab content
			
				$tab_title = stripslashes( $_POST['_yikes_wc_custom_repeatable_product_tabs_tab_title_' . $i] );
				$tab_content = stripslashes( $_POST['_yikes_wc_custom_repeatable_product_tabs_tab_content_' . $i] );
			
				if ( empty( $tab_title ) && empty( $tab_content ) ) {
					
					// clean up if the custom tabs are removed
					unset( $tab_data[$i] );
				
				} elseif ( ! empty( $tab_title ) || ! empty( $tab_content ) ) {
					
					$tab_id = '';
					
					if ( $tab_title ) {
						if ( strlen( $tab_title ) != strlen( utf8_encode( $tab_title ) ) ) {
							// can't have titles with utf8 characters as it breaks the tab-switching javascript
							// so we'll just append an integer
							$tab_id = "tab-custom-" . $i;
						} else {
							// convert the tab title into an id string
							$tab_id = strtolower( $tab_title );
							$tab_id = preg_replace( "/[^\w\s]/", '', $tab_id );
							// remove non-alphas, numbers, underscores or whitespace
							$tab_id = preg_replace( "/_+/", ' ', $tab_id );
							// replace all underscores with single spaces
							$tab_id = preg_replace( "/\s+/", '-', $tab_id );
							// replace all multiple spaces with single dashes
							$tab_id = $tab_id;
						}
					}
					$current_tab_id_array[] = $tab_id;

					// push the data to the array
					$tab_data[$i] = array( 'title' => $tab_title, 'id' => $tab_id, 'content' => $tab_content );
				}	

				// Deal with saving / applying globally saved tabs

				if ( isset ( $_POST['_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' . $i] ) && isset ( $_POST['_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' . $i . '_action'] ) ) {
					
					// Store the tab_id and action
					$reusable_tab_id = $_POST['_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' . $i];
					$reusable_tab_action = $_POST['_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' . $i . '_action'];	

					// If $reusable_tab_options_array is not empty, we've done this before
					if ( ! empty( $reusable_tab_options_array ) ) {

						// Check if this tab has reusable tabs and set flag
						if ( isset ( $reusable_tab_options_array[$post_id] ) ) {
							$post_has_reusable_tabs = true;
						}

						// If action is 'add', add the tab!
						if ( $reusable_tab_action == 'add' ) {

							$reusable_tab_options_array[$post_id][$reusable_tab_id] = array(
								'post_id' => $post_id,
								'reusable_tab_id' => $reusable_tab_id,
								'tab_id' => $tab_id
							);

							// Update our applied tabs array
							update_option( 'yikes_woo_reusable_products_tabs_applied', $reusable_tab_options_array );

						} elseif ( $reusable_tab_action == 'remove' ) {

							// This tab will no longer be affected by the global/reusable tab changes
							unset( $reusable_tab_options_array[$post_id][$reusable_tab_id] );

							// Update our applied tabs array
							update_option( 'yikes_woo_reusable_products_tabs_applied', $reusable_tab_options_array );
						}
					} elseif ( $reusable_tab_action == 'add' ) {

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
				}

				$i++;
			}

			// Let's check our $current_tab_id_array and see if we need to remove any reusable tabs
			if ( $post_has_reusable_tabs === true ) {

				// Loop through our reusable tab array	
				foreach( $reusable_tab_options_array[$post_id] as $id => $reusable_tab_array ) {

					// If we find one of our reusable tabs is no longer part of this post, remove it
					if ( ! in_array( $reusable_tab_array['tab_id'], $current_tab_id_array ) ) {
						unset( $reusable_tab_options_array[$post_id][$id] );
						$remove_a_tab_from_reusable = true;
					}
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
			}
		}

		/* Helper methods */

		/**
		 * Lazy-load the product_tabs meta data, and return true if it exists,
		 * false otherwise
		 *
		 * @return true if there is custom tab data, false otherwise
		 */
		private function product_has_custom_tabs( $product ) {
			if ( false === $this->tab_data ) {
				$product_id = method_exists( $product, 'get_id' ) === true ? $product->get_id() : $product->ID;

				$this->tab_data = maybe_unserialize( get_post_meta( $product_id, 'yikes_woo_products_tabs', true ) );
			}
			// tab must at least have a title to exist
			return ! empty( $this->tab_data ) && ! empty( $this->tab_data[0] ) && ! empty( $this->tab_data[0]['title'] );
		}


		/**
		 * Checks if WooCommerce is active
		 *
		 * @since  1.0
		 * @return bool true if WooCommerce is active, false otherwise
		 */
		public static function is_woocommerce_active() {

			$active_plugins = (array) get_option( 'active_plugins', array() );

			if ( is_multisite() ) {
				$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
			}

			return in_array( 'woocommerce/woocommerce.php', $active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins );
		}


		/* Lifecycle methods */

		/**
		 * Run every time.  
		 * Used since the activation hook is not executed when updating a plugin
		 */
		private function install() {

			global $wpdb;

			$installed_version = get_option( self::VERSION_OPTION_NAME );

			// installed version lower than plugin version?
			if ( -1 === version_compare( $installed_version, self::VERSION ) ) {
				// new version number
				update_option( self::VERSION_OPTION_NAME, self::VERSION );
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
			$textarea_id = filter_var( $_POST['textarea_id'], FILTER_SANITIZE_STRING );

			// Check if we have tab content
			$tab_content = isset( $_POST['tab_content'] ) ? $_POST['tab_content'] : '';

			// Set up options
			$wp_editor_options = array( 
				'textarea_name' => $textarea_id,
				'textarea_rows' => 8
			);

			// Return wp_editor HTML
			wp_editor( stripslashes( $tab_content ), $textarea_id, $wp_editor_options );
			exit;
		}

		/**
		* [AJAX] Save a tab as reusable
		*
		* @since 1.5
		*
		* @param  string $_POST['tab_title'] 	Tab title to save
		* @param  string $_POST['tab_content']	Tab content to save
		* @param  string $_POST['tab_id'] 		Optional. Tab ID we're updating
		* @return object success || failure
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
				$tab_title = stripslashes( $_POST['tab_title'] );

				$tab_string_id = strtolower( $tab_title );
				$tab_string_id = preg_replace( "/[^\w\s]/", '', $tab_string_id );
				// remove non-alphas, numbers, underscores or whitespace
				$tab_string_id = preg_replace( "/_+/", ' ', $tab_string_id );
				// replace all underscores with single spaces
				$tab_string_id = preg_replace( "/\s+/", '-', $tab_string_id );
			} else {
				wp_send_json_error( array( 'reason' => 'no tab title', 'message' => 'Please fill out the tab title before saving.' ) );
			}

			if ( isset( $_POST['tab_content'] ) && ! empty( $_POST['tab_content'] ) ) {
				$tab_content = $_POST['tab_content'];
			}

			if ( isset( $_POST['tab_id'] ) && ! empty( $_POST['tab_id'] ) ) {
				$tab_id = $_POST['tab_id'];
			}

			// Get our saved tabs array
			$yikes_custom_tab_data = get_option( 'yikes_woo_reusable_products_tabs', array() );

			// If the saved tabs array is empty, create a new array and save it (first time we've done this)
			if ( empty( $yikes_custom_tab_data ) ) {
				$yikes_custom_tab_options_array = array();

				$yikes_custom_tab_options_array[1] = array(
					'tab_title' => $tab_title,
					'tab_content' => $tab_content,
					'tab_id' => 1
				);

				update_option( 'yikes_woo_reusable_products_tabs', $yikes_custom_tab_options_array );

				// Return redirect URL
				$return_redirect_url = admin_url( add_query_arg( array( 'saved-tab-id' => 1 ), esc_url_raw( 'options-general.php?page=' . $this->settings_page_slug ) ) );

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
				
					$yikes_custom_tab_data[$new_tab_id] = array(
						'tab_title' => $tab_title,
						'tab_content' => $tab_content,
						'tab_id' => $new_tab_id
					);

					update_option( 'yikes_woo_reusable_products_tabs', $yikes_custom_tab_data );

					// Return redirect URL
					$return_redirect_url = admin_url( add_query_arg( array( 'saved-tab-id' => $new_tab_id ), esc_url_raw( 'options-general.php?page=' . $this->settings_page_slug ) ) );

					// Send response
					wp_send_json_success( array( 'tab_id' => $new_tab_id, 'redirect' => true, 'redirect_url' => $return_redirect_url ) );

				} else {

					// This is an existing tab, so just update it
					$yikes_custom_tab_data[$tab_id] = array(
						'tab_title' => $tab_title,
						'tab_content' => $tab_content,
						'tab_id' => $tab_id
					);

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
						$custom_tab_data = get_post_meta( $post_id, 'yikes_woo_products_tabs', true );

						// If we don't have custom tab data then continue
						if ( empty( $custom_tab_data ) ) {
							continue;
						}

						// Loop through $custom_tab_data and find the custom tab that was just updated
						foreach( $custom_tab_data as $index => $tab ) {
							if ( $tab['id'] === $reusable_tab_data[$tab_id]['tab_id'] ) {
								$custom_tab_data[$index]['title'] = $tab_title;
								$custom_tab_data[$index]['content'] = $tab_content;
								$custom_tab_data[$index]['id'] = $tab_string_id;

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

						// If we updated the tab_string_id in the yikes_woo_reusable_products_tabs_applied array, save it
						if ( $update_applied_products_array === true ) {
							update_option( 'yikes_woo_reusable_products_tabs_applied', $reusable_tab_options_array);
						}
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

			$tab['tab_content'] = stripslashes( $tab['tab_content'] );

			if ( ! empty( $saved_tabs ) ) {
				wp_send_json_success( $tab );	
			} else {
				wp_send_json_success( array( 'message' => 'Could not find the tab. Pleae try again.' ) );
			}

			// If we get this far, send error
			wp_send_json_error( array( 'message' => 'Uh oh. Something went wrong.' ) );
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
			wp_send_json_failure();
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
				wp_send_json_failure( array( 'message' => 'Could not find the product!' ) );
			}

			// Save our tabs!
			$success = $this->save_tabs( $post_id, $is_ajax = true );

			if ( $success === true ) {
				wp_send_json_success( array( 'message' => 'Your tabs have been saved' ) );
			} else {
				wp_send_json_failure( array( 'message' => 'Uh oh! Something went wrong with saving. Please try again.' ) );
			}
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
						$post_tabs = get_post_meta( $post_id, 'yikes_woo_products_tabs', true );

						// Make sure it exists
						if ( ! empty( $post_tabs ) ) {
							foreach ( $post_tabs as $index => $post_tab ) {
								if ( $applied_tabs[$post_id][$tab_id]['tab_id'] === $post_tab['id'] ) {
									unset( $post_tabs[$index] );
									update_post_meta( $post_id, 'yikes_woo_products_tabs', $post_tabs );
								}
							}
						}

						unset( $applied_tabs[$post_id][$tab_id] );
						$unset_applied_tabs_flag = true;
					}
				}

				// If we unset an applied tab, update the database
				if ( $unset_applied_tabs_flag === true ) {
					update_option( 'yikes_woo_reusable_products_tabs_applied', $applied_tabs );
				}
			}

			// If we're on the single tab edit screen, we want to redirect back to tab list so let's return a var
			$return_redirect_url = admin_url( add_query_arg( array( 'delete-success' => true ), esc_url_raw( 'options-general.php?page=' . $this->settings_page_slug ) ) );

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
			add_submenu_page(	
				'options-general.php', 																							// Parent slug
				__( 'Settings - Custom Product Tabs for WooCommerce', 'yikes-inc-easy-custom-woocommerce-product-tabs' ), 		// Tab title name (HTML title)
				__( 'Custom Product Tabs for WooCommerce', 'yikes-inc-easy-custom-woocommerce-product-tabs' ),					// Menu page name
				apply_filters( 'yikes_simple_taxonomy_ordering_capabilities', 'manage_options' ), 								// Capability required
				$this->settings_page_slug, 																						// Page slug (?page=slug-name)
				array( $this, 'generate_yikes_settings_page' )																	// Function to generate page
			);
		}

		/**
		* Include our settings page
		*
		* @since 1.5
		*
		*/
		public function generate_yikes_settings_page() {

			// Get our settings page slug for redirect purposes
			$settings_page_slug = $this->settings_page_slug;

			// Get our saved tabs array
			$yikes_custom_tab_data = get_option( 'yikes_woo_reusable_products_tabs', array() );

			// New tab URL - used to supply the 'Add Tab' href
			$new_tab_url = add_query_arg( array( 'saved-tab-id' => 'new' ), esc_url_raw( 'options-general.php?page=' . $settings_page_slug ) );

			// If saved_tab_id is set, we should show the single saved tab // add new tab page
			if ( isset( $_GET['saved-tab-id'] ) && ! empty( $_GET['saved-tab-id'] ) ) {

				// Are we trying to add a new tab?
				$new_tab = ( $_GET['saved-tab-id'] === 'new' ) ? true : false;

				// Sanitize saved_tab_id
				$saved_tab_id = filter_var( $_GET['saved-tab-id'], FILTER_SANITIZE_NUMBER_INT );

				// Get the tab
				$tab = isset( $yikes_custom_tab_data[$saved_tab_id] ) ? $yikes_custom_tab_data[$saved_tab_id] : array();

				// Get all the products using this tab
				$products = $this->fetch_all_products_using_current_tab( $saved_tab_id );

				// Redirect URL
				$redirect = admin_url( esc_url_raw( 'options-general.php?page=' . $settings_page_slug ) );

				require_once( plugin_dir_path(__FILE__) . 'admin/page.yikes-woo-saved-tabs-single.php' );

			} else {
				$delete_message_display = 'display: none;';

				// Check if our $_GET variable 'delete-success' is set so we can display a nice success message
				if ( isset( $_GET['delete-success'] ) && $_GET['delete-success'] === '1' ) {
					$delete_message_display = '';
				}

				// Show tab table list
				require_once( plugin_dir_path(__FILE__) . 'admin/page.yikes-woo-saved-tabs.php' );
			}
		}

		/**
		* Fetch all product ids that are using the saved tab
		*
		* @since 1.5
		* 
		* @param  int 	 | $tab_id 			  | unique identifier of a tab
		* @return array  | $product_ids_array | array of product ids
		*/
		protected function fetch_all_products_using_current_tab( $tab_id ) {

			// Set up our return array
			$product_ids_array = array();

			// Get all of the product IDs from the DB
			$applied_tabs = get_option( 'yikes_woo_reusable_products_tabs_applied', array() );

			// If the option returns an empty array, C YA
			if ( empty( $applied_tabs ) ) {
				return $product_ids_array;
			}

			// Loop through all of our applied tabs and get the product IDs
			foreach( $applied_tabs as $product_id => $saved_tabs_array ) {

				// This means the product is using this saved tab
				if ( isset( $saved_tabs_array[$tab_id] ) ) {
					$product_ids_array[] = $product_id;
				}
			}

			return $product_ids_array;
		}

		/* End Plugin Settings Page */

		/* i18n */

		/**
		*	Register the textdomain for proper i18n / l10n
		*	@since 1.5
		*/
		public function yikes_woo_load_plugin_textdomain() {
			load_plugin_textdomain(
				'yikes-inc-easy-custom-woocommerce-product-tabs',
				false,
				plugin_dir_path(__FILE__) . 'languages/'
			);
		}

		/* End i18n */


		/* Misc. */

		/**
		* Default the wp_editor to 'Visual' tab (this helps prevent errors with dynamically generating WYSIWYG)
		*
		* @since 1.5
		*
		* @param  string | $mode | The current mode of the editor
		* @return string 'tinymce' || $mode
		*/
		public function yikes_woo_set_editor_to_visual( $mode ) {
			global $post;

			// Only continue if we're on the products page
			if ( isset( $post ) && isset( $post->post_type ) && $post->post_type !== 'product' ) {
				return $mode;
			}

			// This is funky, but only default the editor when we don't have a post (and we're on the product page)
			// This a result of calling the wp_editor via AJAX - I think
			if ( ! isset( $post ) ) {
				return apply_filters( 'yikes_woocommerce_default_editor_mode', 'tinymce' );
			} else {
				return $mode;
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

		/* End Misc. */
	}
