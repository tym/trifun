<?php
/**
 * @class 		AW_Variable_Order_Date_Shipped
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Order_Date_Shipped extends AW_Variable_Abstract_Shipment_Tracking
{
	protected $name = 'order.date_shipped';

	function init()
	{
		$this->description = sprintf(
			__( "Displays the formatted shipping date as set with the <a href='%s' target='_blank'>WooThemes Shipment Tracking</a> plugin.", 'automatewoo'),
			'https://www.woothemes.com/products/shipment-tracking/'
		);
	}


	/**
	 * @param $order WC_Order
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $order, $parameters )
	{
		return $this->get_shipment_tracking_field( $order, 'date_shipped' );
	}
}

return new AW_Variable_Order_Date_Shipped();

