<?php
/**
 * @class       AW_Conversion_Manager
 * @since       2.1.0
 * @package     AutomateWoo
 */

class AW_Conversion_Manager {

	/**
	 * Max number of days for a purchase to be considered a conversion
	 * @var int
	 */
	public $conversion_window;


	/**
	 * Constructor
	 */
	function __construct() {

		$this->conversion_window = apply_filters( 'automatewoo_conversion_window', AW()->options()->conversion_window );

		add_action( 'woocommerce_checkout_order_processed', array( $this, 'check_order' ) );
	}


	/**
	 * @param $order_id
	 * @return void
	 */
	function check_order( $order_id ) {

		$order = wc_get_order( $order_id );

		if ( ! $order )
			return;

		$user = $order->get_user();

		// if there is no user check if the billing address matches any users
		if ( ! $user ) {
			$user = get_user_by( 'email', $order->billing_email );
		}

		$logs_by_user_id = [];

		if ( $user ) {
			$logs_by_user_id = $this->get_logs_by_user_id( $user->ID );
		}

		// also check for any logs matching the email address
		$logs_by_email = $this->get_logs_by_email( $order->billing_email );

		$logs = array_merge( $logs_by_email, $logs_by_user_id );

		// check that at least one logs shows that it has been opened i.e. has tracking data
		if ( $logs ) foreach ( $logs as $log ) {
			/** @var $log AW_Model_Log  */

			if ( ! $log->get_meta( 'tracking_data' ) ) {
				continue;
			}

			// has tracking data so mark the order as a conversion
			update_post_meta( $order_id, '_aw_conversion', $log->workflow_id );
			update_post_meta( $order_id, '_aw_conversion_log', $log->id );

			do_action( 'automatewoo_abandoned_cart_conversion', $order, $log );

			break; // break loop so we only mark one log as converted
		}

	}


	/**
	 * @return DateTime
	 */
	function get_conversion_window_date() {

		$date = new DateTime(); // UTC
		$date->modify("-$this->conversion_window days");
		return $date;
	}



	/**
	 * @param $email
	 *
	 * @return array
	 */
	function get_logs_by_email( $email ) {

		$query = ( new AW_Query_Logs() )
			->set_ordering('date', 'DESC')
			->where( 'conversion_tracking_enabled', true )
			->where( 'date', $this->get_conversion_window_date(), '>' )
			->where( 'guest_email', $email );

		$logs = $query->get_results();

		if ( ! $logs ) $logs = [];

		return $logs;
	}


	/**
	 * @param $id
	 *
	 * @return array
	 */
	function get_logs_by_user_id( $id ) {

		$query = ( new AW_Query_Logs() )
			->set_ordering('date', 'DESC')
			->where( 'user_id', $id )
			->where( 'conversion_tracking_enabled', true )
			->where( 'date', $this->get_conversion_window_date(), '>' );

		$logs = $query->get_results();

		if ( ! $logs ) $logs = [];

		return $logs;
	}

}
