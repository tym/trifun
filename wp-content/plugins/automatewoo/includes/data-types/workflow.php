<?php
/**
 * @class 		AW_Data_Type_Workflow
 * @package		AutomateWoo/Data Types
 */

class AW_Data_Type_Workflow extends AW_Data_Type {

	/**
	 * @param $item
	 * @return bool
	 */
	function validate( $item ) {
		if ( $item instanceof AW_Model_Workflow )
			return true;
	}


	/**
	 * @param AW_Model_Workflow $item
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
		return new AW_Model_Workflow( $compressed_item );
	}

}

return new AW_Data_Type_Workflow();
