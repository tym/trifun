<?php
/**
 * @class       AW_Trigger_Wishlist_Item_Goes_On_Sale
 * @package     AutomateWoo/Triggers
 */

class AW_Trigger_Wishlist_Item_Goes_On_Sale extends AW_Trigger
{
	public $name = 'wishlist_item_goes_on_sale';

	public $group = 'Wishlist';

	public $supplied_data_items = [ 'user', 'shop', 'product', 'wishlist' ];

	public $allow_queueing = false;


	/**
	 * Construct
	 */
	function init()
	{
		$integration = AW()->wishlist()->get_integration();

		// don't enable triggers
		if ( ! $integration )
			return;

		$this->title = sprintf( __('Wishlist Item On Sale (%s)', 'automatewoo'), AW()->wishlist()->get_integration_title() );
		$this->description = __(
			"This trigger can't fire immediately when a product goes on sale so instead it performs a check every four hours. "
			. "Please note this doesn't work for guests because their wishlist data only exists in their session data.",
			'automatewoo');

		parent::init();
	}


	/**
	 * Add options to the trigger
	 */
	function load_fields() {}



	/**
	 * When might this trigger run?
	 */
	function register_hooks()
	{
		$integration = AW()->wishlist()->get_integration();

		if ( ! $integration )
			return;

		add_action( 'automatewoo_four_hourly_worker', array( $this, 'catch_hooks_' . $integration ) );
	}



	/**
	 * Route hooks through here
	 */
	function catch_hooks_woothemes()
	{
		if ( ! $this->has_workflows() )
			return;

		$products_on_sale = wc_get_product_ids_on_sale();

		$wishlists = get_posts(array(
			'post_type' => 'wishlist',
			'fields' => 'ids'
		));

		if ( is_array( $wishlists ) ) foreach( $wishlists as $list_id )
		{
			$products = get_post_meta( $list_id, '_wishlist_items', true );

			if ( ! $products )
				continue;

			foreach( $products as $product )
			{
				if ( in_array( $product['product_id'], $products_on_sale ) )
				{
					$user_id = get_post_meta( $list_id, '_wishlist_owner', true );
					$user = get_user_by( 'id', $user_id );

					$_product = wc_get_product( $product['product_id'] );

					$this->maybe_run(array(
						'user' => $user,
						'product' => $_product
					));
				}
			}
		}
	}


	/**
	 * Route hooks through here
	 */
	function catch_hooks_yith()
	{
		if ( ! $this->has_workflows() )
			return;

		$products_on_sale = wc_get_product_ids_on_sale();

		$wishlists = YITH_WCWL()->get_wishlists();

		if ( is_array( $wishlists ) ) foreach( $wishlists as $wishlist )
		{
			$products = YITH_WCWL()->get_products(array(
				'wishlist_id' => $wishlist['ID']
			));

			if ( ! $products )
				continue;

			foreach( $products as $product )
			{
				if ( in_array( $product['prod_id'], $products_on_sale ) )
				{
					$user = get_user_by( 'id', $wishlist['user_id'] );

					$_product = wc_get_product( $product['prod_id'] );

					$this->maybe_run(array(
						'user' => $user,
						'product' => $_product
					));
				}
			}
		}
	}



	/**
	 * @param $workflow AW_Model_Workflow
	 *
	 * @return bool
	 */
	function validate_workflow( $workflow )
	{
		$user = $workflow->get_data_item('user');
		$product = $workflow->get_data_item('product');

		if ( ! $user || ! $product )
			return false;

		// Only trigger once per user, per product, per workflow, check logs
		$log_query = ( new AW_Query_Logs() )
			->where( 'workflow_id', $workflow->id )
			->where( 'product_id', $product->id )
			->where( 'user_id', $user->ID );

		if ( $log_query->get_results() )
			return false;

		return true;
	}

}

