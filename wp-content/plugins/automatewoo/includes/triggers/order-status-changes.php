<?php
/**
 * @class       AW_Trigger_Order_Status_Changes
 * @package     AutomateWoo/Triggers
 */

class AW_Trigger_Order_Status_Changes extends AW_Trigger_Abstract_Order_Status_Base
{

	public $name = 'order_status_changes';


	/**
	 * Construct
	 */
	function init()
	{
		$this->title = __('Order Status Changes', 'automatewoo');

		// Registers the trigger
		parent::init();
	}


	/**
	 * When could this trigger run?
	 */
	function register_hooks()
	{
		add_action( 'woocommerce_order_status_changed', array( $this, 'catch_hooks' ), 100, 3 );
		add_action( 'automatewoo_order_pending', array( $this, 'catch_hooks' ) ); // allowance for pending PayPal orders
	}


	/**
	 * Add options to the trigger
	 */
	function load_fields()
	{
		$placeholder = __( 'Leave blank for any status', 'automatewoo'  );

		$from = ( new AW_Field_Order_Status() )
			->set_title( __( 'Status Changes From', 'automatewoo'  ) )
			->set_name('order_status_from')
			->set_placeholder( $placeholder )
			->set_multiple();

		$to = ( new AW_Field_Order_Status() )
			->set_title( __( 'Status Changes To', 'automatewoo'  ) )
			->set_name('order_status_to')
			->set_placeholder( $placeholder )
			->set_multiple();

		$this->add_field($from);
		$this->add_field($to);

		parent::load_fields();
	}



	/**
	 * @param $workflow AW_Model_Workflow
	 *
	 * @return bool
	 */
	function validate_workflow( $workflow )
	{
		/**
		 * @var WC_Order $order
		 */
		$order = $workflow->get_data_item('order');

		if ( ! $order ) return false;

		// get options
		$order_status_from = $workflow->get_trigger_option('order_status_from');
		$order_status_to = $workflow->get_trigger_option('order_status_to');

		if ( ! $this->validate_status_field( $order_status_from, $order->_aw_old_status ) )
			return false;

		if ( ! $this->validate_status_field( $order_status_to, $order->get_status() ) )
			return false;

		if ( ! parent::validate_workflow( $workflow ) )
			return false;

		return true;
	}


	/**
	 * Ensures 'to' status has not changed while sitting in queue
	 *
	 * @param $workflow
	 *
	 * @return bool
	 */
	function validate_before_queued_event( $workflow )
	{
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
			$order_status_to = $workflow->get_trigger_option('order_status_to');

			if ( ! $this->validate_status_field( $order_status_to, $order->get_status() ) )
				return false;
		}

		return true;
	}


}
