<?php
/**
 * @class 		AW_Data_Type_Subscription
 * @package		AutomateWoo/Data Types
 */

class AW_Data_Type_Subscription extends AW_Data_Type {

	/**
	 * @param $item
	 * @return bool
	 */
	function validate( $item ) {
		if ( $item instanceof WC_Subscription )
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
		if ( AW()->integrations()->subscriptions_enabled() ) {
			return wcs_get_subscription( $compressed_item );
		}
	}

}

return new AW_Data_Type_Subscription();
