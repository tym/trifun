/**
 * Workflow field validator
 */

(function( $, localizedErrorMessages ) {

    var self;

    AW.Validate = {

        errorMessages: {},


        init: function() {

            setInterval(function() {
                tinyMCE.triggerSave();
                $('.aw-input textarea.wp-editor-area').each(function() {
                    $(this).attr( 'data-automatewoo-validate', 'variables' );
                    self.validateField( $(this) );
                })
            }, 3000 );

            $( document.body ).on( 'keyup blur', '[data-automatewoo-validate]', function( event ){
                self.validateField( $(event.target) )
            });

            self.validateAllFields();

        },



        validateAllFields: function() {
            $( '[data-automatewoo-validate]' ).each( function() {
                self.validateField( $(this) );
            });
        },



        validateField: function( $field ) {

            if ( ! AW.workflow )
                return;

            var errors = [];
            var text = $field.val();

            self.clearFieldErrors( $field );

            var usedVariables = AW.Validate.getVariablesFromText( text );

            if ( self.fieldSupports( 'variables', $field ) ) {

                var trigger = AW.workflow.get( 'trigger' );

                _.each( usedVariables, function( variable ) {
                    var dataType = self.getDataTypeFromVariable( variable );

                    if ( dataType && _.indexOf( trigger.supplied_data_items, dataType ) === -1 ) {
                        errors.push( self.getErrorMessage( 'invalidDataType', self.getVariableWithoutParams( variable ) ) );
                    }

                });

            }
            else {
                if ( usedVariables ) {
                    errors.push( self.getErrorMessage( 'noVariablesSupport' ) );
                }
            }


            if ( errors.length ) {
                self.setFieldErrors( $field, errors );
            }

        },



        setFieldErrors: function( $field, errors ) {

            $field.addClass( 'automatewoo-field--invalid' );
            var $wrap = $field.parents( '.automatewoo-field-wrap:first' );
            $wrap.append('<div class="automatewoo-field-errors"></div>');
            var $errors = $wrap.find( '.automatewoo-field-errors' );

            if ( $field.is( '.wp-editor-area' ) ) {
                $wrap.find( '.wp-editor-container' ).addClass( 'automatewoo-field--invalid' )
            }

            _.each( errors, function( error ) {
                $errors.append( '<div class="automatewoo-field-errors__error">'+ error + '</div>' );
            });
        },


        clearFieldErrors: function( $field ) {
            var $wrap = $field.parents( '.automatewoo-field-wrap:first' );
            $field.removeClass( 'automatewoo-field--invalid' );

            if ( $field.is( '.wp-editor-area' ) ) {
                $wrap.find( '.wp-editor-container' ).removeClass( 'automatewoo-field--invalid' )
            }

            $wrap.find( '.automatewoo-field-errors' ).remove();
        },


        fieldSupports: function( option, $field ) {
            var options = $field.data( 'automatewoo-validate' ).split( ' ' );
            return _.indexOf( options, option ) !== -1
        },


        getVariablesFromText: function( text ) {

            var variables = text.match(/{{(.*?)}}/g);

            if ( ! variables ) {
                return false;
            }

            _.each( variables, function( variable, i ) {
                variables[i] = variable.replace( /\s|{|}/g, '' );
            });

            return variables;
        },


        getVariableWithoutParams: function( variable ) {
            return variable.replace( /(\|.*)/g, '' );
        },


        getDataTypeFromVariable: function( variable ) {
            if ( variable.indexOf('.') === -1 ) return false;
            return variable.replace( /(\..*)/g, '' );
        },



        getErrorMessage: function( error, replace ) {

            if ( ! self.errorMessages[error] ) {
                return 'Unknown error, please try refreshing your browser.';
            }

            var error = self.errorMessages[error];

            if ( typeof replace == 'string' ) {
                error = error.replace( '%s', replace );
            }

            return error;
        }

    };


    self = AW.Validate;
    self.errorMessages = localizedErrorMessages;

})( jQuery, automatewooValidateLocalizedErrorMessages );