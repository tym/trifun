<?php
/**
 * @class 		AW_Variable_User_Firstname
 * @package		AutomateWoo/Variables
 */

class AW_Variable_User_Firstname extends AW_Variable
{
	protected $name = 'user.firstname';

	function init()
	{
		$this->description = __( "Displays the user's first name.", 'automatewoo');
	}

	/**
	 * @param $user WP_User|AW_Model_Order_Guest
	 * @param $parameters array
	 * @param $workflow AW_Model_Workflow
	 * @return string
	 */
	function get_value( $user, $parameters, $workflow )
	{
		return $user->first_name;
	}
}

return new AW_Variable_User_Firstname();
