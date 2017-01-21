<?php
/**
 * @class 		AW_Data_Type_Guest
 * @package		AutomateWoo/Data Types
 */

class AW_Data_Type_Guest extends AW_Data_Type {

	/**
	 * @param $item
	 * @return bool
	 */
	function validate( $item ) {
		if ( $item instanceof AW_Model_Guest )
			return true;
	}


	/**
	 * @param AW_Model_Guest $item
	 * @return mixed
	 */
	function compress( $item ) {
		return $item->email;
	}


	/**
	 * @param $compressed_item
	 * @param $compressed_data_layer
	 * @return mixed
	 */
	function decompress( $compressed_item, $compressed_data_layer ) {

		// Guests are compressed by their email not their ID
		if ( is_email( $compressed_item ) ) {
			$guest_email = $compressed_item;
		}
		elseif ( isset( $compressed_data_layer['comment'] ) ) {
			// If there is a comment fetch the guest info from that
			$comment = get_comment( $compressed_data_layer['comment'] );
			$guest_email = $comment->comment_author_email;
		}
		else {
			return false;
		}

		$guest_email = aw_clean_email( $guest_email );

		$guest = new AW_Model_Guest();
		$guest->get_by( 'email', $guest_email );

		if ( ! $guest->exists ) {
			$guest->email = $guest_email;
		}

		// Yes we will still pass the guest object even if it doesn't exist in the database.
		// In most cases it should have been stored but there is no harm since all we need is an email

		return $guest;
	}

}

return new AW_Data_Type_Guest();
