<?php
/**
 * YIKES Inc. Custom WooCommerce Product Tabs
 *
 * @author Freddie Mixell
 * @since 1.7.0
 */

class YIKES_Custom_Product_Tabs_Settings {

    /**
     * Constructor
     */
    public function __construct() {

        // Enqueue scripts & styles.
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10, 1 );

        // Render settings area.
        add_action( 'yikes-woo-settings-area', array( $this, 'render_settings_area' ), 10 );
    }

    /**
     * Enqueue assets
     */
    public function enqueue_scripts() {
        if ( defined( 'YIKES_Custom_Product_Tabs_Pro_Enabled' ) ) {
            return;
        }
        wp_enqueue_script( 'yikes-cpt-settings-modal', YIKES_Custom_Product_Tabs_URI . 'js/settings.js', array( 'jquery' ), '1.0.0', true );
    }

    /**
     * Render settings area
     */
    public function render_settings_area() {
        if ( defined( 'YIKES_Custom_Product_Tabs_Pro_Enabled' ) ) {
            return;
        }

        $toggle_the_content = get_option( 'yikes_cpt_use_the_content' );
        ?>
        <div class="postbox yikes-woo-buy-us yikes-woo-all-about-us-box" id="yikes-woo-buy-us">
            <h3 class="yikes-woo-settings-title">Settings</h3>
            <div class="yikes-woo-buy-us-body">
                <h4><?php _e( 'Use a custom filter for the_content', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?> </h4>
                <p><?php _e( 'If you\'re using a page builder and you\'re having issues toggle this setting on. This will allow other plugins to use the WordPress \'the_content\' filter will we use our own custom version.', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?></p>
                <p>
                <label>Toggle the_content filter.
                    <input id="yikes-woo-toggle-content-input" type="checkbox" name="yikes-the-content-toggle" id="yikes-the-content-toggle" <?php checked( 1 == $toggle_the_content ); ?> />
                </label>
                <p>
                <a id="yikes-woo-toggle-content" class="button button-primary" href="https://yikesplugins.com/plugin/custom-product-tabs-pro/" target="_blank">
                    <?php _e( 'Save Settings', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?>
                </a>
            </div><!-- .yikes-woo-buy-us-body -->
        </div>
        <?php
    }
}

new YIKES_Custom_Product_Tabs_Settings();
