<?php
/**
 * @class 		AW_Rule_User_Order_Count
 * @package		AutomateWoo/Rules
 */

class AW_Rule_User_Order_Count extends AW_Rule_Abstract_Number
{
	public $data_item = 'user';

	public $support_floats = false;


	/**
	 * Init
	 */
	function init()
	{
		$this->title = __( 'User Order Count', 'automatewoo' );
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
		return $this->validate_number( aw_get_customer_order_count( $user->ID ), $compare, $value );
	}

}

return new AW_Rule_User_Order_Count();
