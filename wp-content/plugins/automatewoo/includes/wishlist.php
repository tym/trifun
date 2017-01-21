<?php
/**
 * @class AW_Wishlist
 */

class AW_Wishlist {

	/**
	 * @var array
	 */
	public $integration_options = [
		'yith' => 'YITH Wishlists',
		'woothemes' => 'WooThemes Wishlists'
	];


	/**
	 * @return string|false
	 */
	function get_integration() {
		if ( class_exists( 'WC_Wishlists_Plugin') ) {
			return 'woothemes';
		}
		elseif ( class_exists( 'YITH_WCWL') ) {
			return 'yith';
		}
		else {
			return false;
		}
	}


	/**
	 * @return string|false
	 */
	function get_integration_title() {
		$integration = $this->get_integration();

		if ( ! $integration )
			return false;

		return $this->integration_options[$integration];
	}


	/**
	 * Get wishlist by ID
	 *
	 * @param int $id
	 */
	function get_wishlist( $id ) {

		$integration = $this->get_integration();

		if ( ! $id || ! $integration )
			return false;

		if ( $integration == 'yith' ) {
			$wishlist = YITH_WCWL()->get_wishlist_detail( $id );
		}
		elseif ( $integration == 'woothemes' ) {
			$wishlist = get_post($id);
		}
		else {
			return false;
		}

		return $this->get_normalized_wishlist( $wishlist );
	}



	/**
	 * Convert wishlist objects from both integrations into the same format
	 *
	 * Returns false if wishlist is empty
	 *
	 * @param $wishlist object|array
	 *
	 * @return object|false
	 */
	function get_normalized_wishlist( $wishlist ) {

		$integration = $this->get_integration();

		if ( ! $wishlist || ! $integration )
			return false;


		$normalized_wishlist = new stdClass();
		$normalized_wishlist->integration = $integration;


		if ( $integration == 'yith' ) {
			/**
			 * YITH
			 */
			if ( ! is_array($wishlist) )
				return false;

			$normalized_wishlist->id = $wishlist['ID'];
			$normalized_wishlist->owner_id = $wishlist['user_id'];

			// convert wishlist items to ids only
			$product_ids = array();

			$products = YITH_WCWL()->get_products(array(
				'wishlist_id' => $wishlist['ID'],
				'user_id' => $normalized_wishlist->owner_id
			));

			// wishlist is empty
			if ( empty( $products ) )
				return false;

			foreach( $products as $product ) {
				$product_ids[] = $product['prod_id'];
			}

			$normalized_wishlist->items = $product_ids;

			$normalized_wishlist->date_created = get_date_from_gmt( $products[0]['dateadded'] );

			$normalized_wishlist->permalink = YITH_WCWL()->get_wishlist_url();
		}
		elseif ( $integration == 'woothemes' ) {
			/**
			 * WooThemes
			 */
			if ( ! $wishlist instanceof WP_Post )
				return false;

			// convert wishlist items to ids only
			$product_ids = array();

			$products = get_post_meta( $wishlist->ID, '_wishlist_items', true );

			// wishlist is empty
			if ( ! $products )
				return false;

			foreach ( $products as $product ) {
				$product_ids[] = $product['product_id'];
			}

			$product_ids = array_unique( $product_ids );


			$normalized_wishlist->id = $wishlist->ID;
			$normalized_wishlist->owner_id = get_post_meta( $wishlist->ID, '_wishlist_owner', true );

			$normalized_wishlist->items = $product_ids;
			$normalized_wishlist->date_created = $wishlist->post_date;


			if ( class_exists('WC_Wishlists_Pages') ) {
				$normalized_wishlist->permalink = add_query_arg( array( 'wlid' => $normalized_wishlist->id ), WC_Wishlists_Pages::get_url_for('view-a-list') );
			}
		}

		return $normalized_wishlist;
	}

}
