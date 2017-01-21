<?php
/**
 * @class 		AW_Variable_Cart_Total
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Cart_Total extends AW_Variable
{
	protected $name = 'cart.total';

	function init()
	{
		$this->description = __( "Displays the formatted total of the cart.", 'automatewoo');
	}


	/**
	 * @param $cart AW_Model_Abandoned_Cart
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $cart, $parameters )
	{
		return wc_price( $cart->total );
	}
}

return new AW_Variable_Cart_Total();
