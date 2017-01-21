<?php
/**
 * @class 		AW_Variable_User_Meta
 * @package		AutomateWoo/Variables
 */

class AW_Variable_User_Meta extends AW_Variable_Abstract_Meta
{
	protected $name = 'user.meta';

	function init()
	{
		parent::init();

		$this->description = __( "Displays a user's meta field.", 'automatewoo');
	}

	/**
	 * @param $user WP_User
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $user, $parameters )
	{
		if ( empty( $parameters['key'] ) || $user->ID === 0 )
			return false;

		return get_user_meta( $user->ID, $parameters['key'], true );
	}
}

return new AW_Variable_User_Meta();
