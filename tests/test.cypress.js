import 'cypress-wait-until';

Cypress.Commands.add('setTinyMceContent', (index, tinyMceId, content) => {
	cy.window().then(win => {
		console.log( win );
		cy.waitUntil(() => win.tinymce?.editors[index]?.initialized, {timeout: 10000}).then(() => {
			// Clear any pre-existing content in the editor
			const editor = win.tinymce.editors[index].setContent('');

			// The .type() command does not accept an empty string, so we simulate interaction with the field that leaves it empty.
			if (!content?.length) {
				content = '{selectall}{backspace}';
			}

			// Type the content to ensure all browser events are fired
			cy.get('#' + tinyMceId)
				.its('0.contentDocument').should('exist')
				.its('body').should('not.be.undefined')
				.then(cy.wrap)
				.type(content);
		});
	});
});

describe( 'Tests', () => {

	beforeEach(() => {
		cy.visit( Cypress.env( 'testURL' ) + '/wp-admin' )
		 .then( () => {
			cy.wait( 250 );

			cy.get( '#user_login' ).type( Cypress.env( 'wpUsername' ) );
			cy.get( '#user_pass' ).type( Cypress.env( 'wpPassword' ) );
			cy.get( '#wp-submit' ).click();
		} );
	} );

	it( 'Plugin activation notice shows when WooCommerce is not active.', () => {
		cy.visit( Cypress.env( 'testURL' ) + '/wp-admin/plugins.php' );

		// Deactivate WooCommerce to test our admin notice.
		cy.get( 'tr[data-slug="woocommerce"] .row-actions' ).then( ( $row ) => {
			if ( $row.find( '.deactivate' ).length > 0 ) {
				cy.get( 'tr[data-slug="woocommerce"] .row-actions span.deactivate a' ).click();
			}
		} );

		cy.get( 'tr[data-slug="yikes-inc-easy-custom-woocommerce-product-tabs"] .row-actions span:first-child' ).then( ( $btn ) => {
			if ( $btn.hasClass( 'activate' ) ) {
				cy.get( 'tr[data-slug="yikes-inc-easy-custom-woocommerce-product-tabs"] .row-actions span:first-child a' ).click();
				cy.get( 'div.error' ).contains( 'Please activate WooCommerce before activating Custom WooCommerce Product Tabs.' );
			}
		} );
	} );

	it( 'Plugin activates when WooCommerce is active.', () => {
		cy.visit( Cypress.env( 'testURL' ) + '/wp-admin/plugins.php' );

		cy.get( 'tr[data-slug="woocommerce"] .row-actions span:first-child' ).then( ( $btn ) => {
			if ( $btn.hasClass( 'activate' ) ) {
				cy.get( 'tr[data-slug="woocommerce"] .row-actions span:first-child a' ).click();
				cy.get( 'div#message' ).contains( 'Plugin activated.' );
			}
		} );

		cy.get( 'tr[data-slug="yikes-inc-easy-custom-woocommerce-product-tabs"] .row-actions span:first-child' ).then( ( $btn ) => {
			if ( $btn.hasClass( 'activate' ) ) {
				cy.get( 'tr[data-slug="yikes-inc-easy-custom-woocommerce-product-tabs"] .row-actions span:first-child a' ).click();
				cy.get( 'div#message' ).contains( 'Plugin activated.' );
			}
		} );
	} );

	it( 'Custom tab data shows on product.', () => {
		cy.visit( Cypress.env( 'testURL' ) + '/wp-admin/post-new.php?post_type=product' );

		cy.get( 'input[name="post_title"]' ).type( 'Test Product' );

		cy.setTinyMceContent( 0, 'content_ifr', 'This is the new content' );

		cy.get( 'li.yikes_wc_product_tabs_tab' ).click();

		cy.get( 'a#add_another_tab' ).click();

		cy.get( '._yikes_wc_custom_repeatable_product_tabs_tab_title_1_field  input.yikes_woo_tabs_title_field' ).type( 'Custom Tab' );
		cy.setTinyMceContent( 1, '_yikes_wc_custom_repeatable_product_tabs_tab_content_1_ifr', 'Custom tab content goes here.' );

		cy.get( '#publish' ).click();

		cy.get( '#message.updated.notice a' ).click();

		cy.get( '.wc-tabs li.custom-tab_tab' ).contains( 'Custom Tab' ).click();

		cy.get( '.yikes-custom-woo-tab-title' ).should( 'have.text', 'Custom Tab' );
		cy.get( '#tab-custom-tab' ).contains( 'Custom tab content goes here.' );
	} );

	it( 'Saved tabs applies to a product properly.', () => {
		// Create a new saved tab.
		cy.visit( Cypress.env( 'testURL' ) + '/wp-admin/admin.php?page=yikes-woo-settings&saved-tab-id=new' );

		cy.get( '#yikes_woo_reusable_tab_title_new' ).type( 'Saved Tab Title' );
		cy.get( '#yikes_woo_reusable_tab_name_new' ).type( 'Saved Tab Name (reference only)' );

		cy.setTinyMceContent( 0, 'yikes_woo_reusable_tab_content_new_ifr', 'Saved Tab Content Data.' );

		cy.get( '#yikes_woo_save_this_tab_new' ).click();

		cy.get( '#yikes_woo_tab_title_header' ).contains( 'Saved Tab Title' );

		// Confirm the data shows up properly in the dashboard.
		cy.visit( Cypress.env( 'testURL' ) + '/wp-admin/admin.php?page=yikes-woo-settings' );

		cy.get( '#yikes-woo-saved-tabs-list-tbody .yikes_woo_saved_tabs_row:first-child .column-title' ).contains( 'Saved Tab Title' );
		cy.get( '#yikes-woo-saved-tabs-list-tbody .yikes_woo_saved_tabs_row:first-child .column-name' ).contains( 'Saved Tab Name (reference only)' );
		cy.get( '#yikes-woo-saved-tabs-list-tbody .yikes_woo_saved_tabs_row:first-child .column-content' ).contains( 'Saved Tab Content Data.' );

		// Add the saved tab to a product.
		cy.visit( Cypress.env( 'testURL' ) + '/wp-admin/post-new.php?post_type=product' );

		cy.get( 'input[name="post_title"]' ).type( 'Saved Tab Product' );
		cy.setTinyMceContent( 0, 'content_ifr', 'Saved tab content test.' );

		cy.get( 'li.yikes_wc_product_tabs_tab' ).click();
		cy.get( '#_yikes_wc_apply_a_saved_tab' ).click();

		cy.get( '.display_saved_tabs_lity #saved_tab_container_1 .yikes_wc_lity_col_select' ).click();

		cy.get( '#publish' ).click();

		// Check from of site data matches what we added.
		cy.get( '#message.updated.notice a' ).click();

		cy.wait( 2000 );

		cy.get( '.wc-tabs li.saved-tab-title_tab' ).contains( 'Saved Tab Title' ).click();

		cy.get( '.yikes-custom-woo-tab-title' ).should( 'have.text', 'Saved Tab Title' );
		cy.get( '#tab-saved-tab-title' ).contains( 'Saved Tab Content Data.' );
	} );

	it( 'Saved tabs deletes properly', () => {
		cy.visit( Cypress.env( 'testURL' ) + '/wp-admin/admin.php?page=yikes-woo-settings' );

		cy.get( '#yikes-woo-saved-tabs-list-tbody .yikes_woo_saved_tabs_row:first-child .column-title' ).contains( 'Saved Tab Title' );
		cy.get( '#yikes-woo-saved-tabs-list-tbody .yikes_woo_saved_tabs_row:first-child .column-name' ).contains( 'Saved Tab Name (reference only)' );
		cy.get( '#yikes-woo-saved-tabs-list-tbody .yikes_woo_saved_tabs_row:first-child .column-content' ).contains( 'Saved Tab Content Data.' );

		cy.get( '#yikes-woo-saved-tabs-list-tbody .row-actions:first-child .yikes_woo_delete_this_tab' ).click( { force: true } );

		cy.on('window:confirm', (str) => {
			expect( str ).to.equal( 'Are you sure you want to delete this tab?' );
		} );

		cy.on( 'window:confirm', () => true );

		cy.get( '#yikes_woo_delete_success_message' ).contains( 'Tab deleted!' );

		cy.wait( 2000 );

		cy.reload();

		cy.get( '#yikes-woo-saved-tabs-list-tbody tr td strong' ).contains( 'There are no saved tabs. Add one!' );
	} );
} );