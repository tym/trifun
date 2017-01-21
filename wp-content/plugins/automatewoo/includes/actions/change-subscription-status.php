<?php
/**
 * Change Subscription Status Action
 *
 * @class       AW_Action_Change_Subscription_Status
 * @package     AutomateWoo/Actions
 * @since       2.1.0
 */

class AW_Action_Change_Subscription_Status extends AW_Action {

	public $name = 'change_subscription_status';

	public $required_data_items = [ 'subscription' ];


	function init() {
		$this->title = __( 'Change Subscription Status', 'automatewoo' );
		$this->group = __( 'Subscription', 'automatewoo' );

		// Registers the actions
		parent::init();
	}


	function load_fields() {

		$status = new AW_Field_Subscription_Status( false );
		$status->set_name('status');
		$status->set_title(__('Subscription Status', 'automatewoo') );
		$status->set_required();

		$this->add_field($status);
	}


	/**
	 * @return void
	 */
	function run() {

		/** @var $subscription WC_Subscription */
		$subscription = $this->workflow->get_data_item('subscription');
		$status = $this->get_option('status');

		if ( ! $status || ! $subscription )
			return;

		$subscription->update_status( $status, sprintf(
			__( 'Subscription status changed by AutomateWoo Workflow #%s.', 'automatewoo' ),
			$this->workflow->id
		));
	}

}
