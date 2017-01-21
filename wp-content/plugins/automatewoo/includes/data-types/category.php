<?php
/**
 * @class 		AW_Data_Type_Category
 * @package		AutomateWoo/Data Types
 */

class AW_Data_Type_Category extends AW_Data_Type {

	/**
	 * @param $item
	 * @return bool
	 */
	function validate( $item ) {
		if ( is_object( $item ) && isset( $item->term_id ) )
			return true;
	}


	/**
	 * @param $item
	 * @return mixed
	 */
	function compress( $item ) {
		return $item->term_id;
	}


	/**
	 * @param $compressed_item
	 * @param $compressed_data_layer
	 * @return mixed
	 */
	function decompress( $compressed_item, $compressed_data_layer ) {
		return get_term( $compressed_item, 'product_cat' );
	}

}

return new AW_Data_Type_Category();
