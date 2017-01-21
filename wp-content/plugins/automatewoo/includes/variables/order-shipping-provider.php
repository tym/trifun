<?php
/**
 * @class 		AW_Variable_Order_Shipping_Provider
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Order_Shipping_Provider extends AW_Variable_Abstract_Shipment_Tracking
{
	protected $name = 'order.shipping_provider';

	/**
	 * Init
	 */
	function init()
	{
		$this->description = sprintf(
			__( "Displays the name of the shipping provider as set with the <a href='%s' target='_blank'>WooThemes Shipment Tracking</a> plugin.", 'automatewoo'),
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
		return $this->get_shipment_tracking_field( $order, 'formatted_tracking_provider' );
	}
}

return new AW_Variable_Order_Shipping_Provider();