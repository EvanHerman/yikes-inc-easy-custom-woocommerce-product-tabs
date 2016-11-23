<?php 
	// Get our saved tabs array
	$yikes_custom_tab_data = get_option( 'yikes_woo_reusable_products_tabs', array() );
?>

<div class="container-fluid">

	<div class="row">

		<h4> YIKES Custom Product Tabs for WooCommerce </h4>

		<div class="yikes_woo_settings_info">
			<p>
				<?php _e( "Create and save tabs on this page to apply them to existing products.", 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?>
			</p>
			<p>
				<?php _e( "If a tab is updated here, the changes will take effect on all products currently using that tab.", 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?>
			</p>
		</div>

		<div class="yikes-woo-tabs-hidden-how-to-info">
			<p>
				<?php _e( "To generate a tab, click 'Add Another Tab' at the bottom left of this page. " , 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?>
			</p>
			<p>
				<?php _e( "To delete a tab, click 'Delete Tab' underneath the tab." , 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?>
			</p>
			<p>
				<?php _e( "To save/update a tab, click 'Save/Update Tab' underneath the tab." , 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?>
			</p>
		</div>

		<div id="yikes-woo-help-me-icon" class="dashicons yikes-tabs-how-to-toggle-cursor dashicons-editor-help" title="Help Me!"></div>

	</div>

	<div class="yikes_woo_reusable_tabs">

	<?php
		$ii = 1;

		if ( ! empty( $yikes_custom_tab_data ) ) {
			
			foreach ( $yikes_custom_tab_data as $key => $tab_data ) {
	?>
		<div class="row form-group yikes_woo_reusable_tabs_container" id="yikes_woo_reusable_tabs_container_<?php echo $ii ?>" data-tab-number="<?php echo $ii; ?>" data-tab-id="<?php echo $tab_data['tab_id']; ?>">

			<!-- Title -->
			<div class="yikes_woo_reusable_tab_title">
				<label class="yikes_woo_reusable_tab_title_label" for="yikes_woo_reusable_tab_title_<?php echo $ii; ?>">
					<?php _e( 'Tab Title: ' ); ?>
				</label>
				<input type="text" class="form-control" id="yikes_woo_reusable_tab_title_<?php echo $ii; ?>" value="<?php echo $tab_data['tab_title']; ?>" />
			</div>
			
			<!-- Content -->
			<div class="yikes_woo_reusable_tab_content">
				<?php wp_editor( stripslashes( $tab_data['tab_content'] ), 'yikes_woo_reusable_tab_content_' . $ii, array( 'textarea_name' => 'yikes_woo_reusable_tab_content_' . $ii, 'textarea_rows' => 8 ) ); ?>
			</div>

			<!-- Buttons -->
			<div class="yikes_woo_reusable_tab_buttons">
				<span class="button-secondary yikes_woo_save_this_tab" id="yikes_woo_reusable_tab_save_<?php echo $ii; ?>" data-tab-number="<?php echo $ii; ?>">
					<i class="dashicons dashicons-star-filled inline-button-dashicons"></i>
					<?php _e( 'Update Tab' ); ?>
				</span>
				<span class="button-secondary yikes_woo_delete_this_tab" id="yikes_woo_reusable_tab_delete_<?php echo $ii; ?>" data-tab-number="<?php echo $ii; ?>">
					<i class="dashicons dashicons-dismiss inline-button-dashicons"></i>
					<?php _e( 'Delete Tab' ); ?>
				</span>
			</div>
			<hr class="yikes_woo_reusable_tabs_hr_<?php echo $ii; ?>">
		</div>

	<?php 
			$ii++;
			}
		} else {

			// If there are no saved tabs, create a default blank one
	?>
			<div class="row form-group yikes_woo_reusable_tabs_container" id="yikes_woo_reusable_tabs_container_<?php echo $ii ?>" data-tab-number="<?php echo $ii; ?>">

			<!-- Title -->
			<div class="yikes_woo_reusable_tab_title">
				<label class="yikes_woo_reusable_tab_title_label" for="yikes_woo_reusable_tab_title_<?php echo $ii; ?>">
					<?php _e( 'Tab Title: ' ); ?>
				</label>
				<input type="text" class="form-control" id="yikes_woo_reusable_tab_title_<?php echo $ii; ?>" />
			</div>
			
			<!-- Content -->
			<div class="yikes_woo_reusable_tab_content">
				<?php wp_editor( '', 'yikes_woo_reusable_tab_content_' . $ii, array( 'textarea_name' => 'yikes_woo_reusable_tab_content_' . $ii, 'textarea_rows' => 8 ) ); ?>
			</div>

			<!-- Buttons -->
			<div class="yikes_woo_reusable_tab_buttons">
				<span class="button-secondary yikes_woo_save_this_tab" id="yikes_woo_reusable_tab_save_<?php echo $ii; ?>" data-tab-number="<?php echo $ii; ?>">
					<i class="dashicons dashicons-star-filled inline-button-dashicons"></i>
					<?php _e( 'Save Tab' ); ?>
				</span>
				<span class="button-secondary yikes_woo_delete_this_tab" id="yikes_woo_reusable_tab_delete_<?php echo $ii; ?>" data-tab-number="<?php echo $ii; ?>">
					<i class="dashicons dashicons-dismiss inline-button-dashicons"></i>
					<?php _e( 'Delete Tab' ); ?>
				</span>
			</div>
			<hr class="yikes_woo_reusable_tabs_hr_<?php echo $ii; ?>">
		</div>
	<?php
		}
	?>
		<!-- Add Another Tab -->
		<div class="row yikes_woo_add_another_tab_container">
			<span class="button-secondary yikes_woo_add_another_tab" id="yikes_woo_add_another_tab">
				<i class="dashicons dashicons-plus-alt inline-button-dashicons"></i>
				<?php _e( 'Add Another Tab' ); ?>
			</span>
		</div>

	</div>
</div>