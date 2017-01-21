/**
 * AutomateWoo Modal
 */

jQuery(function($) {

    AutomateWoo.Modal = {


        init: function(){

            $(document.body).on( 'click', '.js-close-automatewoo-modal', this.close );
            $(document.body).on( 'click', '.automatewoo-modal-overlay', this.close );
            $(document.body).on( 'click', '.js-open-automatewoo-modal', this.handle_link );

            $(window).resize(function(){
                AutomateWoo.Modal.position();
            });

            $(document).keydown(function(e) {
                if (e.keyCode == 27) {
                    AutomateWoo.Modal.close();
                }
            });

        },


        handle_link: function(e){
            e.preventDefault();

            var $a = $(this);
            var type = $a.data('modal-type');
            var url = $a.attr('href');

            if ( type == 'ajax' )
            {
                AutomateWoo.Modal.open( 'type-ajax' );
                AutomateWoo.Modal.loading();

                $.post( url, {}, function( response ){
                    AutomateWoo.Modal.contents( response );
                });
            }
        },


        open: function( classes ) {
            $(document.body).addClass('automatewoo-modal-open').append('<div class="automatewoo-modal-overlay"></div>');
            $(document.body).append('<div class="automatewoo-modal ' + classes + '"><div class="automatewoo-modal__contents"><div class="automatewoo-modal__header"></div></div><div class="automatewoo-icon-close js-close-automatewoo-modal"></div></div>');
            this.position();
        },


        loading: function() {
            $(document.body).addClass('automatewoo-modal-loading');
        },


        contents: function ( contents ) {
            $(document.body).removeClass('automatewoo-modal-loading');
            $('.automatewoo-modal__contents').html(contents);

            AW.initTooltips();

            this.position();
        },


        close: function() {
            $(document.body).removeClass('automatewoo-modal-open automatewoo-modal-loading');
            $('.automatewoo-modal, .automatewoo-modal-overlay').remove();
        },


        position: function() {

            $('.automatewoo-modal__body').removeProp('style');

            var modal_header_height = $('.automatewoo-modal__header').outerHeight();
            var modal_height = $('.automatewoo-modal').height();
            var modal_width = $('.automatewoo-modal').width();
            var modal_body_height = $('.automatewoo-modal__body').outerHeight();
            var modal_contents_height = modal_body_height + modal_header_height;

            $('.automatewoo-modal').css({
                'margin-left': -modal_width / 2,
                'margin-top': -modal_height / 2
            });

            if ( modal_height < modal_contents_height - 5 ) {
                $('.automatewoo-modal__body').height( modal_height - modal_header_height );
            }
        }


    };


    AutomateWoo.Modal.init();

});