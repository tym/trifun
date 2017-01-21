<?php
/**
 * @class 		AW_Rule_User_Role
 * @package		AutomateWoo/Rules
 */

class AW_Rule_User_Role extends AW_Rule_Abstract_Select
{
	public $data_item = 'user';

	/**
	 * Init
	 */
	function init()
	{
		$this->title = __( 'User Role', 'automatewoo' );
		$this->group = __( 'User', 'automatewoo' );
	}


	/**
	 * @return array
	 */
	function get_select_choices()
	{
		if ( ! isset( $this->select_choices ) )
		{
			global $wp_roles;
			$this->select_choices = [];

			foreach( $wp_roles->roles as $key => $role )
			{
				$this->select_choices[$key] = $role['name'];
			}
		}

		return $this->select_choices;
	}


	/**
	 * @param $user WP_User|AW_Model_Order_Guest
	 * @param $compare
	 * @param $expected_value
	 * @return bool
	 */
	function validate( $user, $compare, $expected_value )
	{
		if ( $user instanceof AW_Model_Order_Guest )
			return false;

		return $this->validate_select( current( $user->roles ), $compare, $expected_value );
	}

}

return new AW_Rule_User_Role();
