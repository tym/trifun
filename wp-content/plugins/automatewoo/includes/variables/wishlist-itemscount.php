<?php
/**
 * @class 		AW_Variable_Wishlist_Itemscount
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Wishlist_Itemscount extends AW_Variable
{
	protected $name = 'wishlist.itemscount';

	function init()
	{
		$this->description = __( "Displays the number of items in the wishlist.", 'automatewoo');
	}

	/**
	 * @param $wishlist
	 * @param $parameters
	 * @return string
	 */
	function get_value( $wishlist, $parameters )
	{
		if ( ! is_array( $wishlist->items ) )
			return 0;

		return count( $wishlist->items );
	}
}

return new AW_Variable_Wishlist_Itemscount();
