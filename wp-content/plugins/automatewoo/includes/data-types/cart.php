<?php
/**
 * @class 		AW_Data_Type_Cart
 * @package		AutomateWoo/Data Types
 */

class AW_Data_Type_Cart extends AW_Data_Type {

	/**
	 * @param $item
	 * @return bool
	 */
	function validate( $item ) {
		if ( $item instanceof AW_Model_Abandoned_Cart )
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

		$cart = new AW_Model_Abandoned_Cart( $compressed_item );
		$cart->id = $compressed_item;

		// Pass the cart object even if it doesn't exist in the database
		return $cart;
	}

}

return new AW_Data_Type_Cart();
