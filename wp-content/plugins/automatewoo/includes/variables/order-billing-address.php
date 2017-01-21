<?php
/**
 * @class 		AW_Variable_Order_Billing_Address
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Order_Billing_Address extends AW_Variable
{
	protected $name = 'order.billing_address';

	/**
	 * Init
	 */
	function init()
	{
		$this->description = __( "Displays the formatted billing address for the order.", 'automatewoo');
	}


	/**
	 * @param $order WC_Order
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $order, $parameters )
	{
		return $order->get_formatted_billing_address();
	}
}

return new AW_Variable_Order_Billing_Address();