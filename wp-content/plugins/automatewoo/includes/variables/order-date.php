<?php
/**
 * @class 		AW_Variable_Order_Date
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Order_Date extends AW_Variable_Abstract_Datetime
{
	protected $name = 'order.date';

	function init()
	{
		parent::init();

		$this->description = __( 'Displays the date the order was placed.', 'automatewoo') . ' ' . $this->_desc_format_tip;
	}


	/**
	 * @param $order WC_Order
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $order, $parameters )
	{
		return $this->format_datetime( $order->order_date, $parameters );
	}
}

return new AW_Variable_Order_Date();

