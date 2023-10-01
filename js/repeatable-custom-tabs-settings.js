/*
*	jQuery for repeatable woo commerce tabs (on settings page)
*	somewhat cool / very cool
*	Kevin Utz
*/
	jQuery( document ).ready(function() {

		// Save a tab
		jQuery( '.yikes_woo_save_this_tab' ).click( function() {
			var tab_id = jQuery( this ).data( 'tab-id' );

			yikes_woo_handle_saving_reusable_tab( tab_id );
		});

		// Delete a saved tab
		jQuery( '.yikes_woo_delete_this_tab' ).click( function() {

			// Confirm: Are you sure?
			var continue_delete = confirm( repeatable_custom_tabs_settings.confirm_delete_single_tab ); 

			if ( continue_delete === false ) {
				return;
			}

			// Store the tab_id
			var tab_id = jQuery( this ).data( 'tab-id' );

			if ( jQuery( this ).hasClass( 'yikes_woo_delete_this_tab_single' ) ) {

				// If we're on the single page and trying to delete a new tab, then just redirect!
				if ( tab_id === 'new' ) {
					location.href = repeatable_custom_tabs_settings.tab_list_page_url;
				}
				yikes_woo_handle_deleting_reusable_tab( tab_id, 'single' );
			} else {
				yikes_woo_handle_deleting_reusable_tab( tab_id, false );
			}
		});

		// Handle bulk actions
		jQuery( '.yikes_woo_handle_bulk_action' ).click( function() {

			// Currently only supporting 'delete' bulk action, so make sure that is the value of the dropdown
			if ( jQuery( '#bulk-action-selector-top' ).val() !== 'delete' ) {
				return;
			}

			var continue_bulk_delete = confirm( repeatable_custom_tabs_settings.confirm_delete_bulk_tabs );

			if ( continue_bulk_delete === false ) {
				return;
			}

			// Set up an array of IDs to delete
			var tab_ids_to_delete = [];

			// Loop through all of the rows and check if checkbox is checked
			jQuery( '.entry-bulk-action-checkbox' ).each( function() { 
				if ( jQuery( this ).is( ':checked' ) === true ) {

					// Add tab_id to array
					tab_ids_to_delete.push( jQuery( this ).val() );

					// Add class to the row (we will target this class later to remove all rows)
					jQuery( this ).parents( 'th' ).parents( '.yikes_woo_saved_tabs_row' ).addClass( 'yikes_woo_bulk_delete_this_row' );
				}
			});
			
			// delete the tabs!
			yikes_woo_handle_deleting_reusable_tab( tab_ids_to_delete, 'bulk' );

		});
		
	}); // End document.ready

	/**
	* @summary Save a tab for re-use
	*
	* @since 1.5
	*
	* @param mixed | tab_id | the uniquely identifying suffix of the current tab ('new' for new tabs)
	*
	*/
	function yikes_woo_handle_saving_reusable_tab( tab_id ) {

		// Add loading spinner
		yikes_woo_display_feedback_messages( '#yikes_woo_delete_this_tab_' + tab_id, 'yikes_woo_tab_success_message', repeatable_custom_tabs_settings.loading_gif, {'time': 100000} );

		var tab_title   = jQuery( '#yikes_woo_reusable_tab_title_' + tab_id ).val();
		var tab_name    = jQuery( '#yikes_woo_reusable_tab_name_' + tab_id ).val();
		var tab_content = '';
		var taxonomies  = {};
		var global_tab  = false;

		if ( typeof( tinymce ) !== 'undefined' && jQuery( '#wp-yikes_woo_reusable_tab_content_' + tab_id + '-wrap' ).hasClass( 'tmce-active' ) ) {
			tab_content = tinymce.get( 'yikes_woo_reusable_tab_content_' + tab_id ).getContent();
		} else {
			tab_content = jQuery( '#yikes_woo_reusable_tab_content_' + tab_id ).val();
		}

		//If no tab_title || tab_content, show error message
		if ( tab_title.length === 0 ) {
			yikes_woo_display_feedback_messages( '#yikes_woo_delete_this_tab_' + tab_id, 'yikes_woo_tab_error_message', 'Please fill out the tab title before saving.', {} );
			return;
		}
		if ( tab_content.length === 0 ) {
			yikes_woo_display_feedback_messages( '#yikes_woo_delete_this_tab_' + tab_id, 'yikes_woo_tab_error_message', 'Please fill out the tab content before saving.', {} );
			return;	
		}

		// CPTPRO: Grab & format taxonomy data
		if ( jQuery( '.cptpro-taxonomies' ).length > 0 ) {

			// Create an object with taxonomy name => array( taxonomy_values )
			jQuery( '.taxonomy-label' ).each( function() {
				var taxonomy = jQuery( this ).data( 'taxonomy' );
				taxonomies[ taxonomy ] = {};

				console.log( taxonomy );

				// fuck.
				const data = jQuery( 'select[name="' + taxonomy + '[]"]' ).select2( 'data' );

				for ( var key in data ) {
					const obj = data[ key ];
					console.log( obj.element );
					taxonomies[ taxonomy ][ obj.id ] = obj.element.dataset['slug'];
				}

			} );

		}

		// CPTPRO: Grab global value
		if ( jQuery( '.global-section' ).length > 0 ) {
			global_tab = jQuery( '#global-checkbox' ).prop( 'checked' );
		}

		// Create data object
		var data = {
			'action'        : 'yikes_woo_save_tab_as_reusable',
			'tab_title'     : tab_title,
			'tab_content'   : tab_content,
			'tab_id'        : tab_id,
			'tab_name'      : tab_name,
			'taxonomies'    : taxonomies,
			'global_tab'    : global_tab,
			'security_nonce': repeatable_custom_tabs_settings.save_tab_as_reusable_nonce
		};

		// AJAX
		jQuery.post( repeatable_custom_tabs_settings.ajaxurl, data, function( response ) {
			if ( typeof( response.success ) !== 'undefined' && response.success === true ) {

				// If redirect var is set, redirect!
				if ( typeof( response.data ) !== 'undefined' && typeof( response.data.redirect ) !== 'undefined' 
						&& response.data.redirect === true && typeof( response.data.redirect_url !== 'undefined' ) ) {
					location.href = response.data.redirect_url;
				} else {
					yikes_woo_display_feedback_messages( '#yikes_woo_delete_this_tab_' + tab_id, 'yikes_woo_tab_success_message', 'Tab saved successfully.', {} );

					if ( repeatable_custom_tabs_settings.is_cptpro_enabled === '1' && typeof cptpro_show_products_using_this_tab === 'function' ) {
						cptpro_show_products_using_this_tab( tab_id );
					}
				}

				jQuery( '#yikes_woo_tab_title_header' ).text( tab_title );
			} else {
				if ( typeof( response.data ) !== 'undefined' && typeof( response.data.message ) !== 'undefined' ) {
					yikes_woo_display_feedback_messages( '#yikes_woo_delete_this_tab_' + tab_id, 'yikes_woo_tab_error_message', response.data.message, {} );
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
	* @param int	| tab_id		| the tab's unique ID in the database
	* @param string	| delete_method	| a string signifying what to do after deleting tabs
	*
	*/
	function yikes_woo_handle_deleting_reusable_tab( tab_id, delete_method ) {

		// Add a spinner...
		if ( delete_method === 'single' ) {
			yikes_woo_display_feedback_messages( '#yikes_woo_delete_this_tab_' + tab_id, 'yikes_woo_tab_success_message', repeatable_custom_tabs_settings.loading_gif, {'time': 100000} );
		}

		// Create data object
		var data = {
			action: 'yikes_woo_delete_reusable_tab_handler',
			tab_id: tab_id,
			security_nonce: repeatable_custom_tabs_settings.delete_reusable_tab_nonce
		}

		// AJAX
		jQuery.post( repeatable_custom_tabs_settings.ajaxurl, data, function( response ) {
			if ( response.success ) {

				if ( delete_method === 'bulk' ) {

					// fadeOut and remove all of the rows at once
					jQuery( '.yikes_woo_bulk_delete_this_row' ).fadeOut( '600', function() {
						jQuery( '.yikes_woo_bulk_delete_this_row' ).remove();
					});
				} else if ( delete_method === 'single' ) {

					// If we have a redirect URL, redirect back to tab list
					if ( typeof( response.data ) !== 'undefined' && typeof( response.data.redirect_url ) !== 'undefined' ) {
						location.href = response.data.redirect_url;
					}
				} else {

					// Remove the tab container
					jQuery( '#yikes_woo_saved_tabs_row_' + tab_id ).fadeOut( '600', function() {
						jQuery( '#yikes_woo_saved_tabs_row_' + tab_id ).remove();

						// Display our delete-success message
						jQuery( '#yikes_woo_delete_success_message' ).show();
					});
				}
			} else {

				// Ok, not sure what went wrong. Let's log it.
				console.log( response );
			}
		});
	}