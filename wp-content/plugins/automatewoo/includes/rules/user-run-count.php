<?php
/**
 * @class 		AW_Rule_User_Run_Count
 * @package		AutomateWoo/Rules
 */

class AW_Rule_User_Run_Count extends AW_Rule_Abstract_Number
{
	public $data_item = 'user';

	public $support_floats = false;


	/**
	 * Init
	 */
	function init()
	{
		$this->title = __( 'Workflow Run Count For User', 'automatewoo' );
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
		if ( ! $workflow = $this->get_workflow() )
			return false;

		return $this->validate_number( $workflow->get_times_run_for_user( $user ), $compare, $value );
	}

}

return new AW_Rule_User_Run_Count();
