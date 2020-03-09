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

        // REST API.
        add_action( 'rest_api_init', array( $this, 'register_rest_route' ) );

        // Admin Notice.
        add_action( 'yikes-woo-display-too-many-products-warning', array( $this, 'generate_messages' ) );
    }

    /**
     * Enqueue assets
     */
    public function enqueue_scripts() {
        if ( defined( 'YIKES_Custom_Product_Tabs_Pro_Enabled' ) ) {
            return;
        }
        wp_register_script( 'yikes-cpt-settings-modal', YIKES_Custom_Product_Tabs_URI . 'js/settings.js', array( 'jquery' ), '1.0.0', true );

        wp_localize_script(
            'yikes-cpt-settings-modal',
            'yikesCptSettings',
            array(
                'root'  => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
            )
        );

        wp_enqueue_script( 'yikes-cpt-settings-modal' );
    }

    /**
     * Register Rest API Route.
     */
    public function register_rest_route() {
        register_rest_route(
            'yikes/cpt/v1',
            '/settings',
            array(
                'methods'  => 'POST',
                'callback' => array( $this, 'rest_response' ),
            ),
        );
    }

    /**
     * REST API Response
     *
     * @param WP_REST_Request $request current WP Rest Request.
     */
    public function rest_response( WP_REST_Request $request ) {
        $response = new WP_REST_Response();

        $toggle_the_content = isset( $request['toggle_the_content'] ) ? sanitize_text_field( $request['toggle_the_content'] ) : 'false';

        update_option( 'yikes_cpt_use_the_content', $toggle_the_content );

        $response->set_data( array(
            'status'  => 'success',
            'message' => 'Settings updated.'
        ) );

        return $response;
    }

    /**
     * Generate Admin Notices
     */
    public function generate_messages() {
        ?>
        <div style="display: none;" id="settings-updated" class="updated notice is-dismissible"><p>Settings updated.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
        <?php
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
                    <input id="yikes-woo-toggle-content-input" type="checkbox" name="yikes-the-content-toggle" id="yikes-the-content-toggle" <?php checked( 'true' === $toggle_the_content ); ?> />
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
