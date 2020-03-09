jQuery( document ).ready( function( $ ) {

    let toggleInput = $( '#yikes-woo-toggle-content-input' );
    let savBtn      = $( '#yikes-woo-toggle-content' );

    savBtn.click( 'click', yikesToggleTheContent );

    function yikesToggleTheContent( event ) {

        event.preventDefault();

        let isChecked = ( toggleInput.prop('checked') ) ? 'true' : 'false';
        
        $.ajax( {
            url: yikesCptSettings.root + 'yikes/cpt/v1/settings',
            method: 'POST',
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', yikesCptSettings.nonce );
            },
            data:{
                'toggle_the_content' : isChecked,
            }
        } ).success( function ( response ) {
            $("html, body").animate({ scrollTop: 0 }, "slow");
            $( '#settings-updated' ).css( 'display', 'block' )

            setTimeout( function() {
                $( '#settings-updated' ).fadeOut();
            }, 2000 );
            
        } )
        .fail(e => console.error(e));

    }

} );