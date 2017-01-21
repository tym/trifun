<?php
/**
 * @class       AW_Action_Change_Workflow_Status
 * @package     AutomateWoo/Actions
 */

class AW_Action_Change_Workflow_Status extends AW_Action {

	public $name = 'change_workflow_status';

	public $required_data_items = [ 'workflow' ];


	function init() {
		$this->title = __('Change Workflow Status', 'automatewoo');
		$this->group = __('Workflow', 'automatewoo');

		parent::init();
	}


	function load_fields() {
		$status = ( new AW_Field_Select( false ) )
			->set_name('status')
			->set_title(__('Status', 'automatewoo'))
			->set_options([
				'publish' => __( 'Active', 'automatewoo' ),
				'aw-disabled' => __( 'Disabled', 'automatewoo' )
			])
			->set_required();

		$this->add_field($status);
	}


	function run() {
		$workflow = $this->workflow->get_data_item('workflow');
		$status = $this->get_option('status');

		if ( ! $status || ! $workflow )
			return;

		wp_update_post([
			'ID' => $workflow->id,
			'post_status' => $status
		]);
	}
}
