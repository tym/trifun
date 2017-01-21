<?php
/**
 * @class 		AW_Variable_Abstract_Shipment_Tracking
 * @package		AutomateWoo/Variables
 */

abstract class AW_Variable_Abstract_Shipment_Tracking extends AW_Variable
{
	/**
	 * Gets the first shipment tracking array
	 *
	 * @param $order WC_Order
	 * @param $field
	 * @return false|string
	 */
	function get_shipment_tracking_field( $order, $field )
	{
		if ( ! class_exists( 'WC_Shipment_Tracking' ) )
			return false;

		$tracking_items = WC_Shipment_Tracking_Actions::get_instance()->get_tracking_items( $order->id, true );

		if ( empty( $tracking_items ) )
			return false;

		if ( empty( $tracking_items[0][$field] ) )
			return false;

		return $tracking_items[0][$field];
	}
}