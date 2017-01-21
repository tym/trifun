<?php
/**
 * @class 		AW_Data_Type_Order
 * @package		AutomateWoo/Data Types
 */

class AW_Data_Type_Order extends AW_Data_Type {

	/**
	 * @param $item
	 * @return bool
	 */
	function validate( $item ) {
		if ( is_subclass_of( $item, 'WC_Abstract_Order' ) )
			return true;
	}


	/**
	 * @param $item
	 * @return mixed
	 */
	function compress( $item ) {
		return $item->id;
	}


	/**
	 * @param $compressed_item
	 * @param $compressed_data_layer
	 * @return mixed
	 */
	function decompress( $compressed_item, $compressed_data_layer ) {
		return wc_get_order( $compressed_item );
	}

}

return new AW_Data_Type_Order();
