<?php
/**
 * @class 		AW_Rule_Abstract_String
 * @package		AutomateWoo/Rules
 */

abstract class AW_Rule_Abstract_String extends AW_Rule_Abstract
{
	public $type = 'string';


	function __construct()
	{
		$this->compare_types = [
			'contains' => __( 'contains', 'automatewoo' ),
			'not_contains' => __( 'does not contain', 'automatewoo' ),
			'is' => __( 'is', 'automatewoo' ),
			'is_not' => __( 'is not', 'automatewoo' ),
			'starts_with' => __( 'starts with', 'automatewoo' ),
			'ends_with' => __( 'ends with', 'automatewoo' )
		];

		parent::__construct();
	}


	/**
	 * @param $input_value
	 * @param $compare_type
	 * @param $target_value
	 * @return bool
	 */
	function validate_string( $input_value, $compare_type, $target_value )
	{
		$input_value = (string) $input_value;
		$target_value = (string) $target_value;

		switch ( $compare_type )
		{
			case 'is':
				return $input_value == $target_value;
				break;

			case 'is_not':
				return $input_value != $target_value;
				break;

			case 'contains':
				return stristr( $input_value, $target_value ) !== false;
				break;

			case 'not_contains':
				return stristr( $input_value, $target_value ) === false;
				break;

			case 'starts_with':
				$length = strlen( $target_value );
				return substr( $input_value, 0, $length ) === $target_value;
				break;

			case 'ends_with':
				$length = strlen( $target_value );

				if ( $length == 0 )
					return true;

				return substr( $input_value, -$length ) === $target_value;
				break;
		}

		return false;
	}

}