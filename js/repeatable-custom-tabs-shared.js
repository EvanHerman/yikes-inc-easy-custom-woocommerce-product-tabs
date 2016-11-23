	/**
	* @summary Fetch the wp_editor HTML via AJAX.
	*
	* @since 1.5
	*
	* @param string | textarea_id  | ID of the textarea where we're initializing the WYSIWYG editor
	* @param bool	| product_page | bool indicating whether we're on the product page (true) or settings page (false)
	*
	* @return bool
	*/
	function yikes_woo_get_wp_editor_ajax( textarea_id, product_page ) {

		// Create data object for AJAX call
		var data = {
			'action': 'yikes_woo_get_wp_editor',
			'textarea_id': textarea_id,
			'security_nonce': repeatable_custom_tabs_shared.get_wp_editor_security_nonce
		};

		// AJAX
		jQuery.post( repeatable_custom_tabs_shared.ajaxurl, data, function( response ) {

			// Re-enable buttons / arrows if on the product page. If on the settings page, remove the loading gif.
			if ( product_page === true ) {
				yikes_woo_toggle_controls( 'enable' );
			} else {
				jQuery( '.yikes_woo_add_another_tab_container' ).children( 'img' ).remove();
				jQuery( '.yikes_woo_add_another_tab_container' ).children( '.yikes_woo_add_another_tab' ).show();
			}

			// If call failed, show error message
			if ( response.success == false ) {
				jQuery( '.' + textarea_id + '_field' ).html( '<p>' + repeatable_custom_tabs_shared.get_wp_editor_failure_message + '</p>' );

				return false;
			}

			// Add wp_editor HTML to the page
			jQuery( '.' + textarea_id + '_field' ).html( response ).addClass( '_yikes_wc_custom_repeatable_product_tabs_tab_content_field _yikes_wc_custom_repeatable_product_tabs_tab_content_field_dynamic' );

			// Initialize quicktags (for working in 'Text tab' mode)
			quicktags( { id: textarea_id } ); // buttons: 'strong,em,block,del,ul,ol,li,link,code,fullscreen' // currently do not work

			// Initialize tinymce
			if( typeof( tinymce ) != 'undefined' ) {
				tinymce.execCommand( 'mceAddEditor', false, textarea_id );	
			}
			
			return true;	
		});
	}

	/**
	* @summary Toggle disabling/hiding of UI buttons, this prevents potentially breaking the UI while AJAX is returning
	*
	* @since 1.5
	*
	* @param string | toggle_enable | 'disable' to disable/hide, 'enable' to enable/show
	*
	*/
	function yikes_woo_toggle_controls( toggle_enable ) {
		if ( toggle_enable === 'disable' ) {
			jQuery( '.remove_this_tab' ).attr('disabled', 'disabled');
			jQuery( '#add_another_tab' ).attr('disabled', 'disabled');
			jQuery( '.move-tab-data-up' ).hide();
			jQuery( '.move-tab-data-down' ).hide();
		} else {
			jQuery( '.remove_this_tab' ).removeAttr('disabled');
			jQuery( '#add_another_tab' ).removeAttr('disabled');
			jQuery( '.move-tab-data-up' ).show();
			jQuery( '.move-tab-data-down' ).show();		
		}
	}

	/**
	* @summary Display a simple error or success message
	*
	* @since 1.5
	*
	* @param string | anchor_element_id | the ID of the element we're going to display the message next to
	* @param string | message_element_id| the ID of the element we're creating to hold the message
	* @param string | message 			| the message we're displaying to the user
	* @param bool	| settings_page		| Flag indicating whether we're on the settings page (true) or product page (false)
	*
	*/
	function yikes_woo_display_feedback_messages( anchor_element_id, message_element_id, message, settings_page ) {
		//remove any other success / error message elements
		jQuery( '._yikes_wc_feedback_message' ).remove();

		var dynamic_message_elements = '';

		// Construct our message
		if ( settings_page === true ) {
			dynamic_message_elements = '<span id="' + message_element_id + '" class="_yikes_wc_feedback_message">' + message + '</span>';
		} else {
			dynamic_message_elements = '<p id="' + message_element_id + '" class="_yikes_wc_feedback_message"> <span>' + message + '</span> </p>';
		}	

		// Add our message to the DOM
		jQuery( '#' + anchor_element_id ).after( dynamic_message_elements );	
			
		// Display message by fadein/fadeout
		jQuery( '#' + message_element_id ).fadeIn( 500 ).delay( 3000 ).fadeOut( 500 );
	}

	/**
	* @summary Slide down / slide up our 'How To' info and swap dashicons
	*
	* @since 1.5
	*
	*/
	function yikes_woo_toggle_how_to() {
		jQuery( '.yikes-woo-tabs-hidden-how-to-info' ).slideToggle( 'fast', function() {
			if ( jQuery( '#yikes-woo-help-me-icon' ).hasClass( 'dashicons-editor-help' ) ) {
				jQuery( '#yikes-woo-help-me-icon' ).removeClass( 'dashicons-editor-help' ).addClass( 'dashicons-arrow-up' );
			} else {
				jQuery( '#yikes-woo-help-me-icon' ).removeClass( 'dashicons-arrow-up' ).addClass( 'dashicons-editor-help' );
			}	
		});
	}