<?php 

// For safety purposes...
// Make sure we have a tab
if ( ! $new_tab && ( ! isset( $tab ) || empty( $tab ) ) ) {
	if ( isset( $redirect ) ) {
		echo '<p>';
		echo sprintf( __( 'It looks like something went wrong. Please %1sgo back%2s. and try again', 'yikes-inc-easy-custom-woocommerce-product-tabs' ), 
				'<a href="' . $redirect . '" title="go back">', '</a>' ); 
		echo '</p>';
	}
	exit;
}

// Set variables before using them
$tab_title   = isset( $tab['tab_title'] ) && ! empty( $tab['tab_title'] ) ? $tab['tab_title'] : '';
$tab_content = isset( $tab['tab_content'] ) && ! empty( $tab['tab_content'] ) ? $tab['tab_content'] : '';
$tab_id      = isset( $tab['tab_id'] ) && ! empty( $tab['tab_id'] ) ? (int) $tab['tab_id'] : 'new';
$tab_name    = isset( $tab['tab_name'] ) ? $tab['tab_name'] : '';
$taxonomies  = isset( $tab['taxonomies'] ) && ! empty( $tab['taxonomies'] ) ? $tab['taxonomies'] : '';
$global      = isset( $tab['global_tab'] ) && $tab['global_tab'] === true ? true : false;

?>

<div class="wrap woo-ct-admin-page-wrap">

	<h1>
		<span class="dashicons dashicons-exerpt-view"></span>
		<?php esc_html_e( 'Custom Product Tabs for WooCommerce ', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?> | <span id="yikes_woo_tab_title_header"><?php echo ! empty( $tab_title ) ? esc_html( $tab_title ) : __( 'New Tab', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?></span>
		<a href="<?php echo $new_tab_url; ?>" class="page-title-action"> <?php _e( 'Add New', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?>	</a>
	</h1>

	<div class="yikes_woo_go_back_url">
		<a class="button" href="<?php echo esc_url( $redirect ); ?>">
			<?php _e( 'Go Back to All Saved Tabs list', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?>
		</a>
	</div>

	<div id="poststuff">

		<div id="post-body" class="metabox-holder columns-2"> 

			<!-- main content -->
			<div id="post-body-content">

				<!-- here -->
				<?php if ( $new_tab !== true ) { ?>
					<!-- Only show this if we're updating an existing tab -->
					<div class="yikes_woo_settings_info">
						<p>
							<?php _e( "Any updates made here will apply to all products using this tab.", 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?>
						</p>
					</div>
				<?php } ?>

				<?php do_action( 'yikes-woo-display-too-many-products-warning' ); ?>

				<div class="row yikes_woo_reusable_tabs_container" id="yikes_woo_reusable_tabs_container_<?php echo $tab_id ?>" data-tab-id="<?php echo $tab_id; ?>">

					<!-- Title -->
					<div class="yikes_woo_reusable_tab_title">
						<label class="yikes_woo_reusable_tab_title_label" for="yikes_woo_reusable_tab_title_<?php echo $tab_id; ?>">
							<h3><?php _e( 'Tab Title', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?></h3>
						</label>
						<input type="text" id="yikes_woo_reusable_tab_title_<?php echo $tab_id; ?>" class="widefat" value="<?php echo $tab_title; ?>" />
					</div>

					<!-- Tab Name -->
					<div class="yikes_woo_reusable_tab_title">
						<label class="yikes_woo_reusable_tab_title_label" for="yikes_woo_reusable_tab_name_<?php echo $tab_id; ?>">
							<h3><?php _e( 'Tab Name', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?></h3>
						</label>
						<input type="text" id="yikes_woo_reusable_tab_name_<?php echo $tab_id; ?>" class="widefat" value="<?php echo $tab_name; ?>" />
						<div class="yikes_woo_reusable_tab_title_note"><em><?php _e( 'This is for your reference only.', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?></em></div>
					</div>

					<!-- Content -->
					<div class="yikes_woo_reusable_tab_content">
						<label class="yikes_woo_reusable_tab_content_label" for="yikes_woo_reusable_tab_content_<?php echo $tab_id; ?>">
							<h3><?php _e( 'Tab Content', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?></h3>
						</label>
						<?php 
							wp_editor( stripslashes( $tab_content ), 'yikes_woo_reusable_tab_content_' . $tab_id, array( 'textarea_name' => 'yikes_woo_reusable_tab_content_' . $tab_id, 'textarea_rows' => 8 ) );
						?>
					</div>

					<?php do_action( 'yikes-woo-saved-tab-before-save-buttons', $saved_tab_id, $taxonomies, $global ); ?>

					<!-- Buttons -->
					<div class="yikes_woo_save_and_delete_tab_buttons">
						<span class="button button-primary yikes_woo_save_this_tab" id="yikes_woo_save_this_tab_<?php echo $tab_id; ?>" data-tab-id="<?php echo $tab_id; ?>">
							<?php _e( 'Save Tab', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?>
						</span>
						<span class="button button-primary yikes_woo_delete_this_tab yikes_woo_delete_this_tab_single" id="yikes_woo_delete_this_tab_<?php echo $tab_id; ?>" data-tab-id="<?php echo $tab_id; ?>">
							<?php _e( 'Delete Tab', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?>
						</span>
					</div>

				</div>

			</div>
			<!-- post-body-content -->

			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">

				<div class="meta-box-sortables">

					<?php do_action( 'yikes-woo-saved-tabs-list-ad' ); ?>

					<?php do_action( 'yikes-woo-saved-tab-after-save-buttons', $saved_tab_id ); ?>

				</div>
				<!-- .meta-box-sortables -->

			</div>
			<!-- #postbox-container-1 .postbox-container -->

		</div>
		<!-- #post-body .metabox-holder .columns-2 -->

		<br class="clear">
	</div>
	<!-- #poststuff -->

</div> <!-- .wrap -->