/**
 * AutomateWoo main - loaded on every admin page
 */

var AutomateWoo, AW = {};

(function($) {

	AW.init = function() {

		AW.params = automatewooLocalizeScript;

		AW.initEnhancedSelects();
		AW.initTooltips();
		AW.initWorkflowStatusSwitch();
	};


	/**
	 * Init tool tips
	 */
	AW.initTooltips = function () {
		$( '.tips' ).tipTip({
			attribute: 'data-tip',
			fadeIn: 50,
			fadeOut: 50,
			delay: 200
		});
	};


	/**
	 * Ajax search search box
	 */
	AW.initEnhancedSelects = function() {

		$(document.body).trigger('wc-enhanced-select-init');

		$( ':input.automatewoo-json-search' ).filter( ':not(.enhanced)' ).each( function() {
			var select2_args = {
				allowClear:  $( this ).data( 'allow_clear' ) ? true : false,
				placeholder: $( this ).data( 'placeholder' ),
				minimumInputLength: 1,
				escapeMarkup: function( m ) {
					return m;
				},
				ajax: {
					url:         ajaxurl,
					dataType:    'json',
					quietMillis: 250,
					data: function( term ) {

						var data = {
							term: term,
							action: $( this ).data( 'action' ),
							exclude: $( this ).data( 'exclude' )
						};

						// pass in sibling field data
						var sibling = $(this).data('pass-sibling');
						if ( sibling ) {
							var $sibling = $('[name="'+ sibling+ '"]');

							if ( $sibling.length ) {
								data['sibling'] = $sibling.val()
							}
						}

						return data;
					},
					results: function( data ) {
						var terms = [];
						if ( data ) {
							$.each( data, function( id, text ) {
								terms.push( { id: id, text: text } );
							});
						}
						return {
							results: terms
						};
					},
					cache: true
				}
			};

			if ( $( this ).data( 'multiple' ) === true ) {
				select2_args.multiple = true;
				select2_args.initSelection = function( element, callback ) {
					var data     = $.parseJSON( element.attr( 'data-selected' ) );
					var selected = [];

					$( element.val().split( ',' ) ).each( function( i, val ) {
						selected.push({
							id: val,
							text: data[ val ]
						});
					});
					return callback( selected );
				};
				select2_args.formatSelection = function( data ) {
					return '<div class="selected-option" data-id="' + data.id + '">' + data.text + '</div>';
				};
			} else {
				select2_args.multiple = false;
				select2_args.initSelection = function( element, callback ) {
					var data = {
						id: element.val(),
						text: element.attr( 'data-selected' )
					};
					return callback( data );
				};
			}

			$( this ).select2( select2_args ).addClass( 'enhanced' );
		});
	};



	AW.initWorkflowStatusSwitch = function() {

		$('.aw-switch.js-toggle-workflow-status').click(function(){

			var $switch, state, new_state;

			$switch = $(this);

			if ( $switch.is('.aw-loading') )
				return;

			state = $switch.attr( 'data-aw-switch' );
			new_state = state === 'on' ? 'off' : 'on';

			$switch.addClass('aw-loading');
			$switch.attr( 'data-aw-switch', new_state );

			$.post( ajaxurl, {
				action: 'aw_toggle_workflow_status',
				workflow_id: $switch.attr( 'data-workflow-id' ),
				new_state: new_state
			}, function() {
				$switch.removeClass('aw-loading');
			});

		});
	};


	/**
	 * @param float
	 * @return string
	 */
	AW.price = function( float ) {

		var price = float.toFixed(2);
		var symbol = AW.params.locale.currency_symbol;

		switch ( AW.params.locale.currency_position ) {
			case 'right':
				price = price + symbol;
				break;
			case 'right_space':
				price = price + ' ' + symbol;
				break;
			case 'left':
				price = symbol + price;
				break;
			case 'left_space':
			default:
				price = symbol + ' ' + price;
				break;
		}

		return price;
	};


	$(document).ready(function() {
		AW.init();
	});


})( jQuery );



jQuery(function($) {


	AutomateWoo = {

		_email_preview_window: null,


		init: function() {
			this.init_notice_dismiss();
			this.init_date_pickers();
		},


		notices: {

			success: function( message, $location ) {
				if ( ! $location.length ) return;
				$location.before('<div class="automatewoo-notice updated fade"><p><strong>' + message + '</strong></p></div>');
			},

			error: function( message, $location ) {
				if ( ! $location.length ) return;
				$location.before('<div class="automatewoo-notice error fade"><p><strong>' + message + '</strong></p></div>');
			},

			clear_all: function() {
				$('.automatewoo-notice').slideUp();
			}

		},



		init_notice_dismiss: function(){

			$('.aw-notice-licence-renew').on('click', '.notice-dismiss', function(){
				$.ajax({
					url: ajaxurl,
					data: { action: 'aw_dismiss_expiry_notice' }
				});
			});

			$('.aw-notice-system-error').on('click', '.notice-dismiss', function(){
				$.ajax({
					url: ajaxurl,
					data: { action: 'aw_dismiss_system_error_notice' }
				});
			});
		},
		


		init_date_pickers: function() {
			$( '.automatewoo-date-picker' ).datepicker({
				dateFormat: 'yy-mm-dd',
				numberOfMonths: 1,
				showButtonPanel: true
			});
		},



		isEmailPreviewOpen: function() {
			return this._email_preview_window && ! this._email_preview_window.closed;
		},


		openLoadingEmailPreview: function() {

			this.openPreviewWindow( AW.params.url.admin + '?automatewoo-email-preview-loader=1' )
		},


        /**
		 * @param type
		 * @param args
         */
		open_email_preview: function( type, args ) {

			var request = {
				action: 'aw_email_preview_ui',
				type: type,
				args: args
			};

			var joiner = AW.params.url.ajax.indexOf('?') == -1 ? '?' : '&';

			this.openPreviewWindow( AW.params.url.ajax + joiner + $.param( request ) );
		},


		/**
		 * @param url
         */
		openPreviewWindow: function( url ) {
			this._email_preview_window = window.open( url, 'automatewoo_preview', 'titlebar=no,toolbar=no,height=768,width=860,resizable=yes,status=no' );
		}


	};

	AutomateWoo.init();

});
