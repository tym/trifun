<?php
/**
 * Order helper
 *
 * Checks if an order is still pending 5 minutes after creation (such as with a PayPal transaction that was not completed)
 * and then causes and pending payment triggers to fire.
 *
 * @class 		AW_Order_Helper
 * @package		AutomateWoo
 * @since		2.4.1
 */

class AW_Order_Helper {

	/**
	 * AW_Order_Helper constructor.
	 */
	function __construct() {
		// order pending payment
		add_action( 'woocommerce_checkout_order_processed', [ $this, 'schedule_pending_check' ] );
		add_action( 'automatewoo_check_for_pending_order', [ $this, 'do_pending_check' ] );

		// refresh customer totals before our triggers fire
		add_action( 'woocommerce_order_status_changed', [ $this, 'maybe_refresh_customer_totals' ], 5, 3 );
		add_action( 'woocommerce_delete_shop_order_transients', [ $this, 'delete_shop_order_transients' ] );


		// manage _aw_last_order_placed user meta field
		add_action( 'woocommerce_order_status_changed', [ $this, 'update_user_last_order_meta' ], 250, 3 );

		add_action( 'automatewoo_updated', [ $this, 'fill_user_last_order_meta' ] );
		add_action( 'automatewoo/batch/fill_user_last_order_meta', [ $this, 'batch_fill_user_last_order_meta' ] );
	}


	/**
	 * @param $order_id int
	 */
	function schedule_pending_check( $order_id ) {
		$delay = apply_filters( 'automatewoo_order_pending_check_delay', 5 ) * 60;
		wp_schedule_single_event( time() + $delay, 'automatewoo_check_for_pending_order', [ $order_id ] );
	}


	/**
	 * @param $order_id int
	 */
	function do_pending_check( $order_id ) {

		if ( ! $order_id )
			return;

		$order = wc_get_order( $order_id );

		if ( $order->has_status( 'pending' ) ) {
			do_action( 'automatewoo_order_pending', $order_id );
		}
	}


	/**
	 * @param WC_Order $order
	 * @return AW_Model_Order_Guest|WP_User|false
	 */
	function prepare_user_data_item( $order ) {

		if ( ! $order ) {
			return false;
		}

		$user = $order->get_user();

		if ( $user ) {
			// ensure first and last name are set
			if ( ! $user->first_name ) $user->first_name = $order->billing_first_name;
			if ( ! $user->last_name ) $user->last_name = $order->billing_last_name;
			if ( ! $user->billing_phone ) $user->last_name = $order->billing_phone;
		}
		else {
			// order placed by a guest
			$user = new AW_Model_Order_Guest( $order );
		}

		return $user;
	}


	/**
	 * @param int $order_item_id
	 * @param array $order_item
	 * @return array|bool
	 */
	function prepare_order_item( $order_item_id, $order_item ) {

		if ( ! is_array( $order_item ) )
			return false;

		$order_item['id'] = $order_item_id;

		return $order_item;
	}


	/**
	 * @param $order_id
	 * @param $old_status
	 * @param $new_status
	 */
	function update_user_last_order_meta( $order_id, $old_status, $new_status ) {

		if ( ! in_array( $new_status, apply_filters( 'automatewoo/user_last_order_meta_statuses', [ 'completed', 'processing' ] ) ) )
			return;

		if ( ! $order = wc_get_order( $order_id ) )
			return;

		if ( ! $order->get_user_id() )
			return;

		update_user_meta( $order->get_user_id(), '_aw_last_order_placed', current_time( 'mysql', true ) );
	}


	/**
	 * In WC_Abstract_Order::update_status() customer totals refresh after change status hooks have fired.
	 * We need access to these for order triggers so manually refresh early.
	 * In the future order triggers could fire async which should solve this issue
	 *
	 * @param $order_id
	 * @param $old_status
	 * @param $new_status
	 */
	function maybe_refresh_customer_totals( $order_id, $old_status, $new_status ) {

		if ( ! in_array( $new_status, [ 'completed', 'processing', 'on-hold', 'cancelled' ] ) )
			return;

		if ( $order_id && ( $user_id = get_post_meta( $order_id, '_customer_user', true ) ) ) {
			delete_user_meta( $user_id, '_money_spent' );
			delete_user_meta( $user_id, '_order_count' );
		}
	}


	/**
	 * @param $order_id
	 */
	function delete_shop_order_transients( $order_id ) {
		if ( $order_id && ( $user_id = get_post_meta( $order_id, '_customer_user', true ) ) ) {
			delete_user_meta( $user_id, '_aw_order_count' );
		}
	}


	/**
	 * Fill any missing last order meta fields
	 */
	function fill_user_last_order_meta() {
		$users = get_users([
			'fields' => 'ids',
			'meta_query' => [
				[
					'key' => '_aw_last_order_placed',
					'compare' => 'NOT EXISTS'
				]
			]
		]);

		if ( $users ) {
			wp_schedule_single_event( time() + 60, 'automatewoo/batch/fill_user_last_order_meta', [ $users ] );
		}
	}


	/**
	 * @param array $users
	 */
	function batch_fill_user_last_order_meta( $users ) {

		if ( empty( $users ) )
			return;

		$batch_size = 15;

		$users_in_batch = array_slice( $users, 0, $batch_size );
		$users_remaining = array_slice( $users, $batch_size );

		foreach ( $users_in_batch as $user_id ) {
			// get the first order belonging to the user ordered by date
			$orders = wc_get_orders([
				'type' => 'shop_order',
				'limit' => 1,
				'customer' => $user_id
			]);

			if ( empty( $orders ) ) {
				update_user_meta( $user_id, '_aw_last_order_placed', false );
			}
			else {
				update_user_meta( $user_id, '_aw_last_order_placed', $orders[0]->order_date );
			}

		}

		if ( ! empty( $users_remaining ) ) {
			wp_schedule_single_event( time() + 60, 'automatewoo/batch/fill_user_last_order_meta', [ $users_remaining ] );
		}
	}

}