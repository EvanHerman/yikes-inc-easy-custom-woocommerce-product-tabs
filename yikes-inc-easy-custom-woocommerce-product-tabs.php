<?php
/**
 * Plugin Name: Easy Custom WooCommerce Product Tabs
 * Plugin URI: http://www.yikesinc.com
 * Description: Extend WooCommerce to add and manage custom product tabs. Create as many product tabs as needed per product.
 * Author: YIKES Inc
 * Author URI: http://www.yikesinc.com
 * Version: 1.0
 * Tested up to: 4.1
 * Text Domain: yikes-inc-woocommerce-custom-product-tabs
 * Domain Path: /i18n/languages/
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
				<p><?php _e( 'Easy Custom WooCommerce Product Tabs could not be activated because WooCommerce is not installed and active.', 'yikes-inc-woocommerce-custom-product-tabs' ); ?></p>
				<p><?php _e( 'Please install and activate ', 'yikes-inc-woocommerce-custom-product-tabs' ); ?><a href="https://wordpress.org/plugins/woocommerce/" title="WooCommerce">WooCommerce</a><?php _e( ' before activating the plugin.', 'yikes-inc-woocommerce-custom-product-tabs' ); ?></p>
			</div>
		<?php
	}
	
	/**
	* Initialize the Custom Product Tab Class
	*/
	class YikesWooCommerceCustomProductTabs {

		private $tab_data = false;

		/** plugin version number */
		const VERSION = "1.0";

		/** plugin text domain */
		const TEXT_DOMAIN = 'yikes-inc-woocommerce-custom-product-tabs';

		/** plugin version name */
		const VERSION_OPTION_NAME = 'yikes_woocommerce_custom_product_tabs_db_version';

		/**
		* Gets things started by adding an action to initialize this plugin once
		* WooCommerce is known to be active and initialized
		*/
		public function __construct() {
			// Installation
			if ( is_admin() && ! defined( 'DOING_AJAX' ) ) $this->install();

			add_action( 'init',             array( $this, 'load_translation' ) );
			add_action( 'woocommerce_init', array( $this, 'init' ) );
		}

		
		/**
		 * Init WooCommerce PDF Product Vouchers when WordPress initializes
		 *
		 * @since 1.2.5
		 */
		public function load_translation() {
			// localization
			load_plugin_textdomain( 'yikes-inc-woocommerce-custom-product-tabs', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages' );
		}

		
		/**
		 * Init WooCommerce Product Tabs Lite extension once we know WooCommerce is active
		 */
		public function init() {
			// backend stuff
			add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'render_custom_product_tabs' ) );
			add_action( 'woocommerce_product_write_panels',     array( $this, 'product_page_yikes_custom_tabs_panel' ) );
			add_action( 'woocommerce_process_product_meta',     array( $this, 'product_save_data' ), 10, 2 );

			// frontend stuff
			add_filter( 'woocommerce_product_tabs', array( $this, 'add_custom_product_tabs' ) );

			// allow the use of shortcodes within the tab content
			add_filter( 'yikes_woocommerce_custom_repeatable_product_tabs_content', 'do_shortcode' );
			
			// enqueue our custom js file, for repeatable tabs
			add_action( 'admin_enqueue_scripts' , array( &$this , 'enqueue_repeatable_tab_script' ) );
						
		}
				
				
		/** Frontend methods ******************************************************/
		public function enqueue_repeatable_tab_script( $hook ) {
			global $post;
			if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
				if ( $post->post_type == 'product' ) {
					// script
					wp_register_script( 'repeatable-custom-tabs' , plugin_dir_url(__FILE__) . 'js/repeatable-custom-tabs.min.js' , array('jquery') , 'all' );
					wp_enqueue_script( 'repeatable-custom-tabs' );
					// styles + font
					wp_register_style( 'repeatable-custom-tabs-styles' , plugin_dir_url(__FILE__) . 'css/repeatable-custom-tabs.min.css' , '' , 'all' );
					wp_enqueue_style( 'repeatable-custom-tabs-styles' );
					// font
					// wp_register_style( 'yikes-woo-tabs-font' , plugin_dir_url(__FILE__) . 'css/yikes-woo-tabs-font.css' , '' , 'all' );
					// wp_enqueue_style( 'yikes-woo-tabs-font' );
				}
			}
		}

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
		 * @since 1.2.0
		 * @param array $tabs array representing the product tabs
		 * @return array representing the product tabs
		 */
		public function add_custom_product_tabs( $tabs ) {
			global $product;
			if ( get_post_meta( $product->id , 'yikes_woo_products_tabs' , true ) ) {
				$this->tab_data = get_post_meta( $product->id , 'yikes_woo_products_tabs' , true );
				$i = 25; // setup priorty to loop over, andrender tabs in proper order
				foreach ( $this->tab_data as $tab ) {
					$tabs[ $tab['id'] ] = array(
						'title'    => __( $tab['title'], self::TEXT_DOMAIN ),
						'priority' => $i++,
						'callback' => array( $this, 'custom_product_tabs_panel_content' ),
						'content'  => $tab['content'],  // custom field
					);
				}
				if ( isset( $tabs['reviews'] ) ) {
					$tabs['reviews']['priority'] = $i; // make sure the reviews tab remains on the end (if it is set)
				}
			}
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

			// allow shortcodes to function
			$content = apply_filters( 'the_content', $tab['content'] );
			$content = str_replace( ']]>', ']]&gt;', $content );

			echo apply_filters( 'yikes_woocommerce_custom_repeatable_product_tabs_heading', '<h2>' . $tab['title'] . '</h2>', $tab );
			echo apply_filters( 'yikes_woocommerce_custom_repeatable_product_tabs_content', $content, $tab );
		}


		/** Admin methods ******************************************************/

		/**
		 * Adds a new tab to the Product Data postbox in the admin product interface
		 */
		public function render_custom_product_tabs() {
			echo "<li class=\"yikes_wc_product_tabs_tab\"><a href=\"#yikes_woocommerce_custom_product_tabs\">" . __( 'Custom Tabs', self::TEXT_DOMAIN ) . "</a></li>";
		}


		/**
		 * Adds the panel to the Product Data postbox in the product interface
		 */
		public function product_page_yikes_custom_tabs_panel() {
			global $post;
			// the product

			if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '2.1', '<' ) ) {
				?>
				<style type="text/css">
					#woocommerce-product-data ul.product_data_tabs li.yikes_wc_product_tabs_tab a { padding:5px 5px 5px 28px;background-repeat:no-repeat;background-position:5px 7px; }
				</style>
				<?php
			}

			// pull the custom tab data out of the database
			$tab_data = maybe_unserialize( get_post_meta( $post->ID, 'yikes_woo_products_tabs', true ) );
					
			if ( empty( $tab_data ) ) {
				$tab_data['1'] = array( 'title' => '', 'content' => '' , 'duplicate' => '' );
			}
					
			$i = 1;
			// display the custom tab panel
				echo '<div id="yikes_woocommerce_custom_product_tabs" class="panel wc-metaboxes-wrapper woocommerce_options_panel">';		
				
					echo $this->review_us_container();
					
					echo '<div class="yikes-woo-tabs-hidden-how-to-info"><h3 style="padding-top:0;padding-bottom:0;">' . __( "How To" , self::TEXT_DOMAIN ) . ':</h3><p style="margin:0;padding-left:13px;">' . __( "To generate tabs, click 'Add Another Tab' at the bottom of this container." , self::TEXT_DOMAIN ) . ' ' . __( "To delete tabs, click 'Remove Tab' to the right of the title field." , self::TEXT_DOMAIN ) . '</p> <p style="padding:0 0 0 13px;margin-top:0;margin-bottom:0;"><em>' . __( "Note : Empty title+content tabs will be removed upon saving." , self::TEXT_DOMAIN ) . '</em></p></div>';
					echo '<div class="dashicons dashicons-editor-help yikes-tabs-how-to-toggle" title="' . __( "Help Me!" , self::TEXT_DOMAIN ) . '"></div>';
										
					// set up the initial display, by looping
					foreach ( $tab_data as $tab ) {
							if ( $i != 1 ) { ?>
								<section class="button-holder" alt="<?php echo $i; ?>">
									<!-- Remove tab button, should not generate for the first tab! -->
									<a href="#" onclick="return false;" class="button-secondary remove_this_tab"><span class="dashicons dashicons-no-alt" style="line-height:1.3;"></span><?php echo __( 'Remove Tab' , self::TEXT_DOMAIN ); ?></a>
									<div style="text-align:center;margin-top:.5em;">	
										<span class="dashicons dashicons-arrow-up move-tab-data-up"></span>
										<span class="dashicons dashicons-arrow-down move-tab-data-down"></span>
									</div>
								</section>
							<?php } else { ?>
								<section style="margin-top:3.5em;" class="button-holder" alt="<?php echo $i; ?>">
									<div style="text-align:center;">
										<span class="dashicons dashicons-arrow-up move-tab-data-up"></span>
										<span class="dashicons dashicons-arrow-down move-tab-data-down"></span>
									</div>
								</section>
							<?php }
							woocommerce_wp_text_input( array( 'id' => '_yikes_wc_custom_repeatable_product_tabs_tab_title_' . $i , 'label' => __( 'Tab Title', self::TEXT_DOMAIN ), 'description' => '', 'value' => $tab['title'] , 'placeholder' => 'Custom Tab Title' , 'class' => 'yikes_woo_tabs_title_field') );
							$this->woocommerce_wp_textarea_input( array( 'id' => '_yikes_wc_custom_repeatable_product_tabs_tab_content_' . $i , 'label' => __( 'Content', self::TEXT_DOMAIN ), 'placeholder' => __( 'HTML and text to display.', self::TEXT_DOMAIN ), 'value' => $tab['content'], 'style' => 'width:70%;height:10.5em;' , 'class' => 'yikes_woo_tabs_content_field' ) );
							if ( $i != count( $tab_data ) ) { 
								echo '<div class="yikes-woo-custom-tab-divider"></div>';
							}
						$i++;
					}
					
					?>
					<div id="duplicate_this_row">
						<a href="#" onclick="return false;" class="button-secondary remove_this_tab" style="float:right;margin-right:4.25em;"><span class="dashicons dashicons-no-alt" style="line-height:1.3;"></span><?php echo __( 'Remove Tab' , self::TEXT_DOMAIN ); ?></a>
						<?php
							// lets add an empty row, to use for duplicating purposes
							woocommerce_wp_text_input( array( 'id' => 'hidden_duplicator_row_title' , 'label' => __( 'Tab Title', self::TEXT_DOMAIN ), 'description' => '', 'placeholder' => 'Custom Tab Title' , 'class' => 'yikes_woo_tabs_title_field' ) );
							$this->woocommerce_wp_textarea_input( array( 'id' => 'hidden_duplicator_row_content' , 'label' => __( 'Content', self::TEXT_DOMAIN ), 'placeholder' => __( 'HTML and text to display.', self::TEXT_DOMAIN ), 'style' => 'width:70%;height:10.5em;' , 'class' => 'yikes_woo_tabs_content_field' ) );
						?>
						<section class="button-holder" alt="<?php echo $i; ?>">
							<div style="text-align:center;">
								<span class="dashicons dashicons-arrow-up move-tab-data-up"></span>
								<span class="dashicons dashicons-arrow-down move-tab-data-down"></span>
							</div>
						</section>
					</div>
								
					<p>
						<label style="display:block;" for="_yikes_wc_custom_repeatable_product_tabs_tab_content_<?php echo $i; ?>"></label>
						<a href="#" class="button-secondary" id="add_another_tab"><em class="dashicons dashicons-plus-alt" style="line-height:1.8;font-size:14px;"></em><?php echo __( 'Add Another Tab' , self::TEXT_DOMAIN ); ?></a>
					</p>
					
					<?php
					// store number of tabs, for count!
					echo '<input type="hidden" value="' . count( $tab_data ) . '" id="number_of_tabs" name="number_of_tabs" >';
					
				echo '</div>';
				
		}


		/**
		* Saves the data inputed into the product boxes, as post meta data
		* identified by the name 'yikes_woo_products_tabs'
		*
		* @param int $post_id the post (product) identifier
		* @param stdClass $post the post (product)
		*/
		public function product_save_data( $post_id, $post ) {
							
			$tab_data = array();
			
			$number_of_tabs = $_POST['number_of_tabs'];
			
			$i = 1;
			while( $i <= $number_of_tabs ) {
			
					$tab_title = stripslashes( $_POST['_yikes_wc_custom_repeatable_product_tabs_tab_title_'.$i] );
					$tab_content = stripslashes( $_POST['_yikes_wc_custom_repeatable_product_tabs_tab_content_'.$i] );
			
				if ( empty( $tab_title ) && empty( $tab_content ) ) {
					
					// clean up if the custom tabs are removed
					unset( $tab_data[$i] );
				
				} elseif ( !empty( $tab_title ) || !empty( $tab_content ) ) {
					
					$tab_id = '';
					
					if ( $tab_title ) {
						if ( strlen( $tab_title ) != strlen( utf8_encode( $tab_title ) ) ) {
							// can't have titles with utf8 characters as it breaks the tab-switching javascript
							$tab_id = "tab-custom";
						} else {
							// convert the tab title into an id string
							$tab_id = strtolower( $tab_title );
							$tab_id = preg_replace( "/[^\w\s]/", '', $tab_id );
							// remove non-alphas, numbers, underscores or whitespace
							$tab_id = preg_replace( "/_+/", ' ', $tab_id );
							// replace all underscores with single spaces
							$tab_id = preg_replace( "/\s+/", '-', $tab_id );
							// replace all multiple spaces with single dashes
							$tab_id = 'tab-' . $tab_id;
							// prepend with 'tab-' string
						}
					}
					// push the data to the array
					$tab_data[$i] = array( 'title' => $tab_title, 'id' => $tab_id, 'content' => $tab_content );
				}	

				$i++;
				
			}
			
			// reset the array count, when items are removed
			$tab_data = array_values( $tab_data );
			// update the post data
			update_post_meta( $post_id, 'yikes_woo_products_tabs', $tab_data );
			
		}

		/**
		* Generates our woo commerce 
		* custom product tab textarea fields
		*
		* @param field
		*/
		private function woocommerce_wp_textarea_input( $field ) {
			global $thepostid, $post;

			if ( ! $thepostid ) $thepostid = $post->ID;
			if ( ! isset( $field['placeholder'] ) ) $field['placeholder'] = '';
			if ( ! isset( $field['class'] ) ) $field['class'] = 'short';
			if ( ! isset( $field['value'] ) ) $field['value'] = get_post_meta( $thepostid, $field['id'], true );

			echo '<p class="form-field ' . $field['id'] . '_field"><label style="display:block;" for="' . $field['id'] . '">' . $field['label'] . '</label><textarea class="' . $field['class'] . '" name="' . $field['id'] . '" id="' . $field['id'] . '" placeholder="' . $field['placeholder'] . '" rows="2" cols="20"' . (isset( $field['style'] ) ? ' style="' . $field['style'] . '"' : '') . '>' . esc_textarea( $field['value'] ) . '</textarea> ';

			if ( isset( $field['description'] ) && $field['description'] ) {
				echo '<span class="description">' . $field['description'] . '</span>';
			}

			echo '</p>';
		}


		/** Helper methods ******************************************************/

		/**
		 * Lazy-load the product_tabs meta data, and return true if it exists,
		 * false otherwise
		 *
		 * @return true if there is custom tab data, false otherwise
		 */
		private function product_has_custom_tabs( $product ) {
			if ( false === $this->tab_data ) {
				$this->tab_data = maybe_unserialize( get_post_meta( $product->id, 'yikes_woo_products_tabs', true ) );
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


		/** Lifecycle methods ******************************************************/

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
		
		/** 
			Review Us Container
			- displays the yikes logo, inside of the 'Custom Tabs' container
		*/
		public function review_us_container() {
			?>
				<div id="yikes-woo-tabs-review-us">
					<a href="http://www.yikesinc.com" target="_blank">
						<img src="<?php echo plugin_dir_url(__FILE__) . 'images/yikes_logo.png';?>" title="Plugin created by YIKES Inc." height=40 width=50 class="yikes-logo" >						
					</a>
				</div>
			<?php
		}

	}
