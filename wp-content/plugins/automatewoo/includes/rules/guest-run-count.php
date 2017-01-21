<?php
/**
 * @class 		AW_Rule_Guest_Run_Count
 * @package		AutomateWoo/Rules
 */

class AW_Rule_Guest_Run_Count extends AW_Rule_Abstract_Number
{
	public $data_item = 'guest';

	public $support_floats = false;


	/**
	 * Init
	 */
	function init()
	{
		$this->title = __( 'Workflow Run Count For Guest', 'automatewoo' );
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
		if ( ! $workflow = $this->get_workflow() )
			return false;

		return $this->validate_number( $workflow->get_times_run_for_guest( $guest ), $compare, $value );
	}

}

return new AW_Rule_Guest_Run_Count();
