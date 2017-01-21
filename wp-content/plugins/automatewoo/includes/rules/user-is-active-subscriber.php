<?php
/**
 * @class 		AW_Rule_User_Is_Active_Subscriber
 * @package		AutomateWoo/Rules
 */

class AW_Rule_User_Is_Active_Subscriber extends AW_Rule_Abstract_Bool
{
	public $data_item = 'user';

	/**
	 * Init
	 */
	function init()
	{
		$this->title = __( "User Is Active Subscriber?", 'automatewoo' );
		$this->group = __( 'User', 'automatewoo' );
	}


	/**
	 * @param $user WP_User|AW_Model_Order_Guest
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $user, $compare, $value )
	{
		$is_subscriber = $user->ID != 0 && wcs_user_has_subscription( $user->ID, '', 'active' );

		switch ( $value )
		{
			case 'yes':
				return $is_subscriber;
				break;

			case 'no':
				return ! $is_subscriber;
				break;
		}
	}

}

return new AW_Rule_User_Is_Active_Subscriber();
