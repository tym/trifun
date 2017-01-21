<?php
/**
 * @class 		AW_Variable_User_Username
 * @package		AutomateWoo/Variables
 */

class AW_Variable_User_Username extends AW_Variable
{
	protected $name = 'user.username';

	function init()
	{
		$this->description = __( "Displays the user's username.", 'automatewoo');
	}


	/**
	 * @param $user WP_User|AW_Model_Order_Guest
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $user, $parameters )
	{
		return $user->user_login;
	}

}

return new AW_Variable_User_Username();

