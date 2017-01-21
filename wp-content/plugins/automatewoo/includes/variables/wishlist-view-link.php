<?php
/**
 * @class 		AW_Variable_Wishlist_View_Link
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Wishlist_View_Link extends AW_Variable
{
	protected $name = 'wishlist.view_link';

	function init()
	{
		$this->description = __( "Displays a link to the wishlist.", 'automatewoo');
	}


	/**
	 * @param $wishlist
	 * @param $parameters
	 * @return string
	 */
	function get_value( $wishlist, $parameters )
	{
		return $wishlist->permalink;
	}

}

return new AW_Variable_Wishlist_View_Link();
