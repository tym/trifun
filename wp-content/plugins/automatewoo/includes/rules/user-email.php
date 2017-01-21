<?php
/**
 * @class 		AW_Rule_User_Email
 * @package		AutomateWoo/Rules
 */

class AW_Rule_User_Email extends AW_Rule_Abstract_String
{
	public $data_item = 'user';


	/**
	 * Init
	 */
	function init()
	{
		$this->title = __( 'User Email', 'automatewoo' );
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
		return $this->validate_string( $user->user_email, $compare, $value );
	}

}

return new AW_Rule_User_Email();
