<?php 

// For safety purposes...
// Make sure we have a tab
if ( ! $new_tab && ( ! isset( $tab ) || empty( $tab ) ) ) {
	if ( isset( $redirect ) ) {
		echo '<p> Oops. It looks like something went wrong. Please <a href="' . $redirect . '" title="go back">go back</a> and try again</p>';
	}
	exit;
}

// Set variables before using them
$tab_title 	 = ( isset( $tab['tab_title'] ) && ! empty( $tab['tab_title'] ) ) ? $tab['tab_title'] : '';
$tab_content = ( isset( $tab['tab_content'] ) && ! empty( $tab['tab_content'] ) ) ? $tab['tab_content'] : '';
$tab_id 	 = ( isset( $tab['tab_id'] ) && ! empty( $tab['tab_id'] ) ) ? (int) $tab['tab_id'] : 'new';

// Tab stats
$number_of_products_using_this_tab = count( $products );

?>
<div class="wrap">
	<h1 class="screen-media">
		Custom Product Tabs for WooCommerce | <span id="yikes_woo_tab_title_header"><?php echo $tab_title; ?></span>
		<span class="yikes_woo_add_another_tab page-title-action" id="yikes_woo_add_another_tab">
			<a href="<?php echo $new_tab_url; ?>"> <?php _e( 'Add Tab', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?>	</a>
		</span>
	</h1>

	<?php if ( $new_tab !== true ) { ?>
		<!-- Only show this if we're updating an existing tab -->
		<div class="yikes_woo_settings_info">
			<p>
				<?php _e( "Any updates made here will apply to all products using this tab.", 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?>
			</p>
		</div>
	<?php } ?>

	<div id="poststuff">

		<div class="yikes_woo_go_back_url">
			<a href="<?php echo $redirect; ?>"><?php _e( 'Go Back to Saved Tabs list', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?></a>  
		</div>

		<div class="row yikes_woo_reusable_tabs_container" id="yikes_woo_reusable_tabs_container_<?php echo $tab_id ?>" data-tab-id="<?php echo $tab_id; ?>">

			<!-- Title -->
			<div class="yikes_woo_reusable_tab_title">
				<label class="yikes_woo_reusable_tab_title_label" for="yikes_woo_reusable_tab_title_<?php echo $tab_id; ?>">
					<h3><?php _e( 'Tab Title', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?></h3>
				</label>
				<input type="text" id="yikes_woo_reusable_tab_title_<?php echo $tab_id; ?>" value="<?php echo $tab_title; ?>" />
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

			<!-- Buttons -->
			<div class="yikes_woo_save_and_delete_tab_buttons">
				<span class="button button-primary yikes_woo_save_this_tab" id="yikes_woo_save_this_tab_<?php echo $tab_id; ?>" data-tab-id="<?php echo $tab_id; ?>">
					<?php _e( 'Save Tab', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?>
				</span>
				<span class="button button-secondary yikes_woo_delete_this_tab yikes_woo_delete_this_tab_single" id="yikes_woo_delete_this_tab_<?php echo $tab_id; ?>" data-tab-id="<?php echo $tab_id; ?>">
					<i class="dashicons dashicons-dismiss inline-button-dashicons"></i>
					<?php _e( 'Delete Tab', 'yikes-inc-easy-custom-woocommerce-product-tabs' ); ?>
				</span>
			</div>
		</div>
		<!-- <div class="yikes_woo_saved_tab_products"> -->
			<!-- <h3 class="yikes_woo_saved_tab_header">Products</h3> -->
			<!-- <div class="inside entry-details-overview"> -->
				<!-- <?php // if ( $number_of_products_using_this_tab === 0 ) { ?> -->
					<!-- <p>
						This tab is currently not used for any products.
					</p> -->
				<!-- <?php//  } else {
					/* $plural_product_name = ( $number_of_products_using_this_tab > 1 ) ? 'products' : 'product'; */
				?> -->
					<!-- <p> -->
						<!-- This tab is currently used on <span class="yikes_woo_number_of_products"><?php // echo $number_of_products_using_this_tab; ?></span> <?php // echo $plural_product_name ?> -->
					<!-- </p> -->
					<!-- <?php // foreach( $products as $product_id ) { ?>  -->
						<!-- <p> -->
							<!-- <?php /* $edit_product_url = add_query_arg( array( 'post' => $product_id, 'action' => 'edit' ), esc_url_raw( 'post.php' ) ); */ ?> -->
							<!-- <span> <a href="<?php // echo $edit_product_url ?>"> <?php // echo get_the_title( $product_id ); ?> </a> </span> -->
						<!-- </p> -->
					<!-- <?php // } ?> -->
				<!-- <?php // } ?> -->
			<!-- </div> -->
		<!-- </div> -->
	</div>
</div>