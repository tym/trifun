<?php
/**
 * @class 		AW_Variable_Cart_Link
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Cart_Link extends AW_Variable
{
	protected $name = 'cart.link';

	function init()
	{
		$this->description = __( "Displays a unique link to the cart page that will also restore items to the users cart.", 'automatewoo');
	}


	/**
	 * @param $cart AW_Model_Abandoned_Cart
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $cart, $parameters )
	{
		return add_query_arg(array(
			'aw-restore-cart' => $cart->token
		), wc_get_page_permalink('cart') );
	}
}

return new AW_Variable_Cart_Link();