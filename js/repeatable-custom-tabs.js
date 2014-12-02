/*
*	jQuery for repeatable woo commerce tabs
*	somewhat cool
*	YIKES Inc. / Evan Herman
*/
jQuery(document).ready(function() {
	
	/*
		Add a new tab
	*/
	jQuery( '#add_another_tab' ).on( 'click' , function( e ){
		
		var clone_container = jQuery( '#duplicate_this_row' );
		
		var before_add_count = jQuery('#number_of_tabs').val();
		
		var new_count = parseInt(jQuery('#number_of_tabs').val())+parseInt(1); /* get new number of cloned element */
		
		clone_container.children( 'p' ).each(function() {
			
			jQuery(this).clone().insertBefore( '#duplicate_this_row' ).removeClass('hidden_duplicator_row_title_field').removeClass('hidden_duplicator_row_content_field').addClass('new_duplicate_row');
		
		}).promise().done(function() {
			
			jQuery( '.new_duplicate_row' ).find('input').each(function() {
				if ( jQuery(this).is('input[name="hidden_duplicator_row_title"]') ) {
					jQuery(this).attr( 'name' , '_yikes_wc_custom_repeatable_product_tabs_tab_title_'+new_count ).attr( 'id' , '_yikes_wc_custom_repeatable_product_tabs_tab_title_'+new_count ).parents('p').addClass('_yikes_wc_custom_repeatable_product_tabs_tab_title_'+new_count+'_field').removeClass('hidden_duplicator_row_title_field').find('label').removeAttr('for').attr('for','_yikes_wc_custom_repeatable_product_tabs_tab_title_'+new_count+'_field');
				}
			});
			
			jQuery( '.new_duplicate_row' ).find('textarea').each(function() {
				if ( jQuery(this).is('textarea[name="hidden_duplicator_row_content"]') ) {
					jQuery(this).attr( 'name' , '_yikes_wc_custom_repeatable_product_tabs_tab_content_'+new_count ).attr( 'id' , '_yikes_wc_custom_repeatable_product_tabs_tab_content_'+new_count ).parents('p').addClass('_yikes_wc_custom_repeatable_product_tabs_tab_content_'+new_count+'_field').removeClass('hidden_duplicator_row_content_field').find('label').removeAttr('for').attr('for','_yikes_wc_custom_repeatable_product_tabs_tab_content_'+new_count+'_field');	
				}
			});
			
			jQuery( '#number_of_tabs' ).val(new_count);
			
			jQuery( '._yikes_wc_custom_repeatable_product_tabs_tab_content_'+before_add_count+'_field' ).after( '<div class="yikes-woo-custom-tab-divider"></div>' );
		
		});
		
		
		
		e.preventDefault();
		
	});
	// end duplicate
	
	/* 
		How To Click 
		- slide out display
	*/
	jQuery( '.yikes-tabs-how-to-toggle' ).on( 'click' , function( e ) {
		jQuery( '.yikes-woo-tabs-hidden-how-to-info' ).slideToggle('fast');
	});
	
	
});