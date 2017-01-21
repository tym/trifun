<?php
/**
 * Variable Processor
 *
 * Process variables into values. Is used on workflows and action options.
 *
 * @class AW_Variable_Processor
 * @since 2.0.2
 */

class AW_Variables_Processor {

	/** @var AW_Model_Workflow */
	public $workflow;


	/**
	 * @param $workflow
	 */
	function __construct( $workflow ) {
		$this->workflow = $workflow;
	}


	/**
	 * @param $text string
	 * @param bool $allow_html
	 *
	 * @return string
	 */
	function process_field( $text, $allow_html = false ) {

		$value = preg_replace_callback('~\{\{(.*?)\}\}~', [ $this,'_process_preg_callback' ], $text );

		if ( ! $allow_html ) {
			$value = strip_tags( $value );
		}

		return $value;
	}


	/**
	 * @param $match
	 *
	 * @return bool|mixed
	 */
	function _process_preg_callback( $match ) {

		$parameters_array = [];

		$var = $this->sanitize( $match[1] );

		if ( ! strstr( $var, '.' ) )
			return false;

		// Only explode once so a full stop can be used in params
		list( $data_type, $data_value ) = explode('.', $var, 2 );


		// Does this have parameters?
		if ( strstr( $var, '|' ) ) {
			// extract parameters
			list( $data_value, $parameters ) = explode( '|', $data_value );

			$parameters = explode( ',', $parameters );

			foreach ( $parameters as $parameter ) {
				if ( strstr( $parameter, ':' ) ) {
					list( $key, $value ) = explode( ':', $parameter );
					$parameters_array[sanitize_title($key)] = sanitize_text_field($value);
				}
			}
		}

		$data_type = sanitize_title( $data_type );
		$data_value = sanitize_title( $data_value );

		$value = $this->get_value( $data_type, $data_value, $parameters_array );

		$value = apply_filters( 'automatewoo/variables/after_get_value', $value, $data_type, $data_value, $parameters_array, $this->workflow );

		if ( ! $value ) {
			// backwards compatibility
			if ( isset( $parameters_array['default'] ) )
				$parameters_array['fallback'] = $parameters_array['default'];

			// show default if set and no real value
			if ( isset( $parameters_array['fallback'] ) )
				$value = $parameters_array['fallback'];
		}

		return $value;
	}


	/**
	 * @param $data_type
	 * @param $data_field
	 * @param $parameters
	 * @return mixed
	 */
	function get_value( $data_type, $data_field, $parameters = [] ) {

		// Short circuit filter
		if ( $filtered = apply_filters( 'automatewoo_text_variable_value', false, $data_type, $data_field ) )
			return $filtered;

		$this->_compatibility( $data_type, $data_field, $parameters );

		$variable = "$data_type.$data_field";
		$variable_obj = AW()->variables()->get_variable_object( $variable );

		if ( method_exists( $variable_obj, 'get_value' ) ) {

			$empty_data_types = [ 'shop' ];

			if ( in_array( $data_type, $empty_data_types ) ) {
				return $variable_obj->get_value( $parameters, $this->workflow );
			}
			else {
				if ( ! $data_item = $this->workflow->get_data_item( $data_type ) )
					return false;

				return $variable_obj->get_value( $data_item, $parameters, $this->workflow );
			}
		}
	}


	/**
	 * Based on sanitize_title()
	 *
	 * @param $string
	 *
	 * @return mixed|string
	 */
	function sanitize( $string ) {

		// remove style and script tags
		$string = wp_strip_all_tags( $string, true );
		$string = remove_accents( $string );

		// remove disallowed chars
		$remove = [
			'{', '}', '[', ']', '=',
		];

		$string = str_replace( $remove, '', $string );

		// remove unicode white spaces
		$string = preg_replace( "#\x{00a0}#siu", ' ', $string );

		$string = trim($string);

		return $string;
	}


	/**
	 * Backwards compatibility
	 */
	private function _compatibility( &$data_type, &$value, &$parameters ) {

		if ( $data_type == 'site' ) {
			$data_type = 'shop';
		}

		if ( $data_type == 'shop' ) {
			if ( $value == 'products_on_sale' ) {
				$value = 'products';
				$parameters['type'] = 'sale';
			}

			if ( $value == 'products_recent' ) {
				$value = 'products';
				$parameters['type'] = 'recent';
			}

			if ( $value == 'products_featured' ) {
				$value = 'products';
				$parameters['type'] = 'featured';
			}
		}

		switch ( $data_type ) {
			case 'site':

				$data_type = 'shop';

				break;
		}
	}

}

