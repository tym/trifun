<?php
/**
 * @class 		AW_Variable_User_ID
 * @package		AutomateWoo/Variables
 */

class AW_Variable_User_ID extends AW_Variable
{
	protected $name = 'user.id';

	function init()
	{
		$this->description = __( "Displays the user's unique ID.", 'automatewoo');
	}

	/**
	 * @param $user WP_User|AW_Model_Order_Guest
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $user, $parameters )
	{
		return $user->ID;
	}
}

return new AW_Variable_User_ID();
