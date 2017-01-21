<?php
/**
 * @class       AW_Trigger_Guest_Created
 * @package     AutomateWoo/Triggers
 * @since       2.4.9
 */

class AW_Trigger_Guest_Created extends AW_Trigger
{
	public $name = 'guest_created';

	public $supplied_data_items = [ 'guest', 'shop' ];


	function init()
	{
		$this->title = __( 'New Guest Captured', 'automatewoo' );
		$this->group = __( 'Guest', 'automatewoo' );

		parent::init();
	}


	function load_fields(){}


	/**
	 *
	 */
	function register_hooks()
	{
		add_action( 'automatewoo/session_tracker/new_stored_guest', [ $this, 'catch_hooks' ], 100, 1 );
	}


	/**
	 * @param $guest AW_Model_Guest
	 */
	function catch_hooks( $guest )
	{
		$this->maybe_run([
			'guest' => $guest
		]);
	}


	/**
	 * @param $workflow AW_Model_Workflow
	 *
	 * @return bool
	 */
	function validate_workflow( $workflow )
	{
		$guest = $workflow->get_data_item('guest');

		if ( ! $guest )
			return false;

		return true;
	}


}
