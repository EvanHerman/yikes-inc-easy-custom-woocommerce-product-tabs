	/**
	* @summary Fetch the wp_editor HTML via AJAX.
	*
	* @since 1.5
	*
	* @param string | textarea_id  | ID of the textarea where we're initializing the WYSIWYG editor
	* @param bool	| product_page | bool indicating whether we're on the product page (true) or settings page (false)
	* @param string | tab_content  | String to pre-populate the editor with content
	*
	* @return bool
	*/
	function yikes_woo_get_wp_editor_ajax( textarea_id, product_page, tab_content ) {

		// Create data object for AJAX call
		var data = {
			'action': 'yikes_woo_get_wp_editor',
			'textarea_id': textarea_id,
			'tab_content': tab_content,
			'security_nonce': repeatable_custom_tabs_shared.get_wp_editor_security_nonce
		};

		// AJAX
		jQuery.post( repeatable_custom_tabs_shared.ajaxurl, data, function( response ) {

			// Re-enable buttons / arrows
			yikes_woo_toggle_controls( 'enable' );

			// If call failed, show error message
			if ( typeof( response.success ) !== 'undefined' && response.success === false ) {
				jQuery( '.' + textarea_id + '_field' ).html( '<p>' + repeatable_custom_tabs_shared.get_wp_editor_failure_message + '</p>' );

				return false;
			}

			// If we're on the button page, show the button holder (we temporarily hide it for UI/UX purposes)
			// if ( product_page === true ) {
			// 	jQuery( '.button-holder' ).show();
			// }

			// Add wp_editor HTML to the page
			jQuery( '.' + textarea_id + '_field' ).html( response ).addClass( '_yikes_wc_custom_repeatable_product_tabs_tab_content_field _yikes_wc_custom_repeatable_product_tabs_tab_content_field_dynamic' );

			// Initialize quicktags (for working in 'Text tab' mode)
			if ( typeof( QTags ) !== 'undefined' ) {
				quicktags( textarea_id );
				QTags._buttonsInit();
			}

			// These are WordPress default editor settings, retrieved from wp-includes\class-wp-editor.php
			// The `setup:` function is not part of the WordPress core, but the default styles were not being applied
			tinymce.init({
				branding: false,
				selector: '#' + textarea_id,
				theme: 'modern',
				skin: 'lightgray',
				language: 'en',
				formats: {
					alignleft: [
						{ selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: { textAlign:'left' } },
						{ selector: 'img,table,dl.wp-caption', classes: 'alignleft' }
					],
					aligncenter: [
						{ selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: { textAlign:'center' } },
						{ selector: 'img,table,dl.wp-caption', classes: 'aligncenter' }
					],
					alignright: [
						{ selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: { textAlign:'right' } },
						{ selector: 'img,table,dl.wp-caption', classes: 'alignright' }
					],
					strikethrough: { inline: 'del' }
				},
				relative_urls: false,
				remove_script_host: false,
				convert_urls: false,
				browser_spellcheck: true,
				fix_list_elements: true,
				entities: '38,amp,60,lt,62,gt',
				entity_encoding: 'raw',
				keep_styles: false,
				paste_webkit_styles: 'font-weight font-style color',
				preview_styles: 'font-family font-size font-weight font-style text-decoration text-transform',
				end_container_on_empty_block: true,
				wpeditimage_disable_captions: false,
				wpeditimage_html5_captions: true,
				plugins: 'charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wpdialogs,wptextpattern,wpview',
				resize: true,
				menubar: false,
				wpautop: true,
				indent: false,
				toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_adv',
				toolbar2: 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
				toolbar3: '',
				toolbar4: '',
				tabfocus_elements: ':prev,:next',
				body_class: 'id post-type-post post-status-publish post-format-standard',
				setup: function( editor ) {
					editor.on( 'init', function() {
						this.getBody().style.fontFamily = 'Georgia, "Times New Roman", "Bitstream Charter", Times, serif';
						this.getBody().style.fontSize = '16px';
						this.getBody().style.color = '#333';
					});
				}
			});

			// Initialize tinymce
			if( typeof( tinymce ) != 'undefined' ) {
				tinymce.execCommand( 'mceAddEditor', false, textarea_id );
			}

			// After tinymce is initialized, let's check if we need to disable the box (because it's a saved tab)
			var tab_number = yikes_woo_get_tab_number_from_id( textarea_id );
			if ( jQuery( '#_yikes_wc_custom_repeatable_product_tabs_tab_title_' + tab_number ).hasClass( 'yikes_woo_disable_this_tab' ) ) {
				jQuery( '#_yikes_wc_custom_repeatable_product_tabs_tab_title_' + tab_number ).removeClass( 'yikes_woo_disable_this_tab' );
				yikes_woo_toggle_reusable_override_overlay( 'disable', tab_number );
			}
			
			return true;	
		});
	}

	function yikes_woo_get_wp_editor_foureight( textarea_id, product_page, tab_content ) {

		if ( ! wp && ! wp.editor && ! wp.editor.initialize ) {
			yikes_woo_get_wp_editor_ajax( textarea_id, product_page, tab_content );
			return false;
		}

		// Re-enable buttons / arrows
		yikes_woo_toggle_controls( 'enable' );

		var settings = {
			tinymce: {
				branding: false,
				theme: 'modern',
				skin: 'lightgray',
				language: 'en',
				formats: {
					alignleft: [
						{ selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: { textAlign:'left' } },
						{ selector: 'img,table,dl.wp-caption', classes: 'alignleft' }
					],
					aligncenter: [
						{ selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: { textAlign:'center' } },
						{ selector: 'img,table,dl.wp-caption', classes: 'aligncenter' }
					],
					alignright: [
						{ selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: { textAlign:'right' } },
						{ selector: 'img,table,dl.wp-caption', classes: 'alignright' }
					],
					strikethrough: { inline: 'del' }
				},
				relative_urls: false,
				remove_script_host: false,
				convert_urls: false,
				browser_spellcheck: true,
				fix_list_elements: true,
				entities: '38,amp,60,lt,62,gt',
				entity_encoding: 'raw',
				keep_styles: false,
				paste_webkit_styles: 'font-weight font-style color',
				preview_styles: 'font-family font-size font-weight font-style text-decoration text-transform',
				end_container_on_empty_block: true,
				wpeditimage_disable_captions: false,
				wpeditimage_html5_captions: true,
				plugins: 'charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wpdialogs,wptextpattern,wpview',
				menubar: false,
				wpautop: true,
				indent: false,
				resize: true,
				theme_advanced_resizing: true,
				theme_advanced_resize_horizontal: false,
				statusbar: true,
				toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_adv',
				toolbar2: 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
				toolbar3: '',
				toolbar4: '',
				tabfocus_elements: ':prev,:next',
				// width: '100%',
				// body_class: 'id post-type-post post-status-publish post-format-standard',
				setup: function( editor ) {
					editor.on( 'init', function() {
						this.getBody().style.fontFamily = 'Georgia, "Times New Roman", "Bitstream Charter", Times, serif';
						this.getBody().style.fontSize = '16px';
						this.getBody().style.color = '#333';
						if ( tab_content.length > 0 ) {
							this.setContent( tab_content );
						}
					});
				},
			},
			quicktags: {
				buttons:"strong,em,link,block,del,ins,img,ul,ol,li,code,more,close"
			}
		}

		wp.editor.initialize( textarea_id, settings );

		// If we're on the button page, show the button holder (we temporarily hide it for UI/UX purposes)
		// if ( product_page === true ) {
		// 	jQuery( '.button-holder' ).show();
		// }

		// After tinymce is initialized, let's check if we need to disable the box (because it's a saved tab)
		var tab_number = yikes_woo_get_tab_number_from_id( textarea_id );
		if ( jQuery( '#_yikes_wc_custom_repeatable_product_tabs_tab_title_' + tab_number ).hasClass( 'yikes_woo_disable_this_tab' ) ) {
			jQuery( '#_yikes_wc_custom_repeatable_product_tabs_tab_title_' + tab_number ).removeClass( 'yikes_woo_disable_this_tab' );
			yikes_woo_toggle_reusable_override_overlay( 'disable', tab_number );
		}

		// Add an 'Add Media' button
		// The plugin is included with the instantiation of the editor but there is no HTML button to trigger the associated functions
		var add_media_button = '<div id="wp-' + textarea_id + '-media-buttons" class="wp-media-buttons"> \
			<button type="button" id="insert-media-button" class="button insert-media add_media" data-editor="' + textarea_id + '"><span class="wp-media-buttons-icon"></span> Add Media</button>\
		</div>';
		jQuery( '#wp-_yikes_wc_custom_repeatable_product_tabs_tab_content_' + tab_number + '-wrap > .wp-editor-tools' ).prepend( add_media_button );
		
		return true;
	}

	/**
	* @summary Get the numerical ID from a string ID
	*
	* @param string  | id_string  | an id of the form this_is_an_id_10
	* @return string | tab_number | the numerical suffix of the string id passed in
	*/ 
	function yikes_woo_get_tab_number_from_id( id_string ) {
		return id_string.slice( ( id_string.lastIndexOf( '_' ) + 1 ) );
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
			jQuery( '.remove_this_tab' ).attr( 'disabled', 'disabled' );
			jQuery( '#add_another_tab' ).attr( 'disabled', 'disabled' );
			jQuery( '._yikes_wc_apply_a_saved_tab' ).attr( 'disabled', 'disabled' );
			jQuery( '.move-tab-data-up' ).hide();
			jQuery( '.move-tab-data-down' ).hide();
		} else {
			jQuery( '.remove_this_tab' ).removeAttr( 'disabled' );
			jQuery( '#add_another_tab' ).removeAttr( 'disabled' );
			jQuery( '._yikes_wc_apply_a_saved_tab' ).removeAttr( 'disabled' )
			jQuery( '.move-tab-data-up' ).show();
			jQuery( '.move-tab-data-down' ).show();		
		}
	}

	/**
	* @summary Display a simple error or success message
	*
	* @since 1.5
	*
	* @param string | anchor_element	| a selector for an element we're going to display the message next to
	* @param string | message_element_id| the ID of the element we're creating to hold the message
	* @param string | message 			| the message we're displaying to the user
	* @param object	| options			| Collection of options for customizing the error message
	*
	*/
	function yikes_woo_display_feedback_messages( anchor_element_id, message_element_id, message, options ) {

		var defaults = {
			'inline': false,
			'classes': [],
			'css_string': '',
			'time': 3000
		}

		var opts = jQuery.extend( defaults, options );

		//remove any other success / error message elements
		jQuery( '._yikes_wc_feedback_message' ).remove();

		// Construct style string
		var style_string = '';
		if ( opts.css_string.length > 0 ) {
		 	style_string = opts.css_string;
		}

		// Construct class string
		var classes = defaults.classes;
		if ( opts.classes.length > 0 ) {
			opts.classes.each( function( index, class_name ) {
				classes += class_name;
			});
		}

		var dynamic_message_elements = '';

		// Construct our message
		if ( opts.inline === true ) {
			dynamic_message_elements = '<span id="' + message_element_id + '" class="_yikes_wc_feedback_message ' + classes + '" style="' + style_string + '">' + message + '</span>';
		} else {
			dynamic_message_elements = '<p id="' + message_element_id + '" class="_yikes_wc_feedback_message ' + classes + '" style="' + style_string + '">' + message + '</p>';
		}

		// Add our message to the DOM
		jQuery( anchor_element_id ).after( dynamic_message_elements );	
			
		// Display message by fadein/fadeout
		jQuery( '#' + message_element_id ).fadeIn( 500 ).delay( opts.time ).fadeOut( 500 );
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
				jQuery( '#yikes-woo-help-me-icon' ).removeClass( 'dashicons-editor-help' ).addClass( 'dashicons-dismiss' );
			} else {
				jQuery( '#yikes-woo-help-me-icon' ).removeClass( 'dashicons-dismiss' ).addClass( 'dashicons-editor-help' );
			}	
		});
	}

	/**
	* Retrieve the content from the wp_editor
	*
	* @since 1.5
	*
	* @param  string | editor_id | the id of the editor (without the prefacing #)
	* @return string | content   | the content of the editor
	*/
	function yikes_woo_get_content_from_wysiwyg( editor_id ) {

		var content = '';

		// Check if tinymce is initialized, and if our instance is known
		if ( tinymce !== 'undefined' && tinymce.get( editor_id ) !== null ) {

			// Store the content
			content = tinymce.get( editor_id ).getContent();

			// If we don't have any content, check the textarea for a value and use it
			if ( content.length === 0 && jQuery( '#' + editor_id ).val().length > 0 ) {
				content = jQuery( '#' + editor_id ).val();
			}
		} else {

			// If tinymce is not initialized, try getting the content from the textarea value
			content = jQuery( '#' + editor_id ).val();
		}

		return content;
	}

	/**
	* Set the content for the wp_editor
	*
	* @since 1.5
	*
	* @param  string | editor_id | the id of the editor (without the prefacing #)
	* @param  string | content	 | the content to supply the editor with
	*/
	function yikes_woo_set_content_for_wysiwyg( editor_id, content ) {
		
		// Check if tinymce is initialized, and if our instance is known
		if ( tinymce !== 'undefined' && tinymce.get( editor_id ) !== null ) {

			// If it's initialized, we can just set the content from here using setContent()
			tinymce.get( editor_id ).setContent( content );

			// tinyMCE stores the value in both places, so we need to set the textarea content from here too
			jQuery( '#' + editor_id ).val( content );
		} else {

			// Else we need to set the value using the textarea's val
			jQuery( '#' + editor_id ).val( content );
		}
	}





