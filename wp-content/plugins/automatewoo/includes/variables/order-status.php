<?php
/**
 * @class 		AW_Variable_Order_Status
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Order_Status extends AW_Variable
{
	protected $name = 'order.status';

	/**
	 * Init
	 */
	function init()
	{
		$this->description = __( "Displays the status of the order.", 'automatewoo');
	}

	/**
	 * @param $order WC_Order
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $order, $parameters )
	{
		return $order->get_status();
	}
}

return new AW_Variable_Order_Status();