<?php
/**
 * @class 		AW_Variable_Order_Billing_Phone
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Order_Billing_Phone extends AW_Variable
{
	protected $name = 'order.billing_phone';

	/**
	 * Init
	 */
	function init()
	{
		$this->description = __( "Displays the billing phone number for the order.", 'automatewoo');
	}


	/**
	 * @param $order WC_Order
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $order, $parameters )
	{
		return $order->billing_phone;
	}
}

return new AW_Variable_Order_Billing_Phone();