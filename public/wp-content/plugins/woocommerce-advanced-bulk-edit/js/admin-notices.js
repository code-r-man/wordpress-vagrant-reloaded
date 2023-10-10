jQuery( document ).ready( function() {

    jQuery( document ).on( 'click', '.my-dismiss-notice .notice-dismiss', function() {
        var data = {
            action: 'my_dismiss_notice',
        };

        jQuery.post( notice_params.ajaxurl, data, function() {
        });
    })
});