<?php
/**
 * @class 		AW_Variable_Order_Itemscount
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Order_Itemscount extends AW_Variable
{
	protected $name = 'order.itemscount';

	function init()
	{
		$this->description = __( "Displays the number of items in the order.", 'automatewoo');
	}

	/**
	 * @param $order WC_Order
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $order, $parameters )
	{
		return $order->get_item_count();
	}
}


return new AW_Variable_Order_Itemscount();