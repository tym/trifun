<?php
/**
 * @class 		AW_Variable_Order_ID
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Order_ID extends AW_Variable
{
	/** @var string  */
	protected $name = 'order.id';

	/**
	 * Init
	 */
	function init()
	{
		$this->description = __( "Displays the order's unique ID.", 'automatewoo');
	}


	/**
	 * @param $order WC_Order
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $order, $parameters )
	{
		return $order->id;
	}
}

return new AW_Variable_Order_ID();