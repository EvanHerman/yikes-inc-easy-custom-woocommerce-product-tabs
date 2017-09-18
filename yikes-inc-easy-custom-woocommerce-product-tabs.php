<?php
/**
 * Plugin Name: Custom Product Tabs for WooCommerce 
 * Plugin URI: http://www.yikesinc.com
 * Description: Extend WooCommerce to add and manage custom product tabs. Create as many product tabs as needed per product.
 * Author: YIKES, Inc
 * Author URI: http://www.yikesinc.com
 * Version: 1.5.17
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
	
	// Must include plugin.php to use is_plugin_active()
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

		new YIKES_Custom_Product_Tabs();

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
	
	/**
	* Initialize the Custom Product Tab Class
	*/
	class YIKES_Custom_Product_Tabs {

		/**
		* Gets things started by adding an action to initialize this plugin once
		* WooCommerce is known to be active and initialized
		*/
		public function __construct() {

			$this->define_constants();

			// Require our classes
			require_once YIKES_Custom_Product_Tabs_Path . 'admin/class.yikes-woo-export.php';
			require_once YIKES_Custom_Product_Tabs_Path . 'admin/class.yikes-woo-saved-tabs.php';
			require_once YIKES_Custom_Product_Tabs_Path . 'admin/class.yikes-woo-tabs.php';
			require_once YIKES_Custom_Product_Tabs_Path . 'public/class.yikes-woo-tabs-display.php';

			add_action( 'woocommerce_init', array( $this, 'init' ) );
		}

		/**
		* Define our constants
		*/
		private function define_constants() {

			/**
			* Define the text domain
			*/
			if ( ! defined( 'YIKES_Custom_Product_Tabs_Text_Domain' ) ) {
				define( 'YIKES_Custom_Product_Tabs_Text_Domain', 'yikes-inc-easy-custom-woocommerce-product-tabs' );
			}

			/**
			* Define the page slug for our plugin's custom settings page in one central location
			*/
			if ( ! defined( 'YIKES_Custom_Product_Tabs_Settings_Page' ) ) {
				define( 'YIKES_Custom_Product_Tabs_Settings_Page', 'yikes-woo-settings' );
			}

			/**
			* Define the plugin's version
			*/
			if ( ! defined( 'YIKES_Custom_Product_Tabs_Version' ) ) {
				define( 'YIKES_Custom_Product_Tabs_Version', '1.5.17' );
			}

			/**
			* Define the plugin's URI
			*/
			if ( ! defined( 'YIKES_Custom_Product_Tabs_URI' ) ) {
				define( 'YIKES_Custom_Product_Tabs_URI', plugin_dir_url( __FILE__ ) );
			}

			/**
			* Define the plugin's path
			*/
			if ( ! defined( 'YIKES_Custom_Product_Tabs_Path' ) ) {
				define( 'YIKES_Custom_Product_Tabs_Path', plugin_dir_path( __FILE__ ) );
			}
		}

		/**
		 * Init WooCommerce Product Tabs Lite extension once we know WooCommerce is active
		 */
		public function init() {

			// Default WYSIWYG to 'visual'
			add_filter( 'wp_default_editor', array( $this, 'yikes_woo_set_editor_to_visual' ), 10, 1 );

			// i18n
			add_action( 'plugins_loaded', array( $this, 'yikes_woo_load_plugin_textdomain' ) );

			// Add settings link to plugin on plugins page
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_plugin_action_links' ), 10, 1 );
		}

		/**
		* Add a link to the settings page to the plugin's action links
		*
		* @since 1.5
		*
		* @param array | $links | array of links passed from the plugin_action_links_{plugin_name} filter
		*/
		public function add_plugin_action_links( $links ) {
			$href = admin_url( esc_url_raw( 'options-general.php?page=' . YIKES_Custom_Product_Tabs_Settings_Page ) );
			$links[] = '<a href="'. $href .'">Settings</a>';
			return $links;
		}

		
		/* i18n */

		/**
		*	Register the textdomain for proper i18n / l10n
		*	@since 1.5
		*/
		public function yikes_woo_load_plugin_textdomain() {
			load_plugin_textdomain(
				YIKES_Custom_Product_Tabs_Text_Domain,
				false,
				YIKES_Custom_Product_Tabs_Path . 'languages/'
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

		/* End Misc. */
	}
