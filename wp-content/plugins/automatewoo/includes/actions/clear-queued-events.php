<?php
/**
 *	@class 		AW_Action_Clear_Queued_Events
 * @package		AutomateWoo/Actions
 * @since 		2.2
 */

class AW_Action_Clear_Queued_Events extends AW_Action {

	public $name = 'clear_queued_events';

	function init() {
		$this->title = __( 'Clear Queued Events', 'automatewoo' );
		$this->group = __( 'AutomateWoo', 'automatewoo' );
		$this->description = __(
			'Deletes any queued events for selected workflows that belong to a user or guest. If you are clearing the queue and ' .
			'creating new queued events at the same time you will probably want to use the workflow order field so this action ' .
			'runs before new queued events are created.',
			'automatewoo');

		parent::init();
	}


	function load_fields() {

		$workflows = new AW_Field_Workflow();
		$workflows->set_required();
		$workflows->multiple = true;

		$user = new AW_Field_Text_Input();
		$user->set_name('email');
		$user->set_title( __( 'Email (User / Guest)', 'automatewoo' ) );
		$user->set_placeholder( __( 'Clear by email address...', 'automatewoo' ) );
		$user->set_variable_validation();
		$user->set_required(true);

		$this->add_field($workflows);
		$this->add_field($user);
	}


	/**
	 * @return void
	 */
	function run() {
		$email = $this->get_option( 'email', true );
		$workflows = $this->get_option('workflow');

		if ( empty( $workflows ) || ! $email )
			return;

		$query = ( new AW_Query_Queue() )
			->where( 'workflow_id', $workflows );

		$results = $query->get_results();

		if ( $results ) foreach ( $results as $result ) {
			/** @var $result AW_Model_Queued_Event */
			$data_items = $result->get_data_layer();

			if ( $data_items['user'] ) {
				if ( $data_items['user']->user_email == $email ) {
					$result->delete();
				}
			}
			elseif ( $data_items['guest'] instanceof AW_Model_Guest ) {
				if ( $data_items['guest']->email == $email ) {
					$result->delete();
				}
			}
		}
	}

}