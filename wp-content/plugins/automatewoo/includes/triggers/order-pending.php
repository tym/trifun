<?php
/**
 * @class       AW_Trigger_Order_Pending
 * @package     AutomateWoo/Triggers
 */

class AW_Trigger_Order_Pending extends AW_Trigger_Abstract_Order_Status_Base {

	public $name = 'order_pending';

	public $_target_status = 'pending';



	function init() {
		$this->title = __('Order Pending Payment', 'automatewoo');

		// Registers the trigger
		parent::init();
	}


	/**
	 * When could this trigger run?
	 */
	function register_hooks() {
		add_action( 'woocommerce_order_status_changed', [ $this, 'catch_hooks' ], 100, 3 );
		add_action( 'automatewoo_order_pending', [ $this, 'catch_hooks' ] ); // allowance for pending PayPal orders
	}


	/**
	 * @param $workflow AW_Model_Workflow
	 * @return bool
	 */
	function validate_workflow( $workflow ) {

		/** @var WC_Order $order */
		$order = $workflow->get_data_item('order');

		if ( ! $order || ! $order->has_status( 'pending' ) )
			return false;

		if ( ! parent::validate_workflow( $workflow ) )
			return false;

		return true;
	}


	/**
	 * @param $workflow
	 *
	 * @return bool
	 */
	function validate_before_queued_event( $workflow ) {
		// check parent
		if ( ! parent::validate_before_queued_event( $workflow ) )
			return false;

		$user = $workflow->get_data_item('user');
		$order = $workflow->get_data_item('order');

		if ( ! $user || ! $order )
			return false;

		// Option to validate order status
		if ( $workflow->get_trigger_option('validate_order_status_before_queued_run') )
		{
			if ( $order->get_status() !== 'pending' )
				return false;
		}

		return true;
	}


}
