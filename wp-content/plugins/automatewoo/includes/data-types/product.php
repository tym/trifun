<?php
/**
 * @class 		AW_Data_Type_Product
 * @package		AutomateWoo/Data Types
 */

class AW_Data_Type_Product extends AW_Data_Type {

	/**
	 * @param $item
	 * @return bool
	 */
	function validate( $item ) {
		if ( is_subclass_of( $item, 'WC_Product' ) )
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
		return wc_get_product( $compressed_item );
	}

}

return new AW_Data_Type_Product();
