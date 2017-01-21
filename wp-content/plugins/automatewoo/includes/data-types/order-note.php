<?php
/**
 * @class 		AW_Data_Type_Order_Note
 * @package		AutomateWoo/Data Types
 */

class AW_Data_Type_Order_Note extends AW_Data_Type {

	/**
	 * @param $item
	 * @return bool
	 */
	function validate( $item ) {
		if ( $item instanceof AW_Model_Order_Note )
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
		if ( $comment = get_comment( $compressed_item ) ) {
			return new AW_Model_Order_Note( $comment->comment_ID, $comment->comment_content, $comment->comment_post_ID) ;
		}
	}

}

return new AW_Data_Type_Order_Note();
