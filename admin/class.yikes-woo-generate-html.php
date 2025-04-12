<?php

if ( ! class_exists( 'Yikes_Woo_Custom_Product_Tabs_HTML' ) ) {
	class Yikes_Woo_Custom_Product_Tabs_HTML {

		public function __construct() {
			// empty...
		}

		/**
		* Creates all of the HTML required for tabs on the product edit screen
		*
		* @since 1.5
		*/
		public function generate_html() {
			global $post;

			// Pull the custom tab data out of the database
			$tab_data = yikes_custom_tabs_maybe_unserialize( get_post_meta( $post->ID, 'yikes_woo_products_tabs', true ) );
			$tab_data = is_array( $tab_data ) ? $tab_data : array();

			// If we don't have tab data, we display things slightly differently
			$product_has_tabs = true;
			if ( empty( $tab_data ) ) {
				$product_has_tabs = false;
			}

			// Pull the saved array of reusable tabs
			$reusable_tab_options_array = get_option( 'yikes_woo_reusable_products_tabs_applied', array() );

			// Display the custom tab panel
				echo '<div id="yikes_woocommerce_custom_product_tabs" class="panel wc-metaboxes-wrapper woocommerce_options_panel">';
				echo '<div class="options_group">';

					echo $this->display_yikes_how_to();

					if ( $product_has_tabs === true ) {

						// Loop through all the tabs and add all components
						$this->generate_tab_html( $tab_data, $reusable_tab_options_array, $post );
					}

					// Add duplicate container
					$this->generate_duplicate_html();

					// Add a Saved Tab // Add Another Tab
					echo $this->display_yikes_add_tabs_container( $product_has_tabs );

					// Hidden input field holding # of tabs
					echo $this->display_yikes_number_of_tabs( count( $tab_data ) );

				echo '</div>';
				echo '</div>';

		}

		/* Generate HTML Functions */

		/**
		* Generate the duplicate HTML block
		*
		* @since 1.5
		*
		*/
		protected function generate_duplicate_html() {

			// duplicate_this_row content
			echo '<div id="duplicate_this_row">';

			// Tab title input field
			woocommerce_wp_text_input( array( 'id' => 'hidden_duplicator_row_title' , 'label' => __( 'Tab Title', 'yikes-inc-easy-custom-woocommerce-product-tabs' ), 'description' => '', 'placeholder' =>  __( 'Custom Tab Title' , 'yikes-inc-easy-custom-woocommerce-product-tabs' ), 'class' => 'yikes_woo_tabs_title_field yikes_woo_tabs_title_field_duplicate' ) );

			// WYSIWYG Content field
			$this->display_woocommerce_wp_wysiwyg_input_duplicate();

			// Override Saved Tab checkbox & hidden input fields - Up & Down arrows && Remove Tab button (Duplicate)
			echo $this->display_yikes_button_holder_container_duplicate();

			echo '</div>';
		}

		/**
		* Generate the normal tab HTML block
		*
		* @since 1.5
		*
		* @param array | $tab_data 				| Array of tab data
		* @param array | $reusable_tab_options  | Array of saved tab data
		* @param object| $post					| The global $post object
		*/
		protected function generate_tab_html( $tab_data, $reusable_tab_options, $post ) {
			$i = 1;

			// Set up the initial display, by looping
			foreach ( $tab_data as $tab ) {

				$reusable_tab_flag = false;
				$reusable_tab_id = '';

				// If $tab is in the array of reusable tabs, set flag
				if ( isset( $reusable_tab_options ) && isset( $reusable_tab_options[$post->ID] ) ) {

					foreach( $reusable_tab_options[$post->ID] as $id => $reusable_tab_data ) {
						if ( isset( $reusable_tab_data['tab_id'] ) && isset( $tab['id'] ) && $reusable_tab_data['tab_id'] === $tab['id'] ) {
							$reusable_tab_flag = true;
							$reusable_tab_id = $reusable_tab_data['reusable_tab_id'];
						}
					}
				}

				// Tab Title input field
				$this->display_woocommerce_wp_text_input( $i, $tab );

				// Tab content wysiwyg
				$this->display_woocommerce_wp_wysiwyg_input( $i, $tab );

				// Override Saved Tab checkbox & hidden input fields, Up & Down arrows, Remove Tab button
				echo $this->display_yikes_button_holder_container( $i, $reusable_tab_flag, $reusable_tab_id );

				// line separating tabs
				echo $this->display_yikes_tab_divider( $i, count( $tab_data ) );

				$i++;
			}
		}

		/**
		* Add how-to info HTML to page
		*
		* @since 1.5
		*
		* @return string HTML
		*/
		protected function display_yikes_how_to() {
			$return_html = '';
			$return_html .= '<div class="yikes-woo-tabs-hidden-how-to-info">';
			$return_html .= '<p class="yikes_woo_how_to_info">' . __( "For help using Custom Tabs please visit our <a href='https://yikesplugins.com/support/knowledge-base/product/easy-custom-product-tabs-for-woocommerce/' target='_blank'>Knowledge Base</a>" , 'yikes-inc-easy-custom-woocommerce-product-tabs' ) . '</p>';
			$return_html .= '</div>';
			$return_html .= '<div id="yikes-woo-help-me-icon" class="dashicons dashicons-editor-help yikes-tabs-how-to-toggle" title="' . __( "Help Me!" , 'yikes-inc-easy-custom-woocommerce-product-tabs' ) . '"></div>';

			return $return_html;
		}

		/**
		* Add button holder container HTML to page
		*
		* @since 1.5
		*
		* @param int $i Counter for tab generating loop
		* @return string HTML
		*/
		protected function display_yikes_button_holder_container( $i, $reusable_tab_flag, $reusable_tab_id ) {
			$return_html = '';

			$return_html .= '<section class="button-holder" alt="' . $i . '">';

			$return_html .= '<p class="yikes_wc_override_reusable_tab_container" id="_yikes_wc_override_reusable_tab_container_' . $i . '" ';
			if ( $reusable_tab_flag === true ) {
				$return_html .= ' data-reusable-tab="true">';
			} else {
				$return_html .= ' style="display: none;">';
			}
			$return_html .= 	'<input type="checkbox" class="_yikes_wc_override_reusable_tab" id="_yikes_wc_override_reusable_tab_' . $i . '" data-tab-number="'. $i .'"';
			$return_html .= 		'title="' . __( 'Check this box to override the saved tab' , 'yikes-inc-easy-custom-woocommerce-product-tabs' ) . '">';
			$return_html .= 	'<label id="_yikes_wc_override_reusable_tab_label_' . $i . '" for="_yikes_wc_override_reusable_tab_' . $i . '" class="_yikes_wc_override_reusable_tab_label">';
			$return_html .= 		__( ' Override Saved Tab' , 'yikes-inc-easy-custom-woocommerce-product-tabs' );
			$return_html .=		'</label>';
			$return_html .= 	'<input type="hidden" name="_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' . $i . '_action" class="_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_action"';
			$return_html .= 		'id="_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' . $i . '_action" value="none">';
			$return_html .= 	'<input type="hidden" name="_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' . $i . '" class="_yikes_wc_custom_repeatable_product_tabs_saved_tab_id"';
			$return_html .= 		'id="_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' . $i . '" value="' . $reusable_tab_id . '">';
			$return_html .= '</p>';

			$return_html .= 	'<div class="yikes_wc_move_tab_container">';
			$return_html .= 		'<p class="yikes_wc_move_tab">Move tab order</p>';
			$return_html .= 		'<span class="dashicons dashicons-arrow-up move-tab-data-up"></span>';
			$return_html .= 		'<span class="dashicons dashicons-arrow-down move-tab-data-down"></span>';
			$return_html .= 	'</div>';
			$return_html .= 	'<a href="#" onclick="return false;" class="button-secondary remove_this_tab"><span class="dashicons dashicons-no-alt"></span>';
			$return_html .= 		__( 'Remove Tab' , 'yikes-inc-easy-custom-woocommerce-product-tabs' );
			$return_html .= 	'</a>';
			$return_html .= '</section>';

			return $return_html;
		}

		/**
		* Add tab divider HTML to page
		*
		* @since 1.5
		*
		* @param int $i 		Counter for tab generating loop
		* @param int $tab_count Total # of tabs
		* @return string HTML
		*/
		protected function display_yikes_tab_divider( $i, $tab_count ) {
			$return_html = '';
			if ( $i != $tab_count ) {
				$return_html .= '<div class="yikes-woo-custom-tab-divider"></div>';
			}

			return $return_html;
		}

		/**
		* Call input field generation function and echo HTML to page
		*
		* @since 1.5
		*
		* @param int   $i 		Counter for tab generating loop
		* @param array $tab		Array of tab data
		*/
		protected function display_woocommerce_wp_text_input( $i, $tab ) {

			woocommerce_wp_text_input( array( 'id' => '_yikes_wc_custom_repeatable_product_tabs_tab_title_' . $i , 'label' => __( 'Tab Title', 'yikes-inc-easy-custom-woocommerce-product-tabs' ), 'description' => '', 'value' => $tab['title'] , 'placeholder' => __( 'Custom Tab Title' , 'yikes-inc-easy-custom-woocommerce-product-tabs' ), 'class' => 'yikes_woo_tabs_title_field') );
		}

		/**
		* Call wp_editor wrapped function and echo HTML to page
		*
		* @since 1.5
		*
		* @param int   $i 		Counter for tab generating loop
		* @param array $tab		Array of tab data
		*/
		protected function display_woocommerce_wp_wysiwyg_input( $i, $tab ) {
			echo '<div class="form-field-tinymce _yikes_wc_custom_repeatable_product_tabs_tab_content_field _yikes_wc_custom_repeatable_product_tabs_tab_content_' . $i . '_field">';
				$this->woocommerce_wp_wysiwyg_input( array(
					'id' => '_yikes_wc_custom_repeatable_product_tabs_tab_content_' . $i ,
					'label' => __( 'Content', 'yikes-inc-easy-custom-woocommerce-product-tabs' ),
					'placeholder' => __( 'HTML and text to display.', 'yikes-inc-easy-custom-woocommerce-product-tabs' ),
					'value' => $tab['content'],
					'style' => 'width:100%;min-height:10rem;',
					'class' => 'yikes_woo_tabs_content_field',
					'number' => $i
				) );
			echo '</div>';
		}

		/* Hidden Duplicate HTML Section */

		/**
		* Add duplicate remove tab button HTML to page
		*
		* @since 1.5
		*
		* @return string HTML
		*/
		protected function display_yikes_remove_tab_duplicate() {
			$return_html = '';

			$return_html .= '<a href="#" onclick="return false;" class="button-secondary remove_this_tab">';
			$return_html .= 	'<span class="dashicons dashicons-no-alt"></span>';
			$return_html .=		__( 'Remove Tab' , 'yikes-inc-easy-custom-woocommerce-product-tabs' );
			$return_html .=	'</a>';

			return $return_html;
		}

		/**
		* Call input field generation function and echo HTML to page
		*
		* @since 1.5
		*
		* @param array $tab		Array of tab data
		*/
		protected function display_woocommerce_wp_wysiwyg_input_duplicate() {

			$this->woocommerce_wp_textarea_input( array( 'id' => 'hidden_duplicator_row_content' , 'label' => __( 'Content', 'yikes-inc-easy-custom-woocommerce-product-tabs' ), 'placeholder' => __( 'HTML and text to display.', 'yikes-inc-easy-custom-woocommerce-product-tabs' ), 'style' => 'width:100%; min-height:10rem;' , 'class' => 'yikes_woo_tabs_content_field' ) );
		}

		/**
		* Add duplicate button holder container HTML to page
		*
		* @since 1.5
		*
		* @return string HTML
		*/
		protected function display_yikes_button_holder_container_duplicate() {
			$return_html = '';

			$return_html .= '<section class="button-holder hidden_duplicator_row_button_holder last-button-holder" alt="">';
			$return_html .= '<p class="yikes_wc_override_reusable_tab_container _yikes_wc_override_reusable_tab_container_duplicate" id="_yikes_wc_override_reusable_tab_container_duplicate" style="display: none;">';
			$return_html .= 	'<input type="checkbox" class="_yikes_wc_override_reusable_tab" id="_yikes_wc_override_reusable_tab_duplicate" title="' . __( 'Check this box to override the saved tab' , 'yikes-inc-easy-custom-woocommerce-product-tabs' ) . '" />';
			$return_html .= 	'<label class="_yikes_wc_override_reusable_tab_label_duplicate">' . __( 'Override Saved Tab' , 'yikes-inc-easy-custom-woocommerce-product-tabs' ) . '</label>';
			$return_html .=		'<input type="hidden" class="_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_action" id="_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_action_duplicate" value="none">';
			$return_html .= 	'<input type="hidden" class="_yikes_wc_custom_repeatable_product_tabs_saved_tab_id" id="_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_duplicate" value="">';
			$return_html .= '</p>';
			$return_html .= 	'<div class="yikes_wc_move_tab_container">';
			$return_html .= 		'<p class="yikes_wc_move_tab">Move tab order</p>';
			$return_html .= 		'<span class="dashicons dashicons-arrow-up move-tab-data-up"></span>';
			$return_html .= 		'<span class="dashicons dashicons-arrow-down move-tab-data-down"></span>';
			$return_html .= 	'</div>';
			$return_html .= 	'<a href="#" onclick="return false;" class="button-secondary remove_this_tab">';
			$return_html .= 		'<span class="dashicons dashicons-no-alt"></span>';
			$return_html .=			__( 'Remove Tab' , 'yikes-inc-easy-custom-woocommerce-product-tabs' );
			$return_html .=		'</a>';
			$return_html .= '</section>';

			return $return_html;
		}

		/**
		* Add hidden input field for number of tabs to page
		*
		* @since 1.5
		*
		* @return string HTML
		*/
		protected function display_yikes_number_of_tabs( $tab_count ) {
			$return_html = '';

			$return_html .= '<input type="hidden" value="' . $tab_count . '" id="number_of_tabs" name="number_of_tabs" >';

			return $return_html;
		}

		/**
		* Add 'Add Another Tab' and 'Add a Saved Tab' buttons to page
		*
		* @since 1.5
		*
		* @param bool | $product_has_tabs | flag indicating whether the product has any defined tabs
		* @return string HTML
		*/
		protected function display_yikes_add_tabs_container( $product_has_tabs ) {
			$return_html = '';

			// If we don't have any tabs, then add some classes
			$classes_to_add = ( $product_has_tabs === false ) ? '_yikes_wc_add_tab_center_new _yikes_wc_add_tab_center' : '';

			$return_html .= '<div class="add_tabs_container ' . $classes_to_add . '">';
			$return_html .=		'<span id="yikes_woo_ajax_save_feedback"></span>';
			$return_html .= 	'<a href="#" class="button-secondary _yikes_wc_add_tabs" id="add_another_tab">';
			$return_html .= 		'<i class="dashicons dashicons-plus-alt inline-button-dashicons"></i>';
			$return_html .=			__( 'Add a Tab' , 'yikes-inc-easy-custom-woocommerce-product-tabs' );
			$return_html .=		'</a>';
			$return_html .= 	'<span class="yikes_wc_apply_reusable_tab_container">';
			$return_html .= 		'<span class="button-secondary _yikes_wc_apply_a_saved_tab _yikes_wc_add_tabs" id="_yikes_wc_apply_a_saved_tab">';
			$return_html .= 			'<i class="dashicons  dashicons-plus-alt inline-button-dashicons"></i>';
			$return_html .= 			__( 'Add a Saved Tab' , 'yikes-inc-easy-custom-woocommerce-product-tabs' );
			$return_html .=			'</span>';
			$return_html .= 	'</span>';
			$return_html .= 	'<input name="save" class="button button-primary" id="yikes_woo_save_custom_tabs" value="Save Tabs" type="button">';
			$return_html .= '</div>';

			return $return_html;
		}

		/**
		* Generates a textarea field for hidden duplicate HTML block
		*
		* @param array $field Array of HTML field related values
		*/
		private function woocommerce_wp_textarea_input( $field ) {

			if ( ! isset( $field['placeholder'] ) ) $field['placeholder'] = '';
			if ( ! isset( $field['class'] ) ) $field['class'] = '';
			if ( ! isset( $field['value'] ) ) $field['value'] = '';

			echo '<p class="form-field-tinymce ' . $field['id'] . '_field">       <textarea class="' . $field['class'] . '" name="' . $field['id'] . '" id="' . $field['id'] . '" placeholder="' . $field['placeholder'] . '" rows="2" cols="20"' . (isset( $field['style'] ) ? ' style="' . $field['style'] . '"' : '') . '>' . $field['value'] . '</textarea> ';

			if ( isset( $field['description'] ) && $field['description'] ) {
				echo '<span class="description">' . $field['description'] . '</span>';
			}

			echo '</p>';
		}

		/**
		* Wrapper function for wp_editor
		*
		* @param array $field Array of HTML field related values
		*/
		private function woocommerce_wp_wysiwyg_input( $field ) {

			if ( ! isset( $field['placeholder'] ) ) $field['placeholder'] = '';
			if ( ! isset( $field['class'] ) ) $field['class'] = '';
			if ( ! isset( $field['value'] ) ) $field['value'] = '';

			$editor_settings = array(
				'textarea_name' => $field['id']
			);

			wp_editor( $field['value'], $field['id'], $editor_settings );

			if ( isset( $field['description'] ) && $field['description'] ) {
				echo '<span class="description">' . $field['description'] . '</span>';
			}
		}

		/* END HTML Functions */
	}
}

?>
