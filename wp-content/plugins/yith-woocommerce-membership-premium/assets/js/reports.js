jQuery( function ( $ ) {
    var filter_user_id                   = $( '#yith-wcmbs-reports-filter-user-id' ),
        filter_button                    = $( '#yith-wcmbs-reports-filter-button' ),
        filter_reset                     = $( '#yith-wcmbs-reports-filter-reset' ),
        download_reports_table_container = $( '#yith-wcmbs-reports-download-reports-table' ),
        downloads_table                  = $( '#yith-wcmbs-reports-table-downloads' ),
        order                            = downloads_table.data( 'order' ),
        user_id                          = downloads_table.data( 'user-id' ),
        block_params                     = {
            message: null,
            overlayCSS: {
                background: '#000',
                opacity: 0.6
            },
            ignoreIfBlocked: true
        };

    download_reports_table_container.on( 'yith_wcmbs_update_table', function () {
        var post_data = {
            user_id: user_id,
            order: order,
            action: 'yith_wcmbs_get_download_table_reports'
        };

        download_reports_table_container.block( block_params );

        $.ajax( {
            type: "POST",
            data: post_data,
            url: ajaxurl,
            success: function ( response ) {
                download_reports_table_container.html( response );
                download_reports_table_container.unblock();
            }
        } );
    } );

    filter_button.on( 'click', function () {
        user_id = filter_user_id.val();
        download_reports_table_container.trigger( 'yith_wcmbs_update_table' );
    } );

    filter_reset.on( 'click', function () {
        filter_user_id.trigger( 'yith_wcmbs_chosen_reset' );
        filter_button.trigger( 'click' );
    } );

    download_reports_table_container.on( 'click', '#yith-wcmbs-reports-table-downloads-order-by-downloads', function () {
        order = $( this ).data( 'order' );
        download_reports_table_container.trigger( 'yith_wcmbs_update_table' );
    } );
} );
