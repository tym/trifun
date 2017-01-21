<?php
/**
 * @class 		AW_Rule_User_Total_Spent
 * @package		AutomateWoo/Rules
 */

class AW_Rule_User_Total_Spent extends AW_Rule_Abstract_Number
{
	public $data_item = 'user';

	public $support_floats = true;


	/**
	 * Init
	 */
	function init()
	{
		$this->title = __( 'User Total Spent', 'automatewoo' );
		$this->group = __( 'User', 'automatewoo' );
	}


	/**
	 * @param $user WP_User
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $user, $compare, $value )
	{
		return $this->validate_number( wc_get_customer_total_spent( $user->ID ), $compare, $value );
	}

}

return new AW_Rule_User_Total_Spent();
