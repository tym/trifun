<?php
/**
 * @class 		AW_Variable_Order_Shipping_Address
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Order_Shipping_Address extends AW_Variable
{
	protected $name = 'order.shipping_address';

	/**
	 * Init
	 */
	function init()
	{
		$this->description = __( "Displays the formatted shipping address for the order.", 'automatewoo');
	}


	/**
	 * @param $order WC_Order
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $order, $parameters )
	{
		return $order->get_formatted_shipping_address();
	}
}

return new AW_Variable_Order_Shipping_Address();