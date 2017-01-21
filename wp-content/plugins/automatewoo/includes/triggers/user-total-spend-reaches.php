<?php
/**
 * This trigger hooks in the the order completed action but will only fire once when a users total spend reaches a certain amount.
 *
 * @class       AW_Trigger_User_Total_Spend_Reaches
 * @package     AutomateWoo/Triggers
 */

class AW_Trigger_User_Total_Spend_Reaches extends AW_Trigger
{
	public $name = 'users_total_spend';

	public $group = 'User';

	public $supplied_data_items = array( 'user', 'shop' );


	function init()
	{
		$this->title = __('User Total Spend Reaches', 'automatewoo');
		$this->description = __("This trigger checks the user's total spend each time an order is completed. Note that it does not work for guests.", 'automatewoo');

		// Registers the trigger
		parent::init();
	}


	/**
	 * Add options to the trigger
	 */
	function load_fields()
	{
		$total_spend = ( new AW_Field_Number_Input() )
			->set_name( 'total_spend' )
			->set_title( __( 'Total Spend', 'automatewoo' ) )
			->set_description( __( 'No need to add a currency symbol.', 'automatewoo'  ) )
			->set_required();

		$this->add_field( $total_spend );
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

		if ( ! $total_spend = floatval( $workflow->get_trigger_option('total_spend') ) )
			return false;

		if ( wc_get_customer_total_spent( $user->ID ) <= $total_spend )
			return false;

		// Only do this once for each user (for each workflow)
		if ( ! $workflow->is_first_run_for_user( $user ) )
			return false;

		return true;
	}

}
