<?php
/**
 * @class       AW_Field_Subscription_Products
 * @package     AutomateWoo/Fields
 * @since       2.0.0
 */

class AW_Field_Subscription_Products extends AW_Field_Select {

	protected $default_name = 'subscription_products';

	public $multiple = true;


	/**
	 *
	 */
	function __construct() {

		$this->default_title = __( 'Subscription Products', 'automatewoo' );
		$options = [];

		$subscriptions = get_posts([
			'post_type' => 'product',
			'posts_per_page' => -1,
			'tax_query' => [
				[
					'taxonomy' => 'product_type',
					'field' => 'slug',
					'terms' => [
						'subscription',
						'variable-subscription'
					],
				],
			],
		]);

		foreach ( $subscriptions as $subscription_post ) {
			$subscription = wc_get_product( $subscription_post );

			$options[$subscription->id] = $subscription->get_formatted_name();

			if ( $subscription->is_type('variable-subscription') ) {
				foreach ( $subscription->get_children() as $variation_id ) {
					$variation = wc_get_product( $variation_id );
					$options[$variation_id] = $variation->get_formatted_name();
				}
			}
		}

		$this->set_options( $options );
	}

}
