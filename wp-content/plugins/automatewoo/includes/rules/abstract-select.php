<?php
/**
 * @class 		AW_Rule_Abstract_Select
 * @package		AutomateWoo/Rules
 */

abstract class AW_Rule_Abstract_Select extends AW_Rule_Abstract
{
	public $type = 'select';

	/** @var array - leave public for json */
	public $select_choices;

	/** @var bool  */
	public $is_multi = false;

	/**
	 * Don't gather select choices on construct as there could be a lot of data
	 * @return array
	 */
	abstract function get_select_choices();


	function __construct()
	{
		if ( $this->is_multi )
		{
			$this->compare_types = [
				'matches_any' => __( 'matches any of', 'automatewoo' ),
				'matches_all' => __( 'matches all of', 'automatewoo' ),
				'matches_none' => __( 'matches none of', 'automatewoo' ),
			];
		}
		else
		{
			$this->compare_types = [
				'is' => __( 'is', 'automatewoo' ),
				'is_not' => __( 'is not', 'automatewoo' )
			];
		}


		parent::__construct();
	}


	/**
	 * $value param can be array
	 *
	 * @param $actual
	 * @param $compare_type
	 * @param $expected
	 * @return bool
	 */
	function validate_select( $actual, $compare_type, $expected )
	{
		if ( $this->is_multi )
		{
			if ( ! $actual ) $actual = []; // actual can be empty
			if ( ! $expected ) return false; // expected must have a value

			$actual = (array) $actual;
			$expected = (array) $expected;

			switch ( $compare_type )
			{
				case 'matches_all':
					return count( array_intersect( $expected, $actual ) ) === count( $expected );
					break;

				case 'matches_none':
					return count( array_intersect( $expected, $actual ) ) === 0;
					break;

				case 'matches_any':
					return count( array_intersect( $expected, $actual ) ) >= 1;
					break;
			}
		}
		else
		{
			// actual must be scalar, but expected could be multiple values
			if ( ! is_scalar( $actual ) )
				return false;

			if ( is_array( $expected ) )
			{
				$is_equal = in_array( $actual, $expected );
			}
			else
			{
				$is_equal = $expected == $actual;
			}

			switch ( $compare_type )
			{
				case 'is':
					return $is_equal;
					break;

				case 'is_not':
					return ! $is_equal;
					break;
			}
		}

		return false;
	}

}