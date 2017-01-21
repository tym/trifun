<?php
/**
 * @class 		AW_Variable_Guest_Email
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Guest_Email extends AW_Variable
{
	protected $name = 'guest.email';

	function init()
	{
		$this->description = __( "Displays the guest’s email address. Note: You can use this variable in the To field when sending emails.", 'automatewoo');
	}

	/**
	 * @param $guest AW_Model_Guest
	 * @param $parameters
	 * @return string
	 */
	function get_value( $guest, $parameters )
	{
		return $guest->email;
	}
}

return new AW_Variable_Guest_Email();
