<?php
/**
 * @class 		AW_Rule_Cart_Total
 * @package		AutomateWoo/Rules
 */

class AW_Rule_Cart_Total extends AW_Rule_Abstract_Number
{
	/** @var array  */
	public $data_item = 'cart';

	public $support_floats = false;

	/**
	 * Init
	 */
	function init()
	{
		$this->title = __( 'Cart Total', 'automatewoo' );
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
		return $this->validate_number( $cart->total, $compare, $value );
	}

}

return new AW_Rule_Cart_Total();
