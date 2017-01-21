<?php
/**
 * @class 		AW_Variable_User_Total_Spent
 * @package		AutomateWoo/Variables
 */

class AW_Variable_User_Total_Spent extends AW_Variable
{
	protected $name = 'user.total_spent';

	function init()
	{
		$this->description = __( "Displays the formatted total spent.", 'automatewoo');
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
			return wc_price( wc_get_customer_total_spent( $user->ID ) );
		}
		elseif ( $user instanceof AW_Model_Order_Guest )
		{
			return aw_get_total_spent_by_email( $user->user_email );
		}
	}
}

return new AW_Variable_User_Total_Spent();