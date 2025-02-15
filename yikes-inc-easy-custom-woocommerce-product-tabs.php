<?php
/**
 * Plugin Name: Custom Product Tabs for WooCommerce
 * Plugin URI: https://www.codeparrots.com
 * Description: Extend WooCommerce to add and manage custom product tabs. Create as many product tabs as needed per product.
 * Author: Code Parrots
 * Author URI: https://www.codeparrots.com
 * Version: 1.8.6
 * Text Domain: yikes-inc-easy-custom-woocommerce-product-tabs
 * Domain Path: languages/
 * Tested up to: 6.4
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 8.6
 *
 * Copyright: (c) 2014-2024 Code Parrots
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * This plugin is originally a fork of SkyVerge WooCommerce Custom Product Tabs Lite.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Must include plugin.php to use is_plugin_active().
require_once ABSPATH . 'wp-admin/includes/plugin.php';

if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	// Enable WooCommerce HPOS compatibility.
	add_action( 'before_woocommerce_init', function() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	} );
	new YIKES_Custom_Product_Tabs();
} else {
	// Deactivate the plugin, and display our error notification.
	deactivate_plugins( '/yikes-inc-easy-custom-woocommerce-product-tabs/yikes-inc-easy-custom-woocommerce-product-tabs.php' );
	add_action( 'admin_notices', 'yikes_woo_display_admin_notice_error' );
}

/**
 * Display our error admin notice if WooCommerce is not installed + active.
 */
function yikes_woo_display_admin_notice_error() {
	?>
		<!-- hide the 'Plugin Activated' default message -->
		<style>
		#message.updated {
			display: none;
		}
		</style>
		<!-- display our error message -->
		<div class="error">
			<p>
				<?php
				$message = sprintf(
					/* translators: The placeholder is a URL to the WooCommerce plugin. */
					__( 'Please install and activate %1s before activating Custom WooCommerce Product Tabs.', 'yikes-inc-easy-custom-woocommerce-product-tabs' ),
					'<a href="' . esc_url( admin_url( 'plugin-install.php?tab=search&type=term&s=WooCommerce' ) ) . '" title="WooCommerce">WooCommerce</a>'
				);

				if ( file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) ) {
					$activate_url = add_query_arg(
						array(
							'action'   => 'activate',
							'plugin'   => 'woocommerce/woocommerce.php',
							'_wpnonce' => wp_create_nonce( 'activate-plugin_woocommerce/woocommerce.php' )
						),
						admin_url( 'plugins.php' )
					);
					$message = sprintf(
						/* translators: The placeholder is a URL to the WooCommerce plugin. */
						__( 'Please activate %1s before activating Custom WooCommerce Product Tabs.', 'yikes-inc-easy-custom-woocommerce-product-tabs' ),
						'<a href="' . esc_url( $activate_url ) . '" title="WooCommerce">WooCommerce</a>'
					);
				}
				echo $message;
				?>
			</p>
		</div>
	<?php
}

/**
 * Initialize the Custom Product Tab Class.
 */
class YIKES_Custom_Product_Tabs {

	/**
	 * Define hooks/require files.
	 */
	public function __construct() {

		$this->define_constants();

		add_action( 'admin_init', array( $this, 'run_update_check' ) );

		// Require our classes.
		require_once YIKES_Custom_Product_Tabs_Path . 'admin/helper.functions.php';
		require_once YIKES_Custom_Product_Tabs_Path . 'admin/class.yikes-woo-saved-tabs.php';
		require_once YIKES_Custom_Product_Tabs_Path . 'admin/class.yikes-woo-tabs.php';
		require_once YIKES_Custom_Product_Tabs_Path . 'admin/class.support.php';
		require_once YIKES_Custom_Product_Tabs_Path . 'public/class.yikes-woo-tabs-display.php';
		require_once YIKES_Custom_Product_Tabs_Path . 'admin/class.settings.php';
		require_once YIKES_Custom_Product_Tabs_Path . 'admin/class.premium.php';
		require_once YIKES_Custom_Product_Tabs_Path . 'admin/class.export.php';
		require_once YIKES_Custom_Product_Tabs_Path . 'admin/class.import.php';

		add_action( 'admin_init', array( $this, 'init' ) );
	}

	/**
	 * Define our constants.
	 */
	private function define_constants() {

		/**
		 * Define the page slug for our plugin's custom settings page in one central location.
		 */
		if ( ! defined( 'YIKES_Custom_Product_Tabs_Settings_Page' ) ) {
			define( 'YIKES_Custom_Product_Tabs_Settings_Page', 'yikes-woo-settings' );
		}

		/**
		 * Define the plugin's version.
		 */
		if ( ! defined( 'YIKES_Custom_Product_Tabs_Version' ) ) {
			define( 'YIKES_Custom_Product_Tabs_Version', '1.8.5' );
		}

		/**
		 * Define the bundled lity.js version
		 */
		if ( ! defined( 'YIKES_Custom_Product_LITY_Version' ) ) {
			define( 'YIKES_Custom_Product_LITY_Version', '2.4.1' );
		}

		/**
		 * Define the plugin's URI.
		 */
		if ( ! defined( 'YIKES_Custom_Product_Tabs_URI' ) ) {
			define( 'YIKES_Custom_Product_Tabs_URI', plugin_dir_url( __FILE__ ) );
		}

		/**
		 * Define the plugin's path.
		 */
		if ( ! defined( 'YIKES_Custom_Product_Tabs_Path' ) ) {
			define( 'YIKES_Custom_Product_Tabs_Path', plugin_dir_path( __FILE__ ) );
		}

		/**
		 * Define the page slug for our plugin's support page.
		 */
		if ( ! defined( 'YIKES_Custom_Product_Tabs_Support_Page' ) ) {
			define( 'YIKES_Custom_Product_Tabs_Support_Page', 'yikes-woo-support' );
		}

		/**
		 * Define the page slug for our plugin's premium page.
		 */
		if ( ! defined( 'YIKES_Custom_Product_Tabs_Premium_Page' ) ) {
			define( 'YIKES_Custom_Product_Tabs_Premium_Page', 'yikes-woo-premium' );
		}
	}

	/**
	 * Run any update scripts.
	 */
	public function run_update_check() {

		$run_onesixone_data_update = get_option( 'custom_product_tabs_onesixone_data_update' );

		// If we don't have a value for this option then run our update again.
		if ( empty( $run_onesixone_data_update ) ) {
			$this->run_onesixone_data_update();
		}

	}

	/**
	 * Run the v1.6 update. This changes the tabs' slug and adds some default elements to our tab items.
	 */
	private function run_onesixone_data_update() {

		// Update Saved Tabs.
		$saved_tabs = get_option( 'yikes_woo_reusable_products_tabs' );

		if ( ! empty( $saved_tabs ) ) {

			foreach ( $saved_tabs as $tab_id => &$tab ) {

				// Set the tab slug to the sanitized tab's title.
				$tab['tab_slug'] = urldecode( sanitize_title( $tab['tab_title'] ) );

				// Default these elements.
				$tab['taxonomies'] = ! isset( $tab['taxonomies'] ) ? array() : $tab['taxonomies'];
				$tab['global_tab'] = ! isset( $tab['global_tab'] ) ? false : $tab['global_tab'];
				$tab['tab_name']   = ! isset( $tab['tab_name'] ) ? '' : $tab['tab_name'];
			}

			update_option( 'yikes_woo_reusable_products_tabs', $saved_tabs );

		}

		// Update Saved Tabs Applied.
		$saved_tabs_applied = get_option( 'yikes_woo_reusable_products_tabs_applied' );

		if ( ! empty( $saved_tabs_applied ) ) {

			foreach ( $saved_tabs_applied as $product_id => &$tabs ) {

				if ( ! empty( $tabs ) ) {

					foreach ( $tabs as $saved_tab_id => &$tab ) {

						if ( ! empty( $tab ) ) {

							if ( isset( $saved_tabs[ $saved_tab_id ] ) ) {

								// Set the tab ID to the saved tab's slug.
								$tab_id        = $saved_tabs[ $saved_tab_id ]['tab_slug'];
								$tab['tab_id'] = $tab_id;
							}
						} else {

							// In previous versions of the plugin we were leaving some empty arrays. Clean 'em up.
							unset( $tab );
						}
					}
				} else {

					// In previous versions of the plugin we were leaving some empty arrays. Clean 'em up.
					unset( $saved_tabs_applied[ $product_id ] );
				}
			}

			update_option( 'yikes_woo_reusable_products_tabs_applied', $saved_tabs_applied );
		}

		// Update Post Meta.
		global $wpdb;

		// Fetch all of the post meta items where meta_key = 'yikes_woo_products_tabs'.
		$yikes_woo_products_tabs = $wpdb->get_results(
			"
			SELECT *
			FROM {$wpdb->postmeta}
			WHERE meta_key = 'yikes_woo_products_tabs'
			"
		);

		if ( ! empty( $yikes_woo_products_tabs ) ) {

			foreach ( $yikes_woo_products_tabs as $table_row ) {

				// Unserialize our tabs.
				$tabs = yikes_custom_tabs_maybe_unserialize( $table_row->meta_value );

				// If we have tabs...
				if ( ! empty( $tabs ) ) {

					foreach ( $tabs as &$tab ) {

						// Set the tab slug ('id') to the sanitized tab's title.
						$tab['id'] = urldecode( sanitize_title( $tab['title'] ) );
					}

					update_post_meta( $table_row->post_id, 'yikes_woo_products_tabs', $tabs );

				} else {

					// In previous versions of the plugin we were leaving some empty arrays. Clean 'em up.
					delete_post_meta( $table_row->post_id, 'yikes_woo_products_tabs' );
				}
			}
		}

		// Set a flag so we don't run this update more than once.
		add_option( 'custom_product_tabs_onesixone_data_update', true );
	}


