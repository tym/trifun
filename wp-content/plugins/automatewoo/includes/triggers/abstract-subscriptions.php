<?php
/**
 * @class       AW_Trigger_Abstract_Subscriptions
 * @package     AutomateWoo/Triggers
 * @since       2.1.0
 */

abstract class AW_Trigger_Abstract_Subscriptions extends AW_Trigger
{
	public $group = 'Subscriptions';

	public $supplied_data_items = [ 'subscription', 'user', 'shop' ];


	/**
	 * @param $workflow AW_Model_Workflow
	 * @return bool
	 */
	protected function validate_subscription_products_field( $workflow )
	{
		/** @var $subscription WC_Subscription */
		$subscription = $workflow->get_data_item('subscription');
		$subscription_products = $workflow->get_trigger_option( 'subscription_products' );

		// blank field == all
		if ( empty( $subscription_products ) )
			return true;

		$line_items = $subscription->get_items();
		$included_product_ids = [];

		foreach ( $line_items as $line_item )
		{
			$included_product_ids[] = $line_item['product_id'];
			$included_product_ids[] = $line_item['variation_id'];
		}

		if ( array_intersect( $included_product_ids, $subscription_products ) == false )
			return false;

		return true;
	}

}
