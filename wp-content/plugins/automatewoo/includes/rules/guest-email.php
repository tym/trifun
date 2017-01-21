<?php
/**
 * @class 		AW_Rule_Guest_Email
 * @package		AutomateWoo/Rules
 */

class AW_Rule_Guest_Email extends AW_Rule_Abstract_String
{
	public $data_item = 'guest';


	/**
	 * Init
	 */
	function init()
	{
		$this->title = __( 'Guest Email', 'automatewoo' );
		$this->group = __( 'Guest', 'automatewoo' );
	}


	/**
	 * @param $guest AW_Model_Guest
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $guest, $compare, $value )
	{
		return $this->validate_string( $guest->email, $compare, $value );
	}

}

return new AW_Rule_Guest_Email();
