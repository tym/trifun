<?php
/**
 * @class       AW_Trigger_Order_Processing
 * @package     AutomateWoo/Triggers
 */

class AW_Trigger_Order_Processing extends AW_Trigger_Abstract_Order_Status_Base
{

	/**
	 * @var string
	 */
	public $name = 'order_processing';

	public $_target_status = 'processing';


	/**
	 * Construct
	 */
	function init()
	{
		$this->title = __('Order Processing', 'automatewoo');

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

		if ( ! $order || ! $order->has_status( 'processing' ) )
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
			if ( $order->get_status() !== 'processing' )
				return false;
		}

		return true;
	}


}
