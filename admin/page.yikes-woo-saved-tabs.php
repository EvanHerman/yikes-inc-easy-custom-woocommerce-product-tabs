<div class="wrap">

	<h1>
		Custom Product Tabs for WooCommerce | Saved Tabs
		<span class="yikes_woo_add_another_tab page-title-action" id="yikes_woo_add_another_tab">
			<a href="<?php echo $new_tab_url; ?>"> <?php _e( 'Add Tab', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?>	</a>
		</span>
	</h1>

	<!-- Delete-success Message -->
	<div id="yikes_woo_delete_success_message" class="deleted notice notice-success is-dismissible" style="<?php echo $delete_message_display ?>">
		<p> 
			<?php _e( 'Tab deleted!', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?>
		</p>
		<button type="button" class="notice-dismiss">
			<span class="screen-reader-text">Dismiss this notice.</span>
		</button>
	</div>

	<div class="yikes_woo_settings_info">
		<p>
			<?php _e( "Create and save tabs you can add to multiple products.", 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?>
		</p>
	</div>

	<div id="poststuff">

		<!-- Bulk Actions -->
		<div class="tablenav top">
			<div class="alignleft actions bulkactions">
				<label for="bulk-action-selector-top" class="screen-reader-text"><?php _e( 'Select bulk action', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?></label>
				<select name="action" id="bulk-action-selector-top">
					<option value="-1"><?php _e( 'Bulk Actions', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?></option>
					<option value="delete" class="hide-if-no-js"><?php _e( 'Delete', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?></option>
				</select>
				<input type="button" id="bulk-action-button" class="button action yikes_woo_handle_bulk_action" value="<?php _e( 'Apply', 'yikes-inc-easy-custom-woocommerce-product-tabs' ) ?>">
			</div>
			<br class="clear">
		</div>
		<table class="widefat fixed" cellspacing="0">
			<thead>
				<tr>
					<td id="cb" class="manage-column column-cb check-column" scope="col">
						<label class="screen-reader-text" for="cb-select-all-1"><?php _e( 'Select All', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?></label>
						<input id="cb-select-all-1" type="checkbox">
					</td>
					<th id="columnname" class="manage-column column-columnname" scope="col">
						<?php _e( 'Tab Title', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?>
					</th>
					<th id="columnname" class="manage-column column-columnname" scope="col">
						<?php _e( 'Tab Content Preview', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?>
					</th>
					<th id="columnname" class="manage-column column-columnname" scope="col">&nbsp;</th>
				</tr>
			</thead>

			<tfoot>
				<tr>
					<td id="cb" class="manage-column column-cb check-column" scope="col">
						<label class="screen-reader-text" for="cb-select-all-1"><?php _e( 'Select All', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?></label>
						<input id="cb-select-all-1" type="checkbox">
					</td>
					<th id="columnname" class="manage-column column-columnname" scope="col">
						<?php _e( 'Tab Title', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?>
					</th>
					<th id="columnname" class="manage-column column-columnname" scope="col">
						<?php _e( 'Tab Content Preview', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?>
					</th>
					<th id="columnname" class="manage-column column-columnname" scope="col">&nbsp;</th>
				</tr>
			</tfoot>

			<tbody>
				<?php
					if( ! empty( $yikes_custom_tab_data ) ) {

						foreach ( $yikes_custom_tab_data as $key => $tab_data ) {

							// Set variables before using them
							$tab_title 			 = ( isset( $tab_data['tab_title'] ) && ! empty( $tab_data['tab_title'] ) ) ? $tab_data['tab_title'] : '';
							$tab_content_excerpt = ( isset( $tab_data['tab_content'] ) && ! empty( $tab_data['tab_content'] ) ) ? stripslashes( substr( wp_strip_all_tags( $tab_data['tab_content'] ), 0, 150 ) ) : '';
							$tab_id 			 = ( isset( $tab_data['tab_id'] ) && ! empty( $tab_data['tab_id'] ) ) ? (int) $tab_data['tab_id'] : 0;
							$edit_tab_url 		 = ( isset( $settings_page_slug ) ) ? add_query_arg( array( 'saved-tab-id' => $tab_id ), esc_url_raw( 'options-general.php?page=' . $settings_page_slug ) ) : '';
							?>
								<tr class="yikes_woo_saved_tabs_row" id="yikes_woo_saved_tabs_row_<?php echo $tab_id; ?>" data-tab-id="<?php echo $tab_id; ?>">
									<th class="check-column" scope="row">
										<input class="entry-bulk-action-checkbox" type="checkbox" value="<?php echo $tab_id; ?>" />
									</th>
									<td class="column-columnname">
										<?php echo $tab_title; ?>
										<div class="row-actions">
											<span class="">
												<a href="<?php echo $edit_tab_url ?>"><?php _e( 'Edit Tab', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?></a>
											</span> |
											<span data-tab-id="<?php echo $tab_id; ?>" class="yikes_woo_delete_this_tab trash">
												<?php _e( 'Delete Tab', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?>
											</span>
										</div>
									</td>
									<td class="column-columnname"><?php echo $tab_content_excerpt; ?></td>
									<td class="column-columnname" align="center">
										<a href="<?php echo $edit_tab_url ?>" class="button-secondary view-saved-tab-button" data-entry-id="<?php echo (int) $tab_id; ?>">
											<?php _e( 'Edit Tab', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?>
										</a>
									</td>
								</tr>
							<?php
						}
					} else {
						?>
							<tr>
								<td class="column-columnname" colspan="5">
									<strong><?php _e( 'There are no saved tabs. Add one!', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?> </strong>
								</td>
							</tr>
						<?php
					}
				?>
			</tbody>
		</table>
	</div>
</div>