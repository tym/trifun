<?php
/**
 *
 *
 * @class       AW_Trigger_Subscription_Trial_End
 * @package     AutomateWoo/Triggers
 * @since       2.1.0
 */

class AW_Trigger_Subscription_Trial_End extends AW_Trigger_Abstract_Subscriptions
{
	public $name = 'subscription_trial_end';

	/**
	 * Construct
	 */
	function init()
	{
		$this->title = __('Subscription Trial End', 'automatewoo');

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

		$this->add_field($product);
	}



	/**
	 * When might this trigger run?
	 */
	function register_hooks()
	{
		add_action( 'woocommerce_scheduled_subscription_trial_end', array( $this, 'catch_hooks' ), 20, 1 );
	}



	/**
	 * Route hooks through here
	 *
	 * @param $subscription_id int
	 */
	function catch_hooks( $subscription_id )
	{
		$subscription = wcs_get_subscription( $subscription_id );

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

		if ( ! $this->validate_subscription_products_field( $workflow ) )
			return false;

		return true;
	}

}
