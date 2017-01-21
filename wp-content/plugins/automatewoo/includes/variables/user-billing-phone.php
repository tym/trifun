<?php
/**
 * @class 		AW_Variable_User_Billing_Phone
 * @package		AutomateWoo/Variables
 */

class AW_Variable_User_Billing_Phone extends AW_Variable
{
	protected $name = 'user.billing_phone';

	function init()
	{
		$this->description = __( "Displays the user's billing phone number.", 'automatewoo');
	}

	/**
	 * @param $user WP_User|AW_Model_Order_Guest
	 * @param $parameters array
	 * @param $workflow AW_Model_Workflow
	 * @return string
	 */
	function get_value( $user, $parameters, $workflow )
	{
		return $user->billing_phone;
	}
}

return new AW_Variable_User_Billing_Phone();
