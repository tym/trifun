<?php
/**
 *
 *
 * @class       AW_Trigger_Subscription_Payment_Complete
 * @package     AutomateWoo/Triggers
 * @since       2.1.0
 */

class AW_Trigger_Subscription_Payment_Complete extends AW_Trigger_Abstract_Subscriptions
{
	public $name = 'subscription_payment_complete';

	/**
	 * Construct
	 */
	function init()
	{
		$this->title = __('Subscription Payment Complete', 'automatewoo');

		// Registers the trigger
		parent::init();
	}


	/**
	 * Add options to the trigger
	 */
	function load_fields()
	{
		$product = new AW_Field_Subscription_Products();
		$product->set_description( __( 'Select which subscription products to trigger for. Leave blank to apply for all subscription products.', 'automatewoo'  ) );

		$skip_first = new AW_Field_Checkbox();
		$skip_first->set_name('skip_first');
		$skip_first->set_title( __( 'Skip First Payment', 'automatewoo'  ) );

		$recheck_active = new AW_Field_Checkbox();
		$recheck_active->set_name('active_only');
		$recheck_active->set_title( __( 'Active Subscriptions Only', 'automatewoo'  ) );
		$recheck_active->set_description( __( 'This may be useful for workflows that are not run immediately as it will check that the related subscription is still active just before running.', 'automatewoo'  ) );
		$recheck_active->default_to_checked = true;

		$this->add_field($product);
		$this->add_field($recheck_active);
		$this->add_field($skip_first);
	}



	/**
	 * When might this trigger run?
	 */
	function register_hooks()
	{
		add_action( 'woocommerce_subscription_payment_complete', array( $this, 'catch_hooks' ), 20, 1 );
	}



	/**
	 * Route hooks through here
	 *
	 * @param $subscription WC_Subscription
	 */
	function catch_hooks( $subscription )
	{
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
		/** @var $subscription WC_Subscription */
		$user = $workflow->get_data_item('user');
		$subscription = $workflow->get_data_item('subscription');

		if ( ! $user || ! $subscription )
			return false;

		$skip_first = $workflow->get_trigger_option('skip_first');


		if ( $skip_first )
		{
			if ( $subscription->get_completed_payment_count() <= 1 ) return false;
		}

		if ( ! $this->validate_subscription_products_field( $workflow ) )
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

		$user = $workflow->get_data_item('user');

		/** @var $subscription WC_Subscription */
		$subscription = $workflow->get_data_item('subscription');

		if ( ! $user || ! $subscription )
			return false;

		if ( $workflow->get_trigger_option('active_only') )
		{
			if ( ! $subscription->has_status('active') )
				return false;
		}

		return true;
	}

}
