<?php
/**
 * @class 		AW_Variable_Order_View_Url
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Order_View_Url extends AW_Variable
{
	protected $name = 'order.view_url';

	function init()
	{
		$this->description = __( "Displays a URL to view the order in the user account area.", 'automatewoo');
	}

	/**
	 * @param $order WC_Order
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $order, $parameters )
	{
		return $order->get_view_order_url();
	}
}

return new AW_Variable_Order_View_Url();
