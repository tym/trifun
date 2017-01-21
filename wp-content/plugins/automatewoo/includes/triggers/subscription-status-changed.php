<?php
/**
 * @class       AW_Trigger_Subscription_Status_Changed
 * @package     AutomateWoo/Triggers
 * @since       2.1.0
 */

class AW_Trigger_Subscription_Status_Changed extends AW_Trigger_Abstract_Subscriptions
{
	public $name = 'subscription_status_changed';

	/**
	 * @var bool
	 */
	public $_doing_payment = false;


	/**
	 * Construct
	 */
	function init()
	{
		$this->title = __('Subscription Status Changed', 'automatewoo');

		// Registers the trigger
		parent::init();
	}


	/**
	 * Add options to the trigger
	 */
	function load_fields()
	{
		$product = ( new AW_Field_Subscription_Products() )
			->set_description( __( 'Select which subscription products to trigger for. Leave blank to apply for all subscription products.', 'automatewoo'  ) );

		$placeholder = __( 'Leave blank for any status', 'automatewoo'  );

		$from = ( new AW_Field_Subscription_Status() )
			->set_title( __( 'Status Changes From', 'automatewoo'  ) )
			->set_name('subscription_status_from')
			->set_placeholder( $placeholder )
			->set_multiple();

		$to = ( new AW_Field_Subscription_Status() )
			->set_title( __( 'Status Changes To', 'automatewoo'  ) )
			->set_name('subscription_status_to')
			->set_placeholder( $placeholder )
			->set_multiple();

		$recheck_status = ( new AW_Field_Checkbox() )
			->set_name('validate_order_status_before_queued_run')
			->set_title("Recheck Status Before Run")
			->set_description( __( "This is useful for Workflows that are not run immediately as it ensures the status of the subscription hasn't changed since initial trigger." , 'automatewoo'  ) )
			->set_default_to_checked();

		$this->add_field( $product );
		$this->add_field( $from );
		$this->add_field( $to );
		$this->add_field( $recheck_status );
	}



	/**
	 * When might this trigger run?
	 */
	function register_hooks()
	{
		// Whenever a renewal payment is due subscription is placed on hold and then back to active if successful
		// Block this trigger while this happens
		add_action( 'woocommerce_scheduled_subscription_payment', array( $this, 'before_payment' ), 0, 1 );
		add_action( 'woocommerce_scheduled_subscription_payment', array( $this, 'after_payment' ), 1000, 1 );

		add_action( 'woocommerce_subscription_status_updated', array( $this, 'catch_hooks' ), 10, 3 );
	}


	/**
	 * @param $subscription_id
	 */
	function before_payment( $subscription_id )
	{
		$this->_doing_payment = true;
	}


	/**
	 * @param $subscription_id
	 */
	function after_payment( $subscription_id )
	{
		$this->_doing_payment = false;

		$subscription = wcs_get_subscription( $subscription_id );

		if ( ! $subscription->has_status( 'active' ) )
		{
			// if status was changed (no longer active) during payment trigger now
			$this->catch_hooks( $subscription, $subscription->get_status(), 'active' );
		}
	}


	/**
	 * Route hooks through here
	 *
	 * @param $subscription WC_Subscription
	 * @param string $old_status
	 */
	function catch_hooks( $subscription, $new_status, $old_status )
	{
		if ( $this->_doing_payment ) return;

		// bit of hack to pass in old status
		$subscription->_aw_old_status = $old_status;

		$this->maybe_run(array(
			'subscription' => $subscription,
			'user' => $subscription->get_user()
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

		/** @var $subscription WC_Subscription */
		$subscription = $workflow->get_data_item('subscription');

		if ( ! $user || ! $subscription )
			return false;

		// options
		$status_from = $workflow->get_trigger_option('subscription_status_from');
		$status_to = $workflow->get_trigger_option('subscription_status_to');

		if ( ! $this->validate_status_field( $status_from, $subscription->_aw_old_status ) )
			return false;

		if ( ! $this->validate_status_field( $status_to, $subscription->get_status() ) )
			return false;

		if ( ! $this->validate_subscription_products_field( $workflow ) )
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

		/** @var $subscription WC_Subscription */
		$subscription = $workflow->get_data_item('subscription');

		if ( ! $user || ! $subscription )
			return false;

		// Option to validate order status
		if ( $workflow->get_trigger_option('validate_order_status_before_queued_run') )
		{
			$status_to = $workflow->get_trigger_option('subscription_status_to');

			if ( ! $this->validate_status_field( $status_to, $subscription->get_status() ) )
				return false;
		}

		return true;
	}

}
