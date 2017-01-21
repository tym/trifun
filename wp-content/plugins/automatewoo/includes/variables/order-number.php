<?php
/**
 * @class 		AW_Variable_Order_Number
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Order_Number extends AW_Variable
{
	protected $name = 'order.number';

	function init()
	{
		$this->description = __( "Displays the order number.", 'automatewoo');
	}

	/**
	 * @param $order WC_Order
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $order, $parameters )
	{
		return $order->get_order_number();
	}
}

return new AW_Variable_Order_Number();