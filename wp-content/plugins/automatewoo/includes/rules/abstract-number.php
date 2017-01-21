<?php
/**
 * @class 		AW_Rule_Abstract_Number
 * @package		AutomateWoo/Rules
 */

abstract class AW_Rule_Abstract_Number extends AW_Rule_Abstract
{
	public $type = 'number';

	public $support_floats = true;


	function __construct()
	{
		$this->compare_types = [
			'is' => __( 'is', 'automatewoo' ),
			'is_not' => __( 'is not', 'automatewoo' ),
			'greater_than' => __( 'is greater than', 'automatewoo' ),
			'less_than' => __( 'is less than', 'automatewoo' ),
		];

		if ( ! $this->support_floats )
		{
			$this->compare_types['multiple_of'] = __( 'is a multiple of', 'automatewoo' );
			$this->compare_types['not_multiple_of'] = __( 'is not a multiple of', 'automatewoo' );
		}


		parent::__construct();
	}


	/**
	 * @param $input_value
	 * @param $compare_type
	 * @param $target_value
	 * @return bool
	 */
	function validate_number( $input_value, $compare_type, $target_value )
	{
		$input_value = (float) $input_value;
		$target_value = (float) $target_value;

		switch ( $compare_type )
		{
			case 'is':
				return $input_value == $target_value;
				break;

			case 'is_not':
				return $input_value != $target_value;
				break;

			case 'greater_than':
				return $input_value > $target_value;
				break;

			case 'less_than':
				return $input_value < $target_value;
				break;
		}


		// extra options for integers
		if ( ! $this->support_floats )
		{
			$input_value = (int) $input_value;
			$target_value = (int) $target_value;

			switch ( $compare_type )
			{
				case 'multiple_of':
					return bcmod( $input_value, $target_value ) == 0;
					break;

				case 'not_multiple_of':
					return bcmod( $input_value, $target_value ) != 0;
					break;
			}
		}

		return false;
	}

}