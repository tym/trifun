<?php
/**
 * @class 		AW_Data_Type_User
 * @package		AutomateWoo/Data Types
 */

class AW_Data_Type_User extends AW_Data_Type {

	/**
	 * @param $item
	 * @return bool
	 */
	function validate( $item ) {
		if ( $item instanceof WP_User || $item instanceof AW_Model_Order_Guest )
			return true;
	}


	/**
	 * @param $item
	 * @return mixed
	 */
	function compress( $item ) {
		return $item->ID;
	}


	/**
	 * @param $compressed_item
	 * @param $compressed_data_layer
	 * @return mixed
	 */
	function decompress( $compressed_item, $compressed_data_layer ) {

		// if order based trigger always get the user data from the order
		if ( isset( $compressed_data_layer['order'] ) ) {
			if ( $order = wc_get_order( $compressed_data_layer['order'] ) ) {
				return AW()->order_helper->prepare_user_data_item( $order );
			}
		}

		if ( $compressed_item ) {
			return get_user_by( 'id', $compressed_item );
		}
	}

}

return new AW_Data_Type_User();
