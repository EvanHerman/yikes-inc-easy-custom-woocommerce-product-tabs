/*
*	jQuery for repeatable woo commerce tabs (on settings page)
*	somewhat cool / very cool
*	YIKES Inc. / Kevin Utz
*/
	jQuery(document).ready(function() {

		// Slide down / slide up our how-to info
		jQuery( '#yikes-woo-help-me-icon' ).click( function() {
			yikes_woo_toggle_how_to();
		});

		// Add a new tab (WYSIWYG editor)
		jQuery( '#yikes_woo_add_another_tab' ).click( function() {
			var tab_number = jQuery( '.yikes_woo_reusable_tabs_container' ).last().data( 'tab-number' );
			tab_number = ! isNaN( parseInt( tab_number ) ) ? parseInt( tab_number ) + 1 : 1;
			var textarea_id = 'yikes_woo_reusable_tab_content_' + tab_number;
			var tab_html = yikes_woo_create_new_tab_html( tab_number );

			// Add our HTML after the last <hr> if it exists
			if ( jQuery( '.yikes_woo_reusable_tabs_container' ).last().length ) {
				jQuery( '.yikes_woo_reusable_tabs_container' ).last().after( tab_html );
			} else {

				// If container does not exist, all tabs are deleted so we need to prepend the new content
				jQuery( '.yikes_woo_reusable_tabs' ).prepend( tab_html );
			}

			// Add a loading gif until AJAX returns
			jQuery( '.yikes_woo_add_another_tab_container' ).children( '.yikes_woo_add_another_tab' ).hide();
			jQuery( '.yikes_woo_add_another_tab_container' ).append( repeatable_custom_tabs_settings.loading_gif );

			// Call AJAX function to get the WYSIWYG
			yikes_woo_get_wp_editor_ajax( textarea_id, false );

		});

		// Save a tab
		jQuery( 'body' ).on( 'click' , '.yikes_woo_save_this_tab' , function() {
			var tab_number = jQuery( this ).data( 'tab-number' );
			var tab_id = jQuery( '#yikes_woo_reusable_tabs_container_' + tab_number ).data( 'tab-id' );

			yikes_woo_handle_saving_reusable_tab( tab_number, tab_id );
		});

		// Delete a saved tab
		jQuery( 'body' ).on( 'click' , '.yikes_woo_delete_this_tab' , function() {

			var tab_number = jQuery( this ).data( 'tab-number' );
			var tab_id = jQuery( '#yikes_woo_reusable_tabs_container_' + tab_number ).data( 'tab-id' );

			// If we don't have a tab_id, the tab was never saved so we don't need to go to the database. Just remove element.
			if ( typeof( tab_id ) === 'undefined' ) {

				if ( typeof( tinymce ) != 'undefined' ) {
					// destroy our tinymce instance (this enables us to re-add it later if we need to)
					tinymce.execCommand( 'mceRemoveEditor', false, 'yikes_woo_reusable_tab_content_' + tab_number );
				}

				jQuery( '#yikes_woo_reusable_tabs_container_' + tab_number ).fadeOut( '600', function() {
					jQuery( '#yikes_woo_reusable_tabs_container_' + tab_number ).remove();
					//jQuery( '.yikes_woo_reusable_tabs_hr_' + tab_number ).remove();
				});

				return;
			}

			// Confirm: Are you sure you want delete??

			yikes_woo_handle_deleting_reusable_tab( tab_number, tab_id );

		});
		
	}); // End document.ready

	/**
	* @summary Save a tab for re-use
	*
	* @since 1.5
	*
	* @param string	| tab_number	| the uniquely identifying suffix of the current tab
	* @param int	| tab_id		| the tab's unique ID in the database
	*
	*/
	function yikes_woo_handle_saving_reusable_tab( tab_number, tab_id ) {

		var tab_title = jQuery( '#yikes_woo_reusable_tab_title_' + tab_number ).val();
		var tab_content = '';

		if ( typeof( tinymce ) != 'undefined' && jQuery( '#wp-yikes_woo_reusable_tab_content_' + tab_number + '-wrap' ).hasClass( 'tmce-active' ) ) {
			tab_content = tinymce.get( 'yikes_woo_reusable_tab_content_' + tab_number ).getContent();
		} else {
			tab_content = jQuery( '#yikes_woo_reusable_tab_content_' + tab_number ).val();
		}

		//If no tab_title || tab_content, show error message
		if ( tab_title.length === 0 ) {
			yikes_woo_display_feedback_messages( 'yikes_woo_reusable_tab_delete_' + tab_number, 'yikes_woo_tab_error_message', 'Please fill out the tab title before saving.', true );
			return;
		}
		if ( tab_content.length === 0 ) {
			yikes_woo_display_feedback_messages( 'yikes_woo_reusable_tab_delete_' + tab_number, 'yikes_woo_tab_error_message', 'Please fill out the tab content before saving.', true );
			return;	
		}

		// Create data object
		var data = {
			'action': 'yikes_woo_save_tab_as_reusable',
			'tab_title': tab_title,
			'tab_content': tab_content,
			'tab_id': tab_id,
			'security_nonce': repeatable_custom_tabs_settings.save_tab_as_reusable_nonce
		};

		// AJAX
		jQuery.post( repeatable_custom_tabs_settings.ajaxurl, data, function( response ) { console.log(response);
			if ( typeof( response.success ) !== 'undefined' && response.success === true ) {
				yikes_woo_display_feedback_messages( 'yikes_woo_reusable_tab_delete_' + tab_number, 'tab_saved_success_message', 'Tab saved successfully.', true );

				// Add our tab_id back to the DOM
				if ( typeof( response.data ) !== 'undefined' && typeof( response.data.tab_id ) !== 'undefined' ) {
					var saved_tab_id = response.data.tab_id;
					jQuery( '#yikes_woo_reusable_tabs_container_' + tab_number ).attr( 'data-tab-id', saved_tab_id );
				}
			} else {
				if ( typeof( response.data ) !== 'undefined' && typeof( response.data.message ) !== 'undefined' ) {
					yikes_woo_display_feedback_messages( 'yikes_woo_reusable_tab_delete_' + tab_number, 'yikes_woo_tab_error_message', response.data.message, true );
				} else {

					// Ok, not sure what went wrong. Let's log it.
					console.log( response );
				}
			}
		});
	}

	/**
	* @summary Delete a saved tab
	*
	* @since 1.5
	*
	* @param string	| tab_number	| the uniquely identifying suffix of the current tab
	* @param int	| tab_id		| the tab's unique ID in the database
	*
	*/
	function yikes_woo_handle_deleting_reusable_tab( tab_number, tab_id ) {

		// Create data object
		var data = {
			action: 'yikes_woo_delete_reusable_tab',
			tab_id: tab_id,
			security_nonce: repeatable_custom_tabs_settings.delete_reusable_tab_nonce
		}

		// AJAX
		jQuery.post( repeatable_custom_tabs_settings.ajaxurl, data, function( response ) {
			if ( response.success ) {

				// Destroy our tinymce instance (this enables us to re-add it later if we need to)
				if ( typeof( tinymce ) != 'undefined' ) {
					tinymce.execCommand( 'mceRemoveEditor', false, 'yikes_woo_reusable_tab_content_' + tab_number );
				}

				// Remove the tab container
				jQuery( '#yikes_woo_reusable_tabs_container_' + tab_number ).fadeOut( '600', function() {
					jQuery( '#yikes_woo_reusable_tabs_container_' + tab_number ).remove();
				});
			} else {
				console.log( response );
			}
		});
	}

	/**
	* @summary Create new tab HTML
	*
	* @since 1.5
	*
	* @param string	| tab_number	| the uniquely identifying suffix of the current tab
	*
	* @return string HTML string for new tab
	*/
	function yikes_woo_create_new_tab_html( tab_number ) {
		var html = '';

		html += '<div class="row form-group yikes_woo_reusable_tabs_container" id="yikes_woo_reusable_tabs_container_' + tab_number + '" data-tab-number="' + tab_number + '">';
		
					// Title
		html += 	'<div class="yikes_woo_reusable_tab_title">';
		html +=			'<label class="yikes_woo_reusable_tab_title_label" for="yikes_woo_reusable_tab_title_' + tab_number + '">';
		html +=				'Tab Title: ';
		html +=			'</label>';
		html +=			'<input type="text" class="form-control" id="yikes_woo_reusable_tab_title_' + tab_number + '" value="" />';
		html +=		'</div>';

					// Content
		html +=		'<div class="yikes_woo_reusable_tab_content yikes_woo_reusable_tab_content_' + tab_number + '_field" >';
		html +=		'</div>';

					// Buttons
		html +=		'<div class="yikes_woo_reusable_tab_buttons">';
		html +=			'<span class="button-secondary yikes_woo_save_this_tab" id="yikes_woo_reusable_tab_save_' + tab_number + '" data-tab-number="' + tab_number + '">';
		html +=				'<i class="dashicons dashicons-star-filled inline-button-dashicons"></i>';
		html +=				'Save Tab';
		html +=			'</span>';
		html +=			'<span class="button-secondary yikes_woo_delete_this_tab" id="yikes_woo_reusable_tab_delete_' + tab_number + '" data-tab-number="' + tab_number + '">';
		html +=				'<i class="dashicons dashicons-dismiss inline-button-dashicons"></i>';
		html +=				'Delete Tab';
		html +=			'</span>';
		html +=		'</div>';
		html += '<hr class="yikes_woo_reusable_tabs_hr_' + tab_number + '">';
		html += '</div>';

		return html;
	}