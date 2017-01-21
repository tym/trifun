<?php
/**
 * @class 		AW_Variable_Order_Payment_Url
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Order_Payment_Url extends AW_Variable
{
	protected $name = 'order.payment_url';

	function init()
	{
		$this->description = __( "Displays a payment URL for the order.", 'automatewoo');
	}

	/**
	 * @param $order WC_Order
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $order, $parameters )
	{
		return $order->get_checkout_payment_url();
	}
}

return new AW_Variable_Order_Payment_Url();