<?php
/**
 * @class 		AW_Data_Type_Post
 * @package		AutomateWoo/Data Types
 */

class AW_Data_Type_Post extends AW_Data_Type {

	/**
	 * @param $item
	 * @return bool
	 */
	function validate( $item ) {
		if ( $item instanceof WP_Post )
			return true;
	}


	/**
	 * @param $item WP_Post
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
		return get_post( $compressed_item );
	}

}

return new AW_Data_Type_Post();
