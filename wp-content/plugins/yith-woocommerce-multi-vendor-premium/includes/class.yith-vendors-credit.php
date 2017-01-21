<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined( 'YITH_WPV_VERSION' ) ) {
    exit( 'Direct access forbidden.' );
}

if ( ! class_exists( 'YITH_Vendors_Credit' ) ) {
    /**
     * Manage the commission payments to vendors
     *
     * @class      YITH_Vendors_Credit
     * @package    Yithemes
     * @since      Version 2.0.0
     * @author     Your Inspiration Themes
     */
    class YITH_Vendors_Credit {

	    /**
	     * Main Instance
	     *
	     * @var string
	     * @since 1.0
	     * @access protected
	     */
	    protected static $_instance = null;

	    /**
	     * Gateway instance to process payment
	     *
	     * @var YITH_Vendors_Gateway_Paypal
	     * @since 1.0
	     * @access protected
	     */
	    public $gateway = null;

	    /**
	     * Gateway instance to process payment
	     *
	     * @var string
	     * @since 1.0
	     */
	    public $payment_method = null;

	    /**
	     * Main plugin Instance
	     *
	     * @static
	     * @return YITH_Vendors_Credit Main instance
	     *
	     * @since  1.0
	     */
	    public static function instance() {
		    if ( is_null( self::$_instance ) ) {
			    self::$_instance = new self();
		    }

		    return self::$_instance;
	    }

	    /**
	     * Constructor
	     *
	     * @return mixed|YITH_Vendors_Credit
	     * @since  1.0.0
	     * @access public
	     */
	    public function __construct() {
			add_action( 'woocommerce_order_status_changed', array( $this, 'process_credit' ), 20, 3 );
			add_action( 'yith_vendors_gateway_notification', array( $this, 'handle_payment_successful' ) );
			add_action( 'admin_action_pay_commission', array( $this, 'handle_single_commission_pay' ) );
			add_action( 'admin_action_pay_commissions', array( $this, 'handle_massive_commissions_pay' ) );
		    add_filter( 'yith_wcqw_panel_payments_options', array( $this, 'remove_vendor_payment_choosing' ) );

		    // enable PayPal adaptive
		    /*if ( 'yes' == get_option( 'paypal_adaptive_enable', 'yes' ) ) {
				add_filter( 'woocommerce_payment_gateways', array( $this, 'enable_paypal_adaptive' ) );
		    }*/

		    include_once( 'class.yith-vendors-gateway.php' );
		    $this->gateway = YITH_Vendors_Gateway( 'paypal' );

		    $this->payment_method = get_option( 'payment_method', 'choose' );
	    }

	    /**
	     * Pay the commission to vendor
	     *
	     * @param $order_id
	     * @param $old_status
	     * @param $new_status
	     */
	    public function process_credit( $order_id, $old_status, $new_status ) {
		    if ( 'completed' != $new_status ) {
			    return;
		    }

			$order = wc_get_order( $order_id );
		    $commissions = array();
		    $vendors_to_pay = array();
		    $data = array();

		    // retrieve the vendors to pay
		    foreach ( $order->get_items() as $item_id => $item ) {
			    if ( ! isset( $item['commission_id'] ) ) {
				    continue;
			    }

			    $commission = YITH_Commission( $item['commission_id'] );
			    $vendor = $commission->get_vendor();

			    if ( 'manual' == $this->payment_method ) {
				    continue;
			    }

			    $vendors_to_pay[ $vendor->id ] = $vendor;
		    }

		    // get the unpaid commissions for each vendor e get the amount to pay
		    foreach ( $vendors_to_pay as $vendor_id => $vendor ) {
			    /** @var YITH_Vendor $vendor */

			    // save the amount to pay for each commission of vendor
			    foreach ( $vendor->commissions_to_pay() as $commission_id ) {
				    $commission = YITH_Commission( $commission_id );
				    $data[] = array(
					    'paypal_email' => $vendor->paypal_email,
					    'amount' => round( $commission->get_amount(), 2 ),
					    'request_id' => $commission->id
				    );

				    // save the commissions with other to set 'paid' after success payment
				    $commissions[] = $commission;
			    }
		    }

		    // pay
		    $result = $this->pay( $data );

		    foreach ( $commissions as $commission ) {
			    /** @var YITH_Commission $commission */

			    // set as processing, because paypal will set as paid as soon as the transaction is completed
			    if ( $result['status'] ) {
				    $commission->update_status( 'processing' );
			    }

			    // save the error in the note
			    else {
				    $commission->add_note( sprintf( __( 'Payment failed: %s', 'yith-woocommerce-product-vendors' ), $result['messages'] ) );
			    }
		    }
	    }

	    /**
	     * Pay single commission
	     *
	     * @param $commission_id
	     * @return array|void
	     */
	    public function pay_commission( $commission_id ) {
		    $commission = YITH_Commission( $commission_id );

		    if ( ! $commission->exists() ) {
			    return;
		    }

		    $vendor = $commission->get_vendor();

		    if ( empty( $vendor->paypal_email ) ) {
			    return;
		    }

		    // set the request data
		    $data = array(
			    array(
				    'paypal_email' => $vendor->paypal_email,
				    'amount' => round( $commission->get_amount(), 2 ),
				    'request_id' => $commission->id
			    )
		    );

		    // process payment
		    $result = $this->pay( $data );

		    // set as processing, because paypal will set as paid as soon as the transaction is completed
		    if ( $result['status'] ) {
			    $commission->update_status( 'processing' );
		    }

		    // save the error in the note
		    else {
			    $commission->add_note( sprintf( __( 'Payment failed: %s', 'yith-woocommerce-product-vendors' ), $result['messages'] ) );
		    }

            return $result;
	    }

		/**
		 * Pay single commission
		 *
		 * @param $commission_id
		 * @return array|void
		 */
		public function pay_massive_commissions( $vendor_id ) {
			$commission_ids = array();

			if( empty( $_GET['commission_ids'] ) && empty( $_GET['amount'] ) ){
				return;
			}

			else {
				$commission_ids = urldecode( $_GET['commission_ids'] );
				$commission_ids = explode( ',', $commission_ids );
			}

			$vendor = yith_get_vendor( $vendor_id, 'vendor' );

			if ( ! $vendor->is_valid() ) {
				return;
			}

			if ( empty( $vendor->paypal_email ) ) {
				return;
			}

			//Insert Payment Field
			$payment_id = YITH_Commissions()->register_massive_payment( $vendor->id, $vendor->get_owner(), $_GET['amount'], $commission_ids, 'processing' );

			// set the request data
			$data = array(
				array(
					'paypal_email' => $vendor->paypal_email,
					'amount'       => round( $_GET['amount'], 2 ),
					'request_id'   => 'massive_payment_' . $payment_id
				)
			);

			// process payment
			$result = $this->pay( $data );
			
			// set as processing, because paypal will set as paid as soon as the transaction is completed
			foreach( $commission_ids as $commission_id ) {
				$commission = YITH_Commission( $commission_id );
				if( $commission->exists() ){
					if ( $result['status'] ) {
						$commission->update_status( 'processing' );
					}

					// save the error in the note
					else {
						$commission->add_note( sprintf( __( 'Payment failed: %s', 'yith-woocommerce-product-vendors' ), $result['messages'] ) );
					}
				}
			}

			return $result;
		}

	    /**
	     * Handle the single commission from commission list
	     */
	    public function handle_single_commission_pay() {
		    if ( current_user_can( 'manage_woocommerce' ) && wp_verify_nonce( $_GET['_wpnonce'], 'yith-vendors-pay-commission' ) && isset( $_GET['commission_id'] ) ) {
			    $commission_id = absint( $_GET['commission_id'] );
			    $result = $this->pay_commission( $commission_id );
                $message = $result['status'] ? 'pay-process' : 'pay-failed';
                $text    = $result['status'] ? '' : $result['messages'];
		    }

		    wp_safe_redirect( esc_url_raw( add_query_arg( array( 'message' => $message, 'text' => urlencode( $text ) ), wp_get_referer() ) ) );
		    exit();
	    }

		/**
		 * Handle the massive commission from commission list
		 */
		public function handle_massive_commissions_pay(){
			if ( current_user_can( 'manage_woocommerce' ) && wp_verify_nonce( $_GET['_wpnonce'], 'yith-vendors-pay-commissions' ) && isset( $_GET['commission_ids'] ) && isset( $_GET['vendor_id'] ) ) {
				$result = $this->pay_massive_commissions( $_GET['vendor_id'] );
				$message = $result['status'] ? 'pay-process' : 'pay-failed';
				$text    = $result['status'] ? '' : $result['messages'];
			}

			wp_safe_redirect( esc_url_raw( add_query_arg( array( 'message' => $message, 'text' => urlencode( $text ) ), wp_get_referer() ) ) );
			exit();
		}

	    /**
	     * Process the payment to paypal
	     *
	     * @param array $requests
	     *
	     * @return array
	     */
	    public function pay( $requests = array() ) {
			return $this->gateway->pay( $requests );
	    }

	    /**
	     * Process success payment
	     *
	     * @param $args
	     */
	    public function handle_payment_successful( $args ) {
		    if ( empty( $args['unique_id'] ) ) {
			    return;
		    }

		    $commission = YITH_Commission( absint( $args['unique_id'] ) );

		    // perform only if the commission is in progress
		    if ( ! $commission->has_status( 'processing' ) ) {
			    return;
		    }

		    // emails
		    WC()->mailer();

		    // if completed, set as paid
		    if ( $args['status'] == 'Completed' ) {
			    $commission->update_status( 'paid', sprintf( __( 'Commission paid via PayPal (txn ID: %s)', 'yith-woocommerce-product-vendors' ), $args['txn_id'] ) );
			    do_action( 'yith_vendors_commissions_paid', $commission );
		    }

		    // set unpaid if failed
		    else {
			    $commission->update_status( 'unpaid', sprintf( __( 'Payment %s', 'yith-woocommerce-product-vendors' ), $args['status'] ) );
			    do_action( 'yith_vendors_commissions_unpaid', $commission );
		    }
	    }

	    /**
	     * Remove the option where give the ability to vendor to choose the payment method
	     *
	     * @param array $fields
	     *
	     * @return array
	     */
	    public function remove_vendor_payment_choosing( $fields ) {
		    if ( 'choose' != $this->payment_method ) {
			    unset( $fields['payment_method'] );
		    }

		    return $fields;
	    }
    }
}

/**
 * Main instance of plugin
 *
 * @return YITH_Vendors_Credit
 * @since  1.0
 */
if ( ! function_exists( 'YITH_Vendors_Credit' ) ) {
    function YITH_Vendors_Credit() {
        return YITH_Vendors_Credit::instance();
    }
}

YITH_Vendors_Credit();
