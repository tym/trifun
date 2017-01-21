<?php
/**
 * @class 		AW_Variable_User_Generate_Coupon
 * @package		AutomateWoo/Variables
 */

class AW_Variable_User_Generate_Coupon extends AW_Variable_Abstract_Generate_Coupon
{
	protected $name = 'user.generate_coupon';

	/**
	 * @param $user WP_User|AW_Model_Order_Guest
	 * @param $parameters array
	 * @param $workflow AW_Model_Workflow
	 * @return string
	 */
	function get_value( $user, $parameters, $workflow )
	{
		return $this->generate_coupon( $user->user_email, $parameters, $workflow );
	}
}

return new AW_Variable_User_Generate_Coupon();

