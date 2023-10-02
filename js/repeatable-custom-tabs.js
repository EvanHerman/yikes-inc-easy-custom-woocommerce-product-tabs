/*
*	jQuery for repeatable woo commerce tabs (on product page)
*	somewhat cool / very cool
* Evan Herman / Kevin Utz
*/
	jQuery(document).ready(function() {
		// Disable fields on-load if necessary (delay 4 seconds to allow wp_editor to initialize)
		setTimeout(	function() {
			yikes_woo_check_for_reusable_tabs_and_disable();
			yikes_woo_set_editor_specific_styles();
		}, 4000 );

		// Slide down / slide up our how-to info
		jQuery( '.yikes-tabs-how-to-toggle' ).on( 'click' , function( e ) {
			yikes_woo_toggle_how_to();
		});

		// Add a new tab
		jQuery( '#add_another_tab' ).click( function( e ) {
			yikes_woo_add_another_tab( '' );
			e.preventDefault();
		});

		// Remove tab
		jQuery( 'body' ).on( 'click' , '.remove_this_tab' , function( e ) {

			// We remove the last tab and apply that content
			var clicked_button = jQuery( this );
			var clicked_position = clicked_button.parents( '.button-holder' ).attr( 'alt' );
			var last_post_position = jQuery( '#number_of_tabs' ).val();
			var tab_title_prefix	= '_yikes_wc_custom_repeatable_product_tabs_tab_title_';
			var tab_content_prefix	= '_yikes_wc_custom_repeatable_product_tabs_tab_content_';

			// If we're removing the last tab already, skip this step
			// If not, then swap all the content so the last tab is empty
			if ( clicked_position !== last_post_position ) {

				// Apply the content of the subsequent posts to the correct boxes
				var x = parseInt( clicked_position );
				while ( x < last_post_position ) {
					var tab_title = '';
					var tab_content = '';
					var next_tab_number = x + 1;

					// Switch tab title
					tab_title = jQuery( '#' + tab_title_prefix + next_tab_number ).val();
					jQuery( '#' + tab_title_prefix + x ).val( tab_title );

					tab_content = yikes_woo_get_content_from_wysiwyg( tab_content_prefix + next_tab_number );
					yikes_woo_set_content_for_wysiwyg( tab_content_prefix + x, tab_content );

					// Check global / reusable tab stuff

					// Switch hidden input fields
					var next_saved_tab_action = jQuery( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + next_tab_number + '_action' ).val();
					var next_saved_tab_id = jQuery( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + next_tab_number ).val();
					jQuery( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + x + '_action' ).val( next_saved_tab_action );
					jQuery( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + x ).val( next_saved_tab_id );

					// Check / Uncheck checkbox
					var next_override_checkbox = jQuery( '#_yikes_wc_override_reusable_tab_' + next_tab_number ).is( ':checked' );
					jQuery( '#_yikes_wc_override_reusable_tab_' + x ).prop( 'checked', next_override_checkbox );

					// Show / Hide checkbox container
					if ( jQuery( '#_yikes_wc_override_reusable_tab_container_' + next_tab_number ).is( ':visible' ) ) {
						jQuery( '#_yikes_wc_override_reusable_tab_container_' + x ).show();
					} else {
						jQuery( '#_yikes_wc_override_reusable_tab_container_' + x ).hide();
					}

					// Add / Remove disabled classes
					if ( jQuery( '._yikes_wc_custom_repeatable_product_tabs_tab_title_' + next_tab_number + '_field' ).hasClass( 'yikes_woo_using_reusable_tab' ) ) {
						yikes_woo_toggle_reusable_override_overlay( 'disable', x );
					} else {
						yikes_woo_toggle_reusable_override_overlay( 'enable', x );
					}

					x++;
				}
			}

			// Now remove the last tab (we always remove the last tab to prevent WYSIWYG errors)

			// Set up our id and name vars
			var tab_title_prefix	= '_yikes_wc_custom_repeatable_product_tabs_tab_title_';
			var tab_content_prefix	= '_yikes_wc_custom_repeatable_product_tabs_tab_content_';
			var removed_textarea_id = tab_content_prefix + last_post_position;

			// Set up our DOM elements to be removed
			var tab_reusable_container_to_remove = jQuery( '#_yikes_wc_override_reusable_tab_container_' + last_post_position );
			var tab_title_to_remove = jQuery( '.' + tab_title_prefix + last_post_position + '_field' );
			var tab_content_to_remove = jQuery( '.' + tab_content_prefix + last_post_position );
			var divider_to_remove = jQuery( '.yikes-woo-custom-tab-divider' ).last();
			var button_holder_to_remove = jQuery( '.button-holder[alt="' + last_post_position + '"]' );
			var number_of_tabs = parseInt( last_post_position ) - parseInt( 1 );

			// Destroy our tinymce instance (this enables us to re-add it later if we need to)
			if ( typeof( tinymce ) != 'undefined' ) {
				tinymce.execCommand( 'mceRemoveEditor', false, removed_textarea_id );
			}

			// Remove the DOM elements
			tab_reusable_container_to_remove.remove();
			tab_title_to_remove.remove();
			tab_content_to_remove.remove();
			jQuery( '.' + tab_content_prefix + last_post_position + '_field' ).remove();
			divider_to_remove.remove();
			button_holder_to_remove.remove();

			// Store the new number of tabs
			jQuery( '#number_of_tabs' ).val( number_of_tabs );

			// If we've removed all the tabs, let's add a class to the Add Another Tab button so we can style it
			if ( parseInt( number_of_tabs ) === 0 ) {
				jQuery( '#add_another_tab' ).parent( '.add_tabs_container' ).addClass( '_yikes_wc_add_tab_center' );
			}

			e.preventDefault();
		});

		// Move tab selected tab up, move above-tab below
		jQuery( 'body' ).on( 'click' , '.move-tab-data-up' , function() {
			var clicked_button = jQuery( this );
			var clicked_position = clicked_button.parents( '.button-holder' ).attr( 'alt' );

			// If we're trying to move the top tab, bail
			if ( clicked_position == 1 ) {
				return false;
			}

			// Set up name variables for succinctness
			var tab_title_prefix = '_yikes_wc_custom_repeatable_product_tabs_tab_title_';
			var tab_content_prefix = '_yikes_wc_custom_repeatable_product_tabs_tab_content_';

			// Store our clicked element variables
			var clicked_title = jQuery( '#' + tab_title_prefix + clicked_position ).val();
			var clicked_content = yikes_woo_get_content_from_wysiwyg( tab_content_prefix + clicked_position );

			// Store the previous element variables
			var previous_position = parseInt( clicked_position ) - parseInt( 1 );
			var previous_title = jQuery( '#' + tab_title_prefix + previous_position ).val();
			var previous_content = yikes_woo_get_content_from_wysiwyg( tab_content_prefix + previous_position );

			// Deal with saved tab disabled overlay classes
			if ( jQuery( '.' + tab_title_prefix + clicked_position + '_field' ).hasClass( 'yikes_woo_using_reusable_tab' )
					&& ! jQuery( '.' + tab_title_prefix + previous_position + '_field' ).hasClass( 'yikes_woo_using_reusable_tab' ) ) {

				// Add overlay to the previous tab && remove it from the current one
				yikes_woo_toggle_reusable_override_overlay( 'enable', clicked_position );
				yikes_woo_toggle_reusable_override_overlay( 'disable', previous_position );
			} else if ( jQuery( '.' + tab_title_prefix + previous_position + '_field' ).hasClass( 'yikes_woo_using_reusable_tab' )
					&& ! jQuery( '.' + tab_title_prefix + clicked_position + '_field' ).hasClass( 'yikes_woo_using_reusable_tab' ) ) {

				// Add overlay to the current tab && remove it from the previous one
				yikes_woo_toggle_reusable_override_overlay( 'disable', clicked_position );
				yikes_woo_toggle_reusable_override_overlay( 'enable', previous_position );
			}

			// Deal with saved tab hidden input fields
			var clicked_saved_tab_action = jQuery( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + clicked_position + '_action' ).val();
			var clicked_saved_tab_id = jQuery( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + clicked_position ).val();
			var previous_saved_tab_action = jQuery( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + previous_position + '_action' ).val();
			var previous_saved_tab_id = jQuery( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + previous_position ).val();
			jQuery( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + clicked_position + '_action' ).val( previous_saved_tab_action );
			jQuery( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + previous_position + '_action' ).val( clicked_saved_tab_action );
			jQuery( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + clicked_position ).val( previous_saved_tab_id );
			jQuery( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + previous_position ).val( clicked_saved_tab_id );

			// Deal with saved tab checkbox

			// Checked / Unchecked
			if ( jQuery( '#_yikes_wc_override_reusable_tab_' + clicked_position ).is( ':checked' ) === true
					&& jQuery( '#_yikes_wc_override_reusable_tab_' + previous_position ).is( ':checked' ) === false ) {
				jQuery( '#_yikes_wc_override_reusable_tab_' + previous_position ).prop( 'checked', true );
				jQuery( '#_yikes_wc_override_reusable_tab_' + clicked_position ).prop( 'checked', false );
			} else if ( jQuery( '#_yikes_wc_override_reusable_tab_' + clicked_position ).is( ':checked' ) === false
					&& jQuery( '#_yikes_wc_override_reusable_tab_' + previous_position ).is( ':checked' ) === true ) {
				jQuery( '#_yikes_wc_override_reusable_tab_' + previous_position ).prop( 'checked', false );
				jQuery( '#_yikes_wc_override_reusable_tab_' + clicked_position ).prop( 'checked', true );
			}

			// Shown / Hidden
			if ( jQuery( '#_yikes_wc_override_reusable_tab_container_' + clicked_position ).is( ':visible' ) === true
					&& jQuery( '#_yikes_wc_override_reusable_tab_container_' + previous_position ).is( ':visible' ) === false ) {
				jQuery( '#_yikes_wc_override_reusable_tab_container_' + previous_position ).show();
				jQuery( '#_yikes_wc_override_reusable_tab_container_' + clicked_position ).hide();
			} else if ( jQuery( '#_yikes_wc_override_reusable_tab_container_' + clicked_position ).is( ':visible' ) === false
					&& jQuery( '#_yikes_wc_override_reusable_tab_container_' + previous_position ).is( ':visible' ) === true ) {
				jQuery( '#_yikes_wc_override_reusable_tab_container_' + previous_position ).hide();
				jQuery( '#_yikes_wc_override_reusable_tab_container_' + clicked_position ).show();
			}

			// Swap title
			jQuery( '#' + tab_title_prefix + clicked_position ).val( previous_title );
			jQuery( '#' + tab_title_prefix + previous_position ).val( clicked_title );

			// Swap content
			yikes_woo_set_content_for_wysiwyg( tab_content_prefix + clicked_position, previous_content );
			yikes_woo_set_content_for_wysiwyg( tab_content_prefix + previous_position, clicked_content );
		});

		// Move tab selected tab down, move below-tab above
		jQuery( 'body' ).on( 'click' , '.move-tab-data-down' , function( ) {
			var clicked_button = jQuery( this );
			var clicked_position = clicked_button.parents( '.button-holder' ).attr( 'alt' );
			var number_of_tabs = jQuery( '#number_of_tabs' ).val();

			// If we're trying to move the bottom tab, bail
			if ( clicked_position == number_of_tabs ) {
				return false;
			}

			// Set up name variables for succinctness
			var tab_title_prefix = '_yikes_wc_custom_repeatable_product_tabs_tab_title_';
			var tab_content_prefix = '_yikes_wc_custom_repeatable_product_tabs_tab_content_';

			// Store our clicked element variables
			var clicked_title = jQuery( '#' + tab_title_prefix + clicked_position ).val();
			var clicked_content = yikes_woo_get_content_from_wysiwyg( tab_content_prefix + clicked_position );

			// Store the previous element variables
			var next_position = parseInt( clicked_position ) + parseInt( 1 );
			var next_title = jQuery( '#' + tab_title_prefix + next_position ).val();
			var next_content = yikes_woo_get_content_from_wysiwyg( tab_content_prefix + next_position );

			// Deal with saved tab disabled overlay classes
			if ( jQuery( '.' + tab_title_prefix + clicked_position + '_field' ).hasClass( 'yikes_woo_using_reusable_tab' )
					&& ! jQuery( '.' + tab_title_prefix + next_position + '_field' ).hasClass( 'yikes_woo_using_reusable_tab' ) ) {

				// Add overlay to the next tab && remove it from the current one
				yikes_woo_toggle_reusable_override_overlay( 'enable', clicked_position );
				yikes_woo_toggle_reusable_override_overlay( 'disable', next_position );
			} else if ( jQuery( '.' + tab_title_prefix + next_position + '_field' ).hasClass( 'yikes_woo_using_reusable_tab' )
					&& ! jQuery( '.' + tab_title_prefix + clicked_position + '_field' ).hasClass( 'yikes_woo_using_reusable_tab' ) ) {

				// Add overlay to the current tab && remove it from the next one
				yikes_woo_toggle_reusable_override_overlay( 'disable', clicked_position );
				yikes_woo_toggle_reusable_override_overlay( 'enable', next_position );
			}

			// Deal with saved tab hidden input fields
			var clicked_saved_tab_action = jQuery( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + clicked_position + '_action' ).val();
			var clicked_saved_tab_id = jQuery( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + clicked_position ).val();
			var next_saved_tab_action = jQuery( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + next_position + '_action' ).val();
			var next_saved_tab_id = jQuery( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + next_position ).val();
			jQuery( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + clicked_position + '_action' ).val( next_saved_tab_action );
			jQuery( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + next_position + '_action' ).val( clicked_saved_tab_action );
			jQuery( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + clicked_position ).val( next_saved_tab_id );
			jQuery( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + next_position ).val( clicked_saved_tab_id );

			// Deal with saved tab checkbox

			// Checked / Unchecked
			if ( jQuery( '#_yikes_wc_override_reusable_tab_' + clicked_position ).is( ':checked' ) === true
					&& jQuery( '#_yikes_wc_override_reusable_tab_' + next_position ).is( ':checked' ) === false ) {
				jQuery( '#_yikes_wc_override_reusable_tab_' + next_position ).prop( 'checked', true );
				jQuery( '#_yikes_wc_override_reusable_tab_' + clicked_position ).prop( 'checked', false );
			} else if ( jQuery( '#_yikes_wc_override_reusable_tab_' + clicked_position ).is( ':checked' ) === false
					&& jQuery( '#_yikes_wc_override_reusable_tab_' + next_position ).is( ':checked' ) === true ) {
				jQuery( '#_yikes_wc_override_reusable_tab_' + next_position ).prop( 'checked', false );
				jQuery( '#_yikes_wc_override_reusable_tab_' + clicked_position ).prop( 'checked', true );
			}

			// Shown / Hidden
			if ( jQuery( '#_yikes_wc_override_reusable_tab_container_' + clicked_position ).is( ':visible' ) === true
					&& jQuery( '#_yikes_wc_override_reusable_tab_container_' + next_position ).is( ':visible' ) === false ) {
				jQuery( '#_yikes_wc_override_reusable_tab_container_' + next_position ).show();
				jQuery( '#_yikes_wc_override_reusable_tab_container_' + clicked_position ).hide();
			} else if ( jQuery( '#_yikes_wc_override_reusable_tab_container_' + clicked_position ).is( ':visible' ) === false
					&& jQuery( '#_yikes_wc_override_reusable_tab_container_' + next_position ).is( ':visible' ) === true ) {
				jQuery( '#_yikes_wc_override_reusable_tab_container_' + next_position ).hide();
				jQuery( '#_yikes_wc_override_reusable_tab_container_' + clicked_position ).show();
			}


			// Swap titles
			jQuery( '#' + tab_title_prefix + clicked_position ).val( next_title );
			jQuery( '#' + tab_title_prefix + next_position ).val( clicked_title );

			// Swap content
			yikes_woo_set_content_for_wysiwyg( tab_content_prefix + clicked_position, next_content );
			yikes_woo_set_content_for_wysiwyg( tab_content_prefix + next_position, clicked_content );
		});

		// Show pop-up box with reusable tabs
		jQuery( 'body' ).on( 'click', '#_yikes_wc_apply_a_saved_tab', function() {

			// Disable button to prevent double-clicks
			if ( jQuery( this ).hasClass( 'disabled' ) ) {
				return false;
			}
			jQuery( this ).addClass( 'disabled' );

			yikes_woo_fetch_reusable_tabs( false, yikes_woo_handle_reusable_tabs );
		});

		// Handle clicking on a reusable tab in pop-up box
		jQuery( 'body' ).on( 'click', '.yikes_woo_saved_tab_selector_lity', function() {

			var saved_tab_number = jQuery( this ).data( 'saved-tab-number' );
			var tab_id = jQuery( '#saved_tab_container_' + saved_tab_number ).data( 'tab-id' );

			// Replace the lity box with a spinner because some tabs take a while to load
			jQuery( '.lity-content' ).html( repeatable_custom_tabs.loading_gif ).css( 'width', '50px' );

			yikes_woo_fetch_reusable_tab( tab_id, yikes_woo_apply_resuable_tab );
		});

		// Handle clicking of the 'Override Saved Tab' checkbox
		jQuery( 'body' ).on( 'click', '._yikes_wc_override_reusable_tab', function() {
			var tab_number = jQuery( this ).data( 'tab-number' );

			// Enable reusable tab override
			if ( jQuery( this ).is( ':checked' ) === true ) {

				// Show message explaining what this means
				yikes_woo_display_feedback_messages( '_yikes_wc_override_reusable_tab_container_' + tab_number, '_yikes_wc_override_reusable_tab_message', 'If you override this tab it will no longer recognize global tab changes.', false );

				// Set the hidden input field action to remove
				jQuery( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + tab_number + '_action' ).val( 'remove' );

				// Remove disabled overlay
				yikes_woo_toggle_reusable_override_overlay( 'enable', tab_number );
			} else {

				// Disable reusable tab override

				// Set the hidden input field action to add
				jQuery( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + tab_number + '_action' ).val( 'add' );

				// Add disabled overlay
				yikes_woo_toggle_reusable_override_overlay( 'disable', tab_number );
			}
		});

		// Handle two tabs with the same name by creating an overlay and showing an error message
		// To-do: make this function more robust - it doesn't handle multiple duplicates well
		jQuery( 'body' ).on( 'focusout', '.yikes_woo_tabs_title_field', function() {

			var current_element = jQuery( this );

			// If we have only one tab, don't bother
			if ( parseInt( jQuery("#number_of_tabs").val() ) === 1 ) {
				current_element.removeClass( '_yikes_wc_title_red_overlay' );
				current_element.parent( '.form-field' ).children( '._yikes_wc_duplicate_title_message' ).remove();
				return;
			}

			// Current element ID and value
			var current_element_id = jQuery( this ).attr( 'id' );
			var current_element_val = jQuery( this ).val();

			// Flag indicating whether we detected a duplicate
			var dupe_detected = false;

			// Loop through each title element
			jQuery( '.yikes_woo_tabs_title_field' ).each( function( index, element ) {

				// Make sure we're not looking at the current element (this) and that the element has a value
				if ( jQuery( element ).attr( 'id' ) != current_element_id && jQuery( element ).val() != '' ) {

					// If another title is the same as this one, add red overlay & message. Else, remove red overlay and message.
					if ( jQuery( element ).val() === current_element_val ) {
						current_element.addClass( '_yikes_wc_title_red_overlay' );
						current_element.parent( '.form-field' ).children( '._yikes_wc_duplicate_title_message' ).remove();
						current_element.parent( '.form-field' ).prepend( '<span class="_yikes_wc_duplicate_title_message"> Please choose a unique tab name - duplicate tab names can create errors </span>');
						dupe_detected = true;
					} else {

					}
				}
			});

			// If we didn't find a dupe, remove the overlay classes && message
			if ( dupe_detected === false ) {
				jQuery( '._yikes_wc_title_red_overlay' ).removeClass( '_yikes_wc_title_red_overlay' );
				jQuery( '._yikes_wc_duplicate_title_message' ).remove();
			}
		});

		// If lity popup box is closed, remove 'disabled' class from _yikes_wc_apply_a_saved_tab
		jQuery( 'body' ).on( 'lity:close', function() {
   			jQuery( '#_yikes_wc_apply_a_saved_tab' ).removeClass( 'disabled' );
		});

		// Handle saving all the tabs
		jQuery( '#yikes_woo_save_custom_tabs' ).click( function() {

			// If we've added the disabled class, do not go further
			if ( jQuery( '#yikes_woo_save_custom_tabs' ).hasClass( 'disabled' ) === true ) {
				return;
			}

			// Disable button until we get back from AJAX -- this helps prevent multiple button clicks
			jQuery( '#yikes_woo_save_custom_tabs' ).addClass( 'disabled' );

			// Fade out our feedback message...
			jQuery( '#yikes_woo_ajax_save_feedback' ).fadeOut();

			// Number of tabs
			var number_of_tabs = jQuery( '#number_of_tabs' ).val();

			// Create data object for AJAX call
			var data = {
				'action': 'yikes_woo_save_product_tabs',
				'post_id': repeatable_custom_tabs.global_post_id,
				'number_of_tabs': number_of_tabs,
				'security_nonce': repeatable_custom_tabs.save_product_tabs_nonce
			};

			console.log( data );

			// We're going to collect all the data and send it to the server as if it were a form submission
			// So data object should have all the relevant fields like: field_name => field_value
			for ( var ii = 1; ii <= number_of_tabs; ii++ ) {

				console.log( '_yikes_wc_custom_repeatable_product_tabs_tab_title_' + ii );

				// Title
				data['_yikes_wc_custom_repeatable_product_tabs_tab_title_' + ii] = jQuery( '#_yikes_wc_custom_repeatable_product_tabs_tab_title_' + ii ).val();

				// Content
				data['_yikes_wc_custom_repeatable_product_tabs_tab_content_' + ii] = yikes_woo_get_content_from_wysiwyg( '_yikes_wc_custom_repeatable_product_tabs_tab_content_' + ii );

				// Reusable tab id
				data['_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + ii] = jQuery( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + ii ).val();

				// Reusable tab action
				data['_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + ii + '_action'] = jQuery( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + ii + '_action' ).val();
			}

			jQuery.post( repeatable_custom_tabs.ajaxurl, data, function( response ) {
				var feedback_class = '';
				if ( typeof( response.success ) !== 'undefined' ) {
					if ( response.success === true ) {
						feedback_message_class = 'yikes_woo_save_success';
					} else if ( response.success === false ) {
						feedback_message_class = 'yikes_woo_save_failure';
					}
				}
				if ( typeof( response.data ) !== 'undefined' && typeof( response.data.message ) !== 'undefined' ) {
					jQuery( '#yikes_woo_ajax_save_feedback' ).removeClass().addClass( feedback_message_class ).text( response.data.message ).fadeIn().delay( '2000' ).fadeOut();
				}

				// Remove disabled class
				jQuery( '#yikes_woo_save_custom_tabs' ).removeClass( 'disabled' );
			});

		});
	}); // End document.ready


	function yikes_woo_apply_resuable_tab( response ) {

		var tab_data = response.data;

		// Store some necessary variables
		var tab_title_prefix = '_yikes_wc_custom_repeatable_product_tabs_tab_title_';
		var saved_tab_title = tab_data.tab_title;
		var saved_tab_id = tab_data.tab_id;
		var saved_tab_content = tab_data.tab_content;

		// Create another tab
		yikes_woo_add_another_tab( saved_tab_content );

		// Tab number of our created tab will be equal to the number of tabs
		var tab_number = jQuery( '#number_of_tabs' ).val();

		// Apply variables

		// Title
		jQuery( '#' + tab_title_prefix + tab_number ).val( saved_tab_title );

		// Hidden input fields for (1) tab_id (2) action (add / remove / none)
		jQuery( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + tab_number + '_action' ).val( 'add' );
		jQuery( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + tab_number ).val( saved_tab_id );


		// Check if our tinymce instance has been initialized yet
		if ( jQuery( '#qt__yikes_wc_custom_repeatable_product_tabs_tab_content_' + tab_number + '_toolbar' ).length > 0 ) {

			// Add a class to the items to indicate this is a reusable tab
			yikes_woo_toggle_reusable_override_overlay( 'disable', tab_number )
		} else {

			// If our tinymce instance is not initialized, let's set a flag so we know to do it
			jQuery( '#' + tab_title_prefix + tab_number ).addClass( 'yikes_woo_disable_this_tab' );
		}

		// Show checkbox to let the user override the reusable tab status
		jQuery( '#_yikes_wc_override_reusable_tab_container_' + tab_number ).show();
	}

	function yikes_woo_fetch_reusable_tab( tab_id, callback_function ) {

		// Create data object for AJAX call
		var data = {
			'action': 'yikes_woo_fetch_reusable_tab',
			'tab_id': tab_id,
			'security_nonce': repeatable_custom_tabs.fetch_reusable_tab_nonce
		};

		// AJAX
		jQuery.post( repeatable_custom_tabs.ajaxurl, data, function( response ) {

			// Close the lity modal
			global_lity.close();

			callback_function( response );
		});

	}

	function yikes_woo_fetch_reusable_tabs( fetch_tab_content, callback_function ) {

		// Create data object for AJAX call
		var data = {
			'action': 'yikes_woo_fetch_reusable_tabs',
			'fetch_tab_content': fetch_tab_content,
			'security_nonce': repeatable_custom_tabs.fetch_reusable_tabs_nonce
		};

		// AJAX
		jQuery.post( repeatable_custom_tabs.ajaxurl, data, function( response ) {
			callback_function( response );
		});

	}

	/**
	* @summary Fetch the reusable tabs and display them in a lity box
	*
	* @since 1.5
	*
	*/
	function yikes_woo_handle_reusable_tabs( fetch_tabs_response ) {

		if ( typeof( fetch_tabs_response.success ) !== 'undefined' && fetch_tabs_response.success === true ) {

			// If we have a message, no tabs were found
			if ( typeof( fetch_tabs_response.data ) !== 'undefined' && typeof( fetch_tabs_response.data.message ) !== 'undefined' ) {
				jQuery( '#yikes_woo_ajax_save_feedback' ).removeClass().addClass( 'yikes_woo_save_success' ).text( fetch_tabs_response.data.message ).fadeIn().delay( '2000' ).fadeOut();
			} else {
				// Save response data
				var saved_tabs = JSON.parse( fetch_tabs_response.data );

				// Create HTML from response data
				lity_html = create_lity_manage_reusable_tabs_html( saved_tabs );

				// Display lity box
				global_lity = lity( lity_html, { handler: 'inline' } );

				jQuery( '.lity-opened' ).addClass( 'custom-product-tabs select-tab' );
			}

		} else if (  typeof( fetch_tabs_response.success ) !== 'undefined' && fetch_tabs_response.success === false ) {

			// If we failed, log it
			console.log( response );
		}

		// Re-enable 'Apply a Saved Tab' Button
		jQuery( '#_yikes_wc_apply_a_saved_tab' ).removeClass( 'disabled' );
	}

	/**
	* @summary Create HTML for selected a reusable tab
	*
	* @since 1.5
	*
	* @param object	| tabs		| JSON object w/ tab title, content, and ID
	*
	* @return string lity_html
	*/
	function create_lity_manage_reusable_tabs_html( tabs ) {
		var ii = 1;
		var lity_html = '';

		lity_html += 	'<div class="display_saved_tabs_lity">';
		lity_html +=		'<div class="yikes_wc_lity_header">';
		lity_html +=			'<span> Choose a Tab </span>';
		lity_html +=		'</div>';

		jQuery.each( tabs, function( index, tab_data ) {
			lity_html += '<div id="saved_tab_container_' + ii + '" data-tab-id="' + tab_data.tab_id + '">';
			lity_html +=	'<div class="yikes_wc_lity_col_title">';
			lity_html += 		'<span class="yikes_woo_saved_tab_title_lity" id="yikes_woo_saved_tab_title_' + ii + '">';
			lity_html += 			tab_data.tab_title;
			lity_html += 		'</span>';
			lity_html += 		'<span class="yikes_woo_saved_tab_name_lity" id="yikes_woo_saved_tab_name_' + ii + '">';
			lity_html += 			typeof tab_data.tab_name !== 'undefined' ? ' - ' + tab_data.tab_name : '';
			lity_html += 		'</span>';
			lity_html +=	'</div>';
			lity_html +=	'<div class="yikes_wc_lity_col_select">';
			lity_html += 		'<span class="yikes_woo_saved_tab_selector_lity dashicons dashicons-plus-alt" data-saved-tab-number="' + ii + '"></span>';
			lity_html +=	'</div>';
			lity_html += '</div>';

			ii++;
		});

		lity_html += '</div>';

		return lity_html;
	}

	/**
	* @summary Add / Remove an overlay to a tab
	*
	* @since 1.5
	*
	* @param string | toggle_enable | 'disable' to add overlay, 'enable' to remove
	* @param string	| tab_number	| the uniquely identifying suffix of the current tab
	*
	*/
	function yikes_woo_toggle_reusable_override_overlay( toggle_enable, tab_number ) {
		if ( toggle_enable === 'disable' ) {

			// Page-loaded content box (WYSIWYG) fields

			// Text tab toolbar
			jQuery( '#qt__yikes_wc_custom_repeatable_product_tabs_tab_content_' + tab_number + '_toolbar' ).addClass( 'yikes_woo_using_reusable_tab' );

			// Text tab textarea
			jQuery( '#_yikes_wc_custom_repeatable_product_tabs_tab_content_' + tab_number ).addClass( 'yikes_woo_using_reusable_tab' );

			// Add Media button
			jQuery( '#wp-_yikes_wc_custom_repeatable_product_tabs_tab_content_' + tab_number + '-media-buttons' ).addClass( 'yikes_woo_using_reusable_tab' );

			// Visual tab toolbar
			jQuery( '#wp-_yikes_wc_custom_repeatable_product_tabs_tab_content_' + tab_number + '-editor-container' )
				.children( '.mce-container' ).children( '.mce-container-body' ).children( '.mce-toolbar-grp').addClass( 'yikes_woo_using_reusable_tab' );

			// Visual textarea
			jQuery( '#wp-_yikes_wc_custom_repeatable_product_tabs_tab_content_' + tab_number + '-editor-container' )
				.children( '.mce-tinymce' ).children( '.mce-container-body' ).children( '.mce-edit-area').addClass( 'yikes_woo_using_reusable_tab' );

			// Title
			jQuery( '._yikes_wc_custom_repeatable_product_tabs_tab_title_' + tab_number + '_field' ).addClass( 'yikes_woo_using_reusable_tab' );

		} else {

			// Text tab toolbar
			jQuery( '#qt__yikes_wc_custom_repeatable_product_tabs_tab_content_' + tab_number + '_toolbar' ).removeClass( 'yikes_woo_using_reusable_tab' );

			// Text tab textarea
			jQuery( '#_yikes_wc_custom_repeatable_product_tabs_tab_content_' + tab_number ).removeClass( 'yikes_woo_using_reusable_tab' );

			// Add Media button
			jQuery( '#wp-_yikes_wc_custom_repeatable_product_tabs_tab_content_' + tab_number + '-media-buttons' ).removeClass( 'yikes_woo_using_reusable_tab' );

			// Visual tab toolbar
			jQuery( '#wp-_yikes_wc_custom_repeatable_product_tabs_tab_content_' + tab_number + '-editor-container' )
				.children( '.mce-container' ).children( '.mce-container-body' ).children( '.mce-toolbar-grp').removeClass( 'yikes_woo_using_reusable_tab' );

			// Visual textarea
			jQuery( '#wp-_yikes_wc_custom_repeatable_product_tabs_tab_content_' + tab_number + '-editor-container' )
				.children( '.mce-tinymce' ).children( '.mce-container-body' ).children( '.mce-edit-area').removeClass( 'yikes_woo_using_reusable_tab' );

			// Title
			jQuery( '._yikes_wc_custom_repeatable_product_tabs_tab_title_' + tab_number + '_field' ).removeClass( 'yikes_woo_using_reusable_tab' );

		}
	}

	/**
	* @summary Run through each tab, check if reusable and add disabled overlay
	*
	* @since 1.5
	*
	*/
	function yikes_woo_check_for_reusable_tabs_and_disable() {

		// If we find the reusable-tab data attribute on .yikes_wc_override_reusable_tab_container, we're dealing with a reusable tab so disable relevant fields
		jQuery( '.yikes_wc_override_reusable_tab_container' ).each( function() {
			if ( jQuery( this ).data( 'reusable-tab' ) == true ) {
				var tab_number = jQuery( this ).children( '._yikes_wc_override_reusable_tab' ).data( 'tab-number' );
				yikes_woo_toggle_reusable_override_overlay( 'disable', tab_number );
			}
		});
	}

	/**
	* Duplicate and transform hidden HTML to create a new tab
	*
	* @param string | tab_content | content to pre-populate the editor with
	*/
	function yikes_woo_add_another_tab( tab_content ) {
		// Disable buttons/arrows to help prevent wp_editor errors
		yikes_woo_toggle_controls( 'disable' );

		// Remove our ._yikes_wc_add_tab_center_new class from the Save button (this is added when there are no products on-load)
		jQuery( '._yikes_wc_add_tab_center_new' ).removeClass( '_yikes_wc_add_tab_center_new' );

		// Remove our center class
		jQuery( '#add_another_tab' ).parent( '.add_tabs_container' ).removeClass( '_yikes_wc_add_tab_center' );

		// Setup variables for use in generating a new tab
		var clone_container = jQuery( '#duplicate_this_row' );
		var new_count = parseInt( jQuery( '#number_of_tabs' ).val() ) + parseInt( 1 ); /* get new number of cloned element */
		var move_tab_content_buttons = jQuery( '#duplicate_this_row .button-holder' );
		var textarea_id = '_yikes_wc_custom_repeatable_product_tabs_tab_content_' + new_count;
		var title_id = '_yikes_wc_custom_repeatable_product_tabs_tab_title_' + new_count;

		// Clone our hidden elements and change some classes and attributes
		clone_container.children( '.hidden_duplicator_row_title_field, .hidden_duplicator_row_content_field, .hidden_duplicator_row_button_holder' ).each( function() {
			jQuery(this).clone().insertBefore('#duplicate_this_row').removeClass( 'hidden_duplicator_row_title_field hidden_duplicator_row_content_field hidden_duplicator_row_button_holder' ).addClass( 'new_duplicate_row' );
		}).promise().done( function() {

			// Change title classes
			jQuery( '.new_duplicate_row' ).find( 'input' ).each( function() {
				if ( jQuery( this ).is( 'input[name="hidden_duplicator_row_title"]' ) ) {
					jQuery( this ).attr( 'name' , title_id ).removeClass( 'yikes_woo_tabs_title_field_duplicate' ).attr( 'id' , title_id ).parents( 'p' ).addClass( title_id + '_field' )
									.removeClass( 'hidden_duplicator_row_title_field' ).find( 'label' ).removeAttr( 'for' ).attr( 'for', title_id + '_field' );
				}
			});

			// Change content classes
			jQuery( '.new_duplicate_row' ).find( 'textarea' ).each( function() {
				if ( jQuery( this ).is( 'textarea[name="hidden_duplicator_row_content"]' ) ) {
					jQuery( this ).parent( '.form-field-tinymce' ).addClass( '_yikes_wc_custom_repeatable_product_tabs_tab_content_field ' + textarea_id );
					jQuery( this ).attr( 'name' , textarea_id ).attr( 'id' , textarea_id ).parent( 'div' ).addClass( textarea_id + '_field' ).removeClass( 'hidden_duplicator_row_content_field' )
								.find( 'label' ).removeAttr( 'for' ).attr( 'for', textarea_id + '_field' );
				}
			});

			// Change button holder classes
			jQuery( '.new_duplicate_row.button-holder' ).attr( 'alt', new_count );
			jQuery( '.new_duplicate_row' ).find( '.yikes_wc_override_reusable_tab_container' ).attr( 'id', '_yikes_wc_override_reusable_tab_container_' + new_count );

			// Set the new number of tabs value
			jQuery( '#number_of_tabs' ).val( new_count );

			// Append the divider between tabs
			if ( new_count > 1 ) {
				jQuery( '.new_duplicate_row' ).first().before( '<div class="yikes-woo-custom-tab-divider"></div>' );
			}
		});

		// Change reusable/saved tab classes
		var reusable_tab_container = jQuery( '.last-button-holder' ).children( '.yikes_wc_override_reusable_tab_container' );

		reusable_tab_container.removeClass( '_yikes_wc_override_reusable_tab_container_duplicate' ).children( '#_yikes_wc_override_reusable_tab_duplicate' )
										.attr( 'id', '_yikes_wc_override_reusable_tab_' + new_count ).attr( 'data-tab-number', new_count );
		// Change override label
		reusable_tab_container.children( '._yikes_wc_override_reusable_tab_label_duplicate' ).attr( 'for', '_yikes_wc_override_reusable_tab_' + new_count ).attr( 'id', '_yikes_wc_override_reusable_tab_label_' + new_count )
										.removeClass( '_yikes_wc_override_reusable_tab_label_duplicate' ).addClass( '_yikes_wc_override_reusable_tab_label' );

		// Change hidden input action field
		reusable_tab_container.children( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_action_duplicate' )
										.attr( 'name', '_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + new_count + '_action' )
										.attr( 'id', '_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + new_count + '_action' );
		// Change hidden input tab_id field
		reusable_tab_container.children( '#_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_duplicate' )
										.attr( 'name', '_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + new_count )
										.attr( 'id', '_yikes_wc_custom_repeatable_product_tabs_saved_tab_id_' + new_count );

		// Change container
		jQuery( '#_yikes_wc_override_reusable_tab_container_duplicate' ).first().attr( 'id', '_yikes_wc_override_reusable_tab_container_' + new_count );

		// Retrieve wp_editor HTML, the method to retrieve is dependent on WP version because WP4.8 introduced some new editor methods
		if ( parseInt( repeatable_custom_tabs.wp_version_four_eight ) === 1 ) {
			yikes_woo_get_wp_editor_foureight( textarea_id, true, tab_content );
		} else {

			// Add a loading gif until AJAX returns
			jQuery( '.' + textarea_id + '_field' ).html( repeatable_custom_tabs.loading_gif );

			yikes_woo_get_wp_editor_ajax( textarea_id, true, tab_content );
		}

		jQuery( '#duplicate_this_row' ).find( 'input[type="hidden"]' ).removeAttr( 'name' );

		// Remove some classes
		jQuery( '.last-button-holder' ).removeClass( 'last-button-holder' );
		jQuery( '.new_duplicate_row' ).removeClass( 'new_duplicate_row' );
	}

	function yikes_woo_set_editor_specific_styles() {
		jQuery( 'textarea[name^="_yikes_wc_custom_repeatable_product_tabs_tab_content_"]' ).each( function() {
			jQuery( this ).addClass( 'yikes_woo_custom_editor_styles' );
		});
	}
