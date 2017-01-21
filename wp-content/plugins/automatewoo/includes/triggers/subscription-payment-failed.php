<?php
/**
 *
 *
 * @class       AW_Trigger_Subscription_Payment_Failed
 * @package     AutomateWoo/Triggers
 * @since       2.1.0
 */

class AW_Trigger_Subscription_Payment_Failed extends AW_Trigger_Abstract_Subscriptions
{
	public $name = 'subscription_payment_failed';

	/**
	 * Construct
	 */
	function init()
	{
		$this->title = __('Subscription Payment Failed', 'automatewoo');

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

		$this->add_field($product);
		$this->add_field($skip_first);
	}



	/**
	 * When might this trigger run?
	 */
	function register_hooks()
	{
		add_action( 'woocommerce_subscription_payment_failed', array( $this, 'catch_hooks' ), 20, 1 );
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
		$user = $workflow->get_data_item('user');

		/** @var $subscription WC_Subscription */
		$subscription = $workflow->get_data_item('subscription');

		if ( ! $user || ! $subscription )
			return false;

		// options
		$skip_first = $workflow->get_trigger_option('skip_first');


		if ( $skip_first )
		{
			// since this is a failed payment trigger the payment count only needs greater than 0
			if ( $subscription->get_completed_payment_count() == 0 ) return false;
		}

		if ( ! $this->validate_subscription_products_field( $workflow ) )
			return false;

		return true;
	}

}
