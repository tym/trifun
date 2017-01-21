<?php
/**
 * @class 		AW_Variable_Order_Tracking_Url
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Order_Tracking_Url extends AW_Variable_Abstract_Shipment_Tracking
{
	protected $name = 'order.tracking_url';


	/**
	 * Init
	 */
	function init()
	{
		$this->description = sprintf(
			__( "Displays the date shipped url as set with the <a href='%s' target='_blank'>WooThemes Shipment Tracking</a> plugin.", 'automatewoo'),
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
		return $this->get_shipment_tracking_field( $order, 'formatted_tracking_link' );
	}
}

return new AW_Variable_Order_Tracking_Url();

