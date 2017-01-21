<?php
/**
 * @class 		AW_Data_Type_Order_Item
 * @package		AutomateWoo/Data Types
 */

class AW_Data_Type_Order_Item extends AW_Data_Type {

	/**
	 * @param $item
	 * @return bool
	 */
	function validate( $item ) {
		if ( is_array($item) )
			return true;
	}


	/**
	 * @param $item array
	 * @return mixed
	 */
	function compress( $item ) {
		return $item['id'];
	}


	/**
	 * Order items are retrieved from the order object so we must ensure that an order is always present in the data layer
	 *
	 * @param $order_item_id
	 * @param $compressed_data_layer
	 * @return mixed
	 */
	function decompress( $order_item_id, $compressed_data_layer ) {

		if ( ! isset( $compressed_data_layer['order'] ) )
			return false;

		if ( ! $order = wc_get_order( $compressed_data_layer['order'] ) )
			return false;

		$items = $order->get_items();

		if ( ! isset( $items[ $order_item_id ] ) )
			return false;

		return AW()->order_helper->prepare_order_item( $order_item_id, $items[ $order_item_id ] );
	}

}

return new AW_Data_Type_Order_Item();
