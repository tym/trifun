<?php
/**
 * @class 		AW_Variable_Order_Total
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Order_Total extends AW_Variable
{
	protected $name = 'order.total';


	/**
	 * Init
	 */
	function init()
	{
		$this->description = __( "Displays the formatted total of the order.", 'automatewoo');
	}

	/**
	 * @param $order WC_Order
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $order, $parameters )
	{
		return $order->get_formatted_order_total();
	}
}

return new AW_Variable_Order_Total();