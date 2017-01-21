<?php
/**
 * @class 		AW_Variable_User_Email
 * @package		AutomateWoo/Variables
 */

class AW_Variable_User_Email extends AW_Variable
{
	protected $name = 'user.email';

	function init()
	{
		$this->description = __( "Displays the user’s email address. Note: You can use this variable in the To field when sending emails.", 'automatewoo');
	}

	/**
	 * @param $user WP_User|AW_Model_Order_Guest
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $user, $parameters )
	{
		return $user->user_email;
	}
}

return new AW_Variable_User_Email();