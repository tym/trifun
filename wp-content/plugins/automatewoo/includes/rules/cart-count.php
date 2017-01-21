<?php
/**
 * @class 		AW_Rule_Cart_Count
 * @package		AutomateWoo/Rules
 */

class AW_Rule_Cart_Count extends AW_Rule_Abstract_Number
{
	/** @var array  */
	public $data_item = 'cart';

	public $support_floats = false;

	/**
	 * Init
	 */
	function init()
	{
		$this->title = __( 'Cart Item Count', 'automatewoo' );
		$this->group = __( 'Cart', 'automatewoo' );
	}


	/**
	 * @param $cart AW_Model_Abandoned_Cart
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $cart, $compare, $value )
	{
		return $this->validate_number( count( $cart->get_items() ), $compare, $value );
	}


}

return new AW_Rule_Cart_Count();
