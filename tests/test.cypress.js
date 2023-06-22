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
} );