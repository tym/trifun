<?php
/**
 * @class       AW_Trigger_Subscription_Before_Renewal
 * @package     AutomateWoo/Triggers
 * @since       2.6.2
 */

class AW_Trigger_Subscription_Before_Renewal extends AW_Trigger_Abstract_Subscriptions {

	public $name = 'subscription_before_renewal';

	/**
	 * Construct
	 */
	function init() {
		$this->title = __('Subscription Before Renewal', 'automatewoo');

		parent::init();
	}


	/**
	 * Add options to the trigger
	 */
	function load_fields() {

		$product = new AW_Field_Subscription_Products();
		$product->set_description( __( 'Select which subscription products to trigger for. Leave blank to apply for all subscription products.', 'automatewoo'  ) );

		$days_before_renewal = ( new AW_Field_Number_Input() )
			->set_name( 'days_before_renewal' )
			->set_title( __( 'Days Before Renewal', 'automatewoo' ) )
			->set_required();

		$this->add_field($days_before_renewal);
		$this->add_field($product);
	}



	/**
	 * Check for renewing subscriptions one each day
	 */
	function register_hooks() {
		// use strict worker so its blocked from ever firing twice in a day
		add_action( 'automatewoo_daily_worker_strict', [ $this, 'catch_hooks' ], 20, 1 );
		add_action( 'automatewoo/batch/subscription_before_renewal', [ $this, 'process_batch' ], 10, 2 );
	}



	/**
	 * Route hooks through here
	 */
	function catch_hooks() {

		if ( ! $this->has_workflows() )
			return;

		if ( ! $workflows = $this->get_workflows() )
			return;

		foreach ( $workflows as $workflow ) {

			if ( ! $days_before_renewal = absint( $workflow->get_trigger_option('days_before_renewal') ) )
				return;

			$date = new DateTime();
			$date->modify("-$days_before_renewal days");

			// todo filter subscriptions by selected workflow trigger option
			if ( $subscription_ids = $this->get_subscriptions_by_next_payment_day( $date ) ) {
				$process = new AW_Background_Process( 'automatewoo/batch/subscription_before_renewal', $subscription_ids, [ 'workflow_id' => $workflow->id ] );
				$process->dispatch();
			}
		}
	}


	/**
	 * Return an array of subscription ids that renew on a specific date
	 *
	 * @param $date DateTime
	 * @return array
	 */
	function get_subscriptions_by_next_payment_day( $date )
	{
		$day_start = clone $date;
		$day_end = clone $date;
		$day_start->setTime(0,0,0);
		$day_end->setTime(23,59,59);

		return get_posts([
			'post_type' => 'shop_subscription',
			'post_status' => 'wc-active',
			'fields' => 'ids',
			'posts_per_page' => -1,
			'meta_query' => [
				[
					'key' => '_schedule_next_payment',
					'compare' => '>',
					'value' => $day_start->format('Y-m-d H:i:s')
				],
				[
					'key' => '_schedule_next_payment',
					'compare' => '<',
					'value' => $day_end->format('Y-m-d H:i:s')
				]
			]
		]);
	}


	/**
	 * @param $subscription_ids
	 * @param $args
	 */
	function process_batch( $subscription_ids, $args ) {

		if ( ! isset( $args['workflow_id'] ) )
			return;

		$workflow = AW()->get_workflow( absint( $args['workflow_id'] ) );

		if ( ! $workflow )
			return;

		foreach ( $subscription_ids as $subscription_id ) {
			$subscription = wcs_get_subscription( $subscription_id );

			$workflow->maybe_run([
				'subscription' => $subscription,
				'user' => $subscription->get_user()
			]);
		}
	}


	/**
	 * @param $workflow AW_Model_Workflow
	 * @return bool
	 */
	function validate_workflow( $workflow ) {

		$user = $workflow->get_data_item('user');
		$subscription = $workflow->get_data_item('subscription');

		if ( ! $user || ! $subscription )
			return false;

		if ( ! $this->validate_subscription_products_field( $workflow ) )
			return false;

		return true;
	}


	/**
	 * @param $workflow
	 * @return bool
	 */
	function validate_before_queued_event( $workflow ) {

		if ( ! parent::validate_before_queued_event( $workflow ) )
			return false;

		/** @var $subscription WC_Subscription */
		$subscription = $workflow->get_data_item('subscription');

		// only trigger for active subscriptions
		if ( ! $subscription->has_status('active') )
			return false;

		return true;
	}

}