	/**
	 * Run our basic plugin setup.
	 */
	public function init() {

		// Default WYSIWYG to 'visual'.
		add_filter( 'wp_default_editor', array( $this, 'yikes_woo_set_editor_to_visual' ), 10, 1 );

		// i18n.
		add_action( 'plugins_loaded', array( $this, 'yikes_woo_load_plugin_textdomain' ) );

		// Add settings link to plugin on plugins page.
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_plugin_action_links' ), 10, 1 );

		add_filter( 'admin_footer_text', array( $this, 'yikes_custom_product_tabs_admin_footer_text' ) );
	}

	/**
	 * Add a link to the settings page to the plugin's action links
	 *
	 * @since 1.5
	 *
	 * @param array $links An array of links passed from the plugin_action_links_{plugin_name} filter.
	 *
	 * @return array $links The $links array, with our saved tabs page appended.
	 */
	public function add_plugin_action_links( $links ) {
		$href     = add_query_arg( array( 'page' => YIKES_Custom_Product_Tabs_Settings_Page ), admin_url( 'admin.php' ) );
		$docs_url = 'https://yikesplugins.com/article-category/custom-product-tabs-for-woocommerce/';

		$links[] = '<a href="' . esc_url_raw( $href ) . '">' . esc_html__( 'Saved Tabs', 'yikes-inc-easy-custom-woocommerce-product-tabs' ) . '</a>';
		$links[] = '<a href="' . esc_url_raw( $docs_url ) . '" target="_blank">' . esc_html__( 'Documentation', 'yikes-inc-easy-custom-woocommerce-product-tabs' ) . '</a>';

		return $links;
	}

	/**
	 * Admin footer text on Yikes pages.
	 *
	 * @param string $footer_text Admin footer text.
	 *
	 * @return string Filtered admin footer text.
	 */
	public function yikes_custom_product_tabs_admin_footer_text( $footer_text ) {

		$page = filter_input( INPUT_GET, 'page', FILTER_UNSAFE_RAW );

		if ( ! $page ) {

			return $footer_text;

		}

		$page = htmlspecialchars( $page );

		$allowed_pages = array(
			'yikes-woo-settings',
			'yikes-woo-support',
			'yikes-woo-premium',
			'cptpro-settings',
		);

		if ( ! in_array( $page, $allowed_pages, true ) ) {

			return $footer_text;

		}

		$stars = '<a href="https://wordpress.org/support/plugin/yikes-inc-easy-custom-woocommerce-product-tabs/reviews/" alt="Custom Product Tabs for WooCommerce | WordPress.org" target="_blank" style="color: #daa520;"><span class="dashicons dashicons-star-filled yikes-star"></span><span class="dashicons dashicons-star-filled yikes-star"></span><span class="dashicons dashicons-star-filled yikes-star"></span><span class="dashicons dashicons-star-filled yikes-star"></span><span class="dashicons dashicons-star-filled yikes-star"></span></a>';

		$footer_text = sprintf(
			__( 'Thank you for using Custom Product Tabs for WooCommerce. Please consider leaving us %s on the WordPress.org plugin repository.', 'yikes-inc-easy-custom-woocommerce-product-tabs' ),
			$stars
		);

		return wp_kses_post( $footer_text );

	}

	/**
	 * Register the textdomain for proper i18n / l10n.
	 */
	public function yikes_woo_load_plugin_textdomain() {
		load_plugin_textdomain(
			'yikes-inc-easy-custom-woocommerce-product-tabs',
			false,
			YIKES_Custom_Product_Tabs_Path . 'languages/'
		);
	}

	/**
	 * Default the wp_editor to 'Visual' tab (this helps prevent errors with dynamically generating WYSIWYG)
	 *
	 * @since 1.5
	 *
	 * @param string $mode The current mode of the editor.
	 *
	 * @return string 'tinymce' || $mode
	 */
	public function yikes_woo_set_editor_to_visual( $mode ) {
		global $post;

		// Only continue if we're on the products page.
		if ( isset( $post ) && isset( $post->post_type ) && $post->post_type !== 'product' ) {
			return $mode;
		}

		// This is funky, but only default the editor when we don't have a post (and we're on the product page).
		// This a result of calling the wp_editor via AJAX - I think.
		if ( ! isset( $post ) ) {
			return apply_filters( 'yikes_woocommerce_default_editor_mode', 'tinymce' );
		} else {
			return $mode;
		}
	}
}
