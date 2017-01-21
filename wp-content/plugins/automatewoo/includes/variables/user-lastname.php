<?php
/**
 * @class 		AW_Variable_User_Lastname
 * @package		AutomateWoo/Variables
 */

class AW_Variable_User_Lastname extends AW_Variable
{
	protected $name = 'user.lastname';

	function init()
	{
		$this->description = __( "Displays the user's last name.", 'automatewoo');
	}

	/**
	 * @param $user WP_User|AW_Model_Order_Guest
	 * @param $parameters array
	 * @param $workflow AW_Model_Workflow
	 * @return string
	 */
	function get_value( $user, $parameters, $workflow )
	{
		return $user->last_name;
	}
}

return new AW_Variable_User_Lastname();
