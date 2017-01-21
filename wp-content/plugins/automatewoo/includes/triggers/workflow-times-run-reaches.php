<?php
/**
 * @class       AW_Trigger_Workflow_Times_Run_Reaches
 * @package     AutomateWoo/Triggers
 */

class AW_Trigger_Workflow_Times_Run_Reaches extends AW_Trigger {

	public $name = 'workflow_times_run_reaches';

	public $supplied_data_items = [ 'workflow', 'shop' ];


	function init() {
		$this->title = __('Workflow Times Run Reaches', 'automatewoo');
		$this->group = __('Workflow', 'automatewoo');

		parent::init();
	}


	/**
	 * Add options to the trigger
	 */
	function load_fields() {

		$workflow_field = new AW_Field_Workflow();

		$times_run = new AW_Field_Number_Input();
		$times_run->set_name('times_run');
		$times_run->set_title(__('Times Run', 'automatewoo') );

		$this->add_field( $workflow_field );
		$this->add_field( $times_run );
	}



	/**
	 * When could this trigger run?
	 */
	function register_hooks() {
		add_action( 'automatewoo_after_workflow_run', [ $this, 'catch_hooks' ] );
	}


	/**
	 * Route hooks through here
	 *
	 * @param $workflow AW_Model_Workflow
	 */
	function catch_hooks( $workflow ) {
		$this->maybe_run([
			'workflow' => $workflow,
			'post' => $workflow->post
		]);
	}


	/**
	 * @param AW_Model_Workflow $workflow
	 * @return bool
	 */
	function validate_workflow( $workflow ) {

		/** @var $workflow_data_item AW_Model_Workflow */
		$workflow_data_item = $workflow->get_data_item('workflow');

		$selected_workflow_id = absint( $workflow->get_trigger_option('workflow') );
		$times_run = absint( $workflow->get_trigger_option('times_run') );

		if ( ! $workflow_data_item )
			return false;

		// match running workflow to selected workflow
		if ( $workflow_data_item->id != $selected_workflow_id )
			return false;

		if ( $workflow_data_item->get_times_run() !== $times_run )
			return false;

		return true;
	}

}
