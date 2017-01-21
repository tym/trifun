<?php
/**
 * @class 		AW_Variable_User_Order_Count
 * @package		AutomateWoo/Variables
 */

class AW_Variable_User_Order_Count extends AW_Variable
{
	protected $name = 'user.order_count';

	function init()
	{
		$this->description = __( "Displays the total number of orders a user has placed.", 'automatewoo');
	}

	/**
	 * @param $user WP_User|AW_Model_Order_Guest
	 * @param $parameters array
	 * @return int
	 */
	function get_value( $user, $parameters )
	{
		if ( $user instanceof WP_User )
		{
			return aw_get_customer_order_count( $user->ID );
		}
		elseif ( $user instanceof AW_Model_Order_Guest )
		{
			return aw_get_order_count_by_email( $user->user_email );
		}
	}
}

return new AW_Variable_User_Order_Count();