jQuery( function ( $ ) {

    $( document ).on( 'yith_wcpb_add_to_cart_init', function () {

        var select_for_variables           = $( '.yith-wcpb-select-for-variables' ),
            add_to_cart_btn                = $( '.single_add_to_cart_button' ),
            add_to_quote_btn               = $( '.add-request-quote-button' ),
            add_to_quote_btn_default_color = add_to_quote_btn.css( 'background-color' ),
            variation_forms                = $( '.bundled_item_cart_content' ),
            control_disable_btn            = function () {
                var disabled = false;

                variation_forms.each( function () {
                    var optional_checked_or_not_optional = ($( this ).find( '.yith-wcpb-bundled-optional' ).length > 0 ) ? $( this ).find( '.yith-wcpb-bundled-optional' ).is( ':checked' ) : true;
                    var my_select                        = $( this ).find( 'select.yith-wcpb-select-for-variables' );

                    if ( optional_checked_or_not_optional ) {
                        my_select.each( function () {
                            var current_select = $( this );
                            if ( current_select.val() == undefined || current_select.val() == '' ) {
                                disabled = true;
                            }

                        } );
                        $( this ).find( '.variations' ).slideDown( 'fast' );
                        $( this ).find( '.single_variation_wrap' ).slideDown( 'fast' );

                        if ( $( this ).find( '.out-of-stock' ).length > 0 ) {
                            disabled = true;
                        }
                    } else {

                        if ( $( this ).find( '.yith-wcpb-bundled-optional' ).length > 0 ) {

                            $( this ).find( '.quantity input.qty' ).removeAttr( 'max' );
                            $( this ).find( '.single_variation_wrap' ).slideUp( 'fast' );
                            $( this ).find( '.variations' ).slideUp( 'fast' );
                            $( this ).find( '.variation_id' ).val( '' );
                            $( this ).closest( '.variations_form' ).find( '.variations select' ).val( '' );
                        }
                    }
                } );

                add_to_cart_btn.prop( 'disabled', disabled );

                // integration with Request a quote
                if ( disabled ) {
                    add_to_quote_btn.addClass( 'disabled' );
                    add_to_quote_btn.css( 'background-color', '#bbb' );
                } else {
                    add_to_quote_btn.removeClass( 'disabled' );
                    add_to_quote_btn.css( 'background-color', add_to_quote_btn_default_color );
                }
            };

        // check if all variable select are selected
        control_disable_btn();
        // when variation form
        variation_forms.on( 'change', control_disable_btn );

        // ***********************
        // * price update [AJAX] *
        // ***********************

        var bundle_data             = $( '#yith-wcpb-bundle-product-data' ),
            bundle_id               = bundle_data.data( 'product-id' ),
            bundle_per_item_pricing = bundle_data.data( 'per-item-pricing' ),
            price_container         = $( '.summary p.price' ).first(),
            quantity_sel            = $( 'input.yith-wcpb-bundled-quantity' ),
            opt_check               = $( '.yith-wcpb-bundled-optional' ),
            variations              = $( '.variation_id' ),
            in_updating             = false,
            block_params            = {
                message        : null,
                overlayCSS     : {
                    background: '#fff',
                    opacity   : 0.6
                },
                ignoreIfBlocked: true
            },
            ajax_update_price_request,
            update_price            = function () {
                if ( ajax_update_price_request ) {
                    ajax_update_price_request.abort();
                }

                if ( bundle_per_item_pricing != 1 ) {
                    return;
                }

                price_container.block( block_params );

                var array_qty = [];
                var array_opt = [];
                var array_var = [];

                quantity_sel.each( function () {
                    array_qty.push( $( this ).val() );
                } );

                opt_check.each( function () {
                    array_opt[ $( this ).data( 'item-id' ) - 1 ] = $( this ).is( ':checked' ) ? 1 : 0;
                } );

                variations.each( function () {
                    array_var[ $( this ).data( 'item-id' ) - 1 ] = $( this ).val();
                } );

                var post_data = {
                    bundle_id: bundle_id,
                    array_qty: array_qty,
                    array_opt: array_opt,
                    array_var: array_var,
                    action   : 'yith_wcpb_get_bundle_total_price'
                };

                ajax_update_price_request = $.ajax( {
                                                        type   : "POST",
                                                        data   : post_data,
                                                        url    : ajax_obj.ajaxurl,
                                                        success: function ( response ) {
                                                            var price_to_upload = price_container.find( 'ins .amount' );
                                                            if ( price_to_upload.length < 1 ) {
                                                                price_to_upload = price_container.find( '.amount' );
                                                            }
                                                            price_to_upload = price_to_upload.first();
                                                            price_to_upload.html( response );
                                                            price_container.html( price_to_upload.html() );
                                                            price_container.unblock();
                                                        }
                                                    } );
            };

        quantity_sel.on( 'change', function ( e ) {
            if ( $( this ).parents( '.bundled_item_cart_content' ).length == 0 )
                update_price();
        } );

        opt_check.on( 'click', function ( e ) {
            if ( $( this ).parents( '.bundled_item_cart_content' ).length == 0 )
                update_price();
        } );

        variation_forms.on( 'change', function () {
            update_price();
        } );

        variation_forms.on( 'found_variation', function ( event, variation ) {
                var $prices     = $( this ).closest( '.product' ).find( '.yith-wcpb-product-bundled-item-image .price' ).first(),
                    $price      = $prices.find( 'ins' ),
                    $real_price = $prices.find( 'del' );

                $price.html( variation.price_html.replace( 'price', 'amount' ) );
                $real_price.html( variation.display_regular_price_html );
            } )
            .on( 'reset_data', function () {
                var $prices     = $( this ).closest( '.product' ).find( '.yith-wcpb-product-bundled-item-image .price' ).first(),
                    $price      = $prices.find( 'ins' ),
                    $real_price = $prices.find( 'del' );

                $price.html( '' );
                $real_price.html( '' );
            } );

        // on first load update the price
        //select_for_variables.trigger( 'change' );
        update_price();

    } ).trigger( 'yith_wcpb_add_to_cart_init' );

    // compatibility with YITH WooCommerce Quick View
    $( document ).on( 'qv_loader_stop', function () {
        $( document ).trigger( 'yith_wcpb_add_to_cart_init' );
    } )
} );