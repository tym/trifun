<?php
/**
 * @class 		AW_Data_Type_Wishlist
 * @package		AutomateWoo/Data Types
 */

class AW_Data_Type_Wishlist extends AW_Data_Type {

	/**
	 * @param $item
	 * @return bool
	 */
	function validate( $item ) {
		if ( is_object( $item ) )
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
		return AW()->wishlist()->get_wishlist( $compressed_item );
	}

}

return new AW_Data_Type_Wishlist();
