<?php
/**
 * @class       AW_Trigger_Order_On_Hold
 * @package     AutomateWoo/Triggers
 */

class AW_Trigger_Order_On_Hold extends AW_Trigger_Abstract_Order_Status_Base
{
	public $name = 'order_on_hold';

	public $_target_status = 'on-hold';


	/**
	 * Construct
	 */
	function init()
	{
		$this->title = __('Order On Hold', 'automatewoo');

		// Registers the trigger
		parent::init();
	}



	/**
	 * @param $workflow AW_Model_Workflow
	 * @return bool
	 */
	function validate_workflow( $workflow )
	{
		/** @var WC_Order $order */
		$order = $workflow->get_data_item('order');

		if ( ! $order || ! $order->has_status( 'on-hold' ) )
			return false;

		if ( ! parent::validate_workflow( $workflow ) )
			return false;

		return true;
	}



	function validate_before_queued_event( $workflow )
	{
		// check parent
		if ( ! parent::validate_before_queued_event( $workflow ) )
			return false;

		$trigger = $workflow->get_trigger();
		$user = $workflow->get_data_item('user');
		$order = $workflow->get_data_item('order');

		if ( ! $user || ! $order )
			return false;

		// Option to validate order status
		if ( $workflow->get_trigger_option('validate_order_status_before_queued_run') )
		{
			if ( $order->get_status() !== 'on-hold' )
				return false;
		}

		return true;
	}


}
