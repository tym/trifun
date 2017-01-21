<?php
/**
 * @class 		AW_Preview_Data
 * @package		AutomateWoo
 * @since 		2.4.6
 */

class AW_Preview_Data {

	/**
	 * @return array
	 */
	static function get_preview_data_layer() {

		$data_layer = [];


		/**
		 * User
		 */
		$data_layer['user'] = wp_get_current_user();


		$orders = wc_get_orders([
			'type' => 'shop_order',
			'limit' => 1,
			'return' => 'ids',
		]);

		if ( ! empty( $orders ) ) {

			$data_layer['order'] = wc_get_order( $orders[0] );

			if ( $data_layer['order'] ) {
				$data_layer['order_item'] = current( $data_layer['order']->get_items() );
				$data_layer['order_item']['id'] = current( array_keys( $data_layer['order']->get_items() ) );
			}
		}


		/**
		 * Product
		 */
		$product_query = new WP_Query([
			'post_type' => 'product',
			'posts_per_page' => 4,
			//'orderby' => 'rand',
			'fields' => 'ids'
		]);
		$data_layer['product'] = wc_get_product( $product_query->posts[0] );


		/**
		 * Category
		 */
		$cats = get_terms([
			'taxonomy' => 'product_cat',
			'order' => 'count',
			'number' => 1
		]);

		$data_layer['category'] = current($cats);


		/**
		 * Cart
		 */
		$cart = new AW_Model_Abandoned_Cart();
		$cart->id = 1;
		$cart->total = 100;
		$cart->set_token();

		$cart->last_modified = current_time('mysql', true );

		$items = array();

		foreach ( $product_query->posts as $product_id )
		{
			$product = wc_get_product( $product_id );

			if ( $product->is_type('variable') )
			{
				$variations = $product->get_available_variations();
				$variation_id = $variations[0]['variation_id'];
				$variation = $variations[0]['attributes'];
			}
			else
			{
				$variation_id = 0;
				$variation = array();

			}

			$items[] = array(
				'product_id' => $product_id,
				'variation_id' => $variation_id,
				'variation' => $variation,
				'quantity' => 1,
//				'line_total' => $product->get_price(),
				'line_subtotal' => $product->get_price(),
//				'line_tax' => $product->get_price_including_tax() - $product->get_price(),
				'line_subtotal_tax' => $product->get_price_including_tax() - $product->get_price(),
			);
		}

		$cart->items = $items;

		$cart->coupons = [
			'10off' => [
				'discount_incl_tax' => '10',
				'discount_excl_tax' => '9',
				'discount_tax' => '1'
			]
		];

		$data_layer['cart'] = $cart;


		/**
		 * Wishlist
		 */
		$wishlist = new stdClass();
		$wishlist->items = $product_query->posts;

		$integration = AW()->wishlist()->get_integration();

		if ( $integration == 'yith' )
		{
			$wishlist->permalink = YITH_WCWL()->get_wishlist_url();
		}
		else
		{
			$wishlist->permalink = home_url() . '/% wishlist-url-goes-here %/';
		}

		$data_layer['wishlist'] = $wishlist;


		$guest = new AW_Model_Guest();
		$guest->email = 'guest@example.com';
		$data_layer['guest'] = $guest;


		if ( AW()->integrations()->subscriptions_enabled() )
		{
			/**
			 * Subscription
			 */
			$subscriptions = wcs_get_subscriptions(array(
				'subscriptions_per_page' => 1
			));

			$data_layer['subscription'] = current($subscriptions);
		}


		return apply_filters( 'automatewoo/preview_data_layer', $data_layer );
	}
}
