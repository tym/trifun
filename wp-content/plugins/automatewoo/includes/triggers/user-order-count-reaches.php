<?php
/**
 *
 *
 * @class       AW_Trigger_User_Order_Count_Reaches
 * @package     AutomateWoo/Triggers
 * @since       2.0.0
 */

class AW_Trigger_User_Order_Count_Reaches extends AW_Trigger
{
	public $name = 'users_order_count_reaches';

	public $supplied_data_items = [ 'user', 'shop' ];


	/**
	 * Construct
	 */
	function init()
	{
		$this->title = __( 'User Orders Count Reaches', 'automatewoo' );
		$this->description = __( "This trigger checks the user's order count each time an order is completed. Note that it does not work for guests.", 'automatewoo' );
		$this->group = __( 'User', 'automatewoo' );

		// Registers the trigger
		parent::init();
	}


	/**
	 * Add options to the trigger
	 */
	function load_fields()
	{
		$order_count = new AW_Field_Number_Input();
		$order_count->set_name( 'order_count' );
		$order_count->set_title( __( 'Order Count', 'automatewoo' ) );
		$order_count->set_required(true);

		$this->add_field( $order_count );
	}



	/**
	 * Must run on woocommerce_order_status_changed after customer totals have been updated
	 */
	function register_hooks()
	{
		add_action( 'woocommerce_order_status_changed', [ $this, 'catch_hooks' ], 100, 3 );
	}


	/**
	 * @param $order_id
	 * @param $old_status
	 * @param $new_status
	 */
	function catch_hooks( $order_id, $old_status, $new_status )
	{
		if ( $new_status !== 'completed' )
			return;

		$order = wc_get_order( $order_id );

		if ( $order->get_user_id() == 0 )
			return; // doesn't work for guests

		$user = AW()->order_helper->prepare_user_data_item( $order );

		$this->maybe_run(array(
			'order' => $order,
			'user' => $user
		));
	}


	/**
	 * @param $workflow AW_Model_Workflow
	 *
	 * @return bool
	 */
	function validate_workflow( $workflow )
	{
		$user = $workflow->get_data_item('user');
		$order = $workflow->get_data_item('order');

		if ( ! $user || ! $order )
			return false;

		// Options
		$order_count = absint( $workflow->get_trigger_option('order_count') );

		// fail if no order count set
		if ( ! $order_count )
			return false;

		// Only do this once for each user (for each workflow)
		if ( ! $workflow->is_first_run_for_user( $user ) )
			return false;

		// Validate order count
		if ( absint( aw_get_customer_order_count( $user->ID ) ) <= $order_count )
			return false;

		return true;
	}

}
