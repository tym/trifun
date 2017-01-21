<?php
use angelleye\PayPal\PayPal;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if( ! class_exists( 'YITH_Vendors_Gateway_Paypal' ) ) {
    /**
     * YITH Gateway Paypal
     *
     * Define methods and properties for class that manages payments via paypal
     *
     * @package   YITH_Marketplace
     * @author    Your Inspiration <info@yourinspiration.it>
     * @license   GPL-2.0+
     * @link      http://yourinspirationstore.it
     * @copyright 2014 Your Inspiration
     */
    class YITH_Vendors_Gateway_Paypal extends YITH_Vendors_Gateway {

	    public function __construct( $gateway ) {
		    parent::__construct( $gateway );

		    // hook the IPNListener
		    add_action( 'init', array( $this, 'handle_notification' ), 30 );
	    }

        /* === PAYMENT METHODS === */

        /**
         * Pay method, used to process payment requests
         *
         * @param $payment_detail  array  Array of parameters for the single requests
         * Excepts at least the following parameters for each payment to process
         * [
         *     paypal_email => string (Paypal email of the receiver)
         *     amount => float (Amount to pay to user)
         *     request_id => int (Unique id of the request paid)
         * ]
         *
         * @return array An array holding the status of the operation; it should have at least a boolean status, a verbose status and an array of messages
         * [
         *     status => bool (status of the operation)
         *     verbose_status => string (one between PAYMENT_STATUS_OK and PAYMENT_STATUS_FAIL)
         *     messages => string|array (one or more message describing operation status)
         * ]
         * @since 1.0
         * @author Antonio La Rocca <antonio.larocca@yithemes.it>
         */
        public function pay( $payment_detail ){
            // include required libraries
            require_once( dirname( dirname(__FILE__) ) . '/third-party/PayPal/PayPal.php' );

            // retrieve saved options from panel
            $stored_options = $this->get_gateway_options();
	        $currency = get_woocommerce_currency();

            if ( empty( $stored_options[ 'api_username' ] ) || empty( $stored_options[ 'api_password' ] ) || empty( $stored_options[ 'api_signature' ] ) ){
                return array(
                    'status' => false,
                    'verbose_status' => YITH_Vendors_Gateway::PAYMENT_STATUS_FAIL,
                    'messages' => __( 'Missing required parameters for PayPal configuration', 'yith-woocommerce-product-vendors' )
                );
            }

            $PayPalConfig = array(
                'Sandbox' => ! ( $stored_options[ 'sandbox' ] == 'no' ),
                'APIUsername' => $stored_options['api_username'],
                'APIPassword' => $stored_options['api_password'],
                'APISignature' => $stored_options['api_signature'],
                'PrintHeaders' => true,
                'LogResults' => false,
            );

            $PayPal = new PayPal($PayPalConfig);

            // Prepare request arrays
            $MPFields = array(
                'emailsubject' => $stored_options['payment_mail_subject'], // The subject line of the email that PayPal sends when the transaction is completed.  Same for all recipients.  255 char max.
                'currencycode' => $currency,                               // Three-letter currency code.
                'receivertype' => 'EmailAddress'                           // Indicates how you identify the recipients of payments in this call to MassPay.  Must be EmailAddress or UserID
            );

            $MPItems = array();

            foreach( $payment_detail as $payment ){
                $MPItems[] = array(
                    'l_email' => $payment['paypal_email'],  // Required.  Email address of recipient.  You must specify either L_EMAIL or L_RECEIVERID but you must not mix the two.
                    'l_amt' => $payment['amount'],         // Required.  Payment amount.
                    'l_uniqueid' => $payment['request_id'] // Transaction-specific ID number for tracking in an accounting system.
                );
            }

            $PayPalRequestData = array('MPFields'=>$MPFields, 'MPItems' => $MPItems);
            $PayPalResult = $PayPal->MassPay($PayPalRequestData);

            $errors = array();
            if( $PayPalResult['ACK'] == self::PAYMENT_STATUS_FAIL ){
                foreach( $PayPalResult['ERRORS'] as $error ){
                    $errors[] = $error['L_LONGMESSAGE'];
                }
            }

            return array(
                'status' => $PayPalResult['ACK'] == self::PAYMENT_STATUS_OK,
                'verbose_status' => $PayPalResult['ACK'],
                'messages' => ( $PayPalResult['ACK'] == self::PAYMENT_STATUS_FAIL ) ? implode( "\n", $errors ) : __( 'Payment sent', 'yith-woocommerce-product-vendors' )
            );
        }

        /**
         * Method used to handle notification from paypal server
         *
         * @return void
         * @since 1.0
         * @author Antonio La Rocca <antonio.larocca@yithemes.it>
         */
        public function handle_notification(){
            if ( empty( $_GET[ 'paypal_ipn_response' ] ) ) {
	            return;
            }

	        // include required libraries
	        require( dirname( dirname(__FILE__) ) . '/third-party/IPNListener/ipnlistener.php' );

	        // retrieve saved options from panel
	        $stored_options = $this->get_gateway_options();

	        $listener = new IpnListener();
	        $listener->use_sandbox = ! ( $stored_options[ 'sandbox' ] == 'no' );

	        try {
		        // process IPN request, require validation to PayPal server
		        $listener->requirePostMethod();
		        $verified = $listener->processIpn();

	        } catch (Exception $e) {
		        // fatal error trying to process IPN.
		        die();
	        }

	        // if PayPal says IPN is valid, process content
	        if ( $verified ) {
		        $request_data = $_POST;

		        if( ! isset( $request_data['payment_status'] ) ){
			        die();
		        }

		        // format payment data
		        $payment_data = array();
		        for( $i = 1; array_key_exists( 'status_' . $i, $request_data ); $i++  ){
			        $data_index = array_keys( $request_data );

			        foreach( $data_index as $index ){
				        if( strpos( $index, '_' . $i ) !== false ){
					        $payment_data[ $i ][ str_replace( '_' . $i, '', $index ) ] = $request_data[ $index ];
					        unset( $request_data[ $index ] );
				        }
			        }
		        }

		        $request_data[ 'payment_data' ] = $payment_data;

		        if( ! empty( $payment_data ) ){
			        foreach( $payment_data as $payment ){
				        if( ! isset( $payment['unique_id'] ) ){
					        continue;
				        }

				        $args = array();
				        $args['unique_id'] = $payment['unique_id'];
				        $args['gross'] = $payment['mc_gross'];
				        $args['status'] = $payment['status'];
				        $args['receiver_email'] = $payment['receiver_email'];
				        $args['currency'] = $payment['mc_currency'];
				        $args['txn_id'] = $payment['masspay_txn_id'];

				        // call action to update request status
				        do_action( 'yith_vendors_gateway_notification', $args );
			        }
		        }

	        }

	        die();
        }

	    /**
	     * Get the gateway options
	     *
	     * @return array
	     */
	    public function get_gateway_options() {

		    $api_username  = get_option( $this->gateway . '_api_username' );
			$api_password  = get_option( $this->gateway . '_api_password' );
			$api_signature = get_option( $this->gateway . '_api_signature' );

		    // If empty, get from woocommerce settings
		    if ( empty( $api_username ) && empty( $api_password ) && empty( $api_signature ) ) {
				$gateways = WC()->payment_gateways()->get_available_payment_gateways();
			    if ( isset( $gateways['paypal'] ) ) {
				    /** @var WC_Gateway_Paypal $paypal */
				    $paypal = $gateways['paypal'];

				    $api_username  = $paypal->get_option( 'api_username' );
				    $api_password  = $paypal->get_option( 'api_password' );
				    $api_signature = $paypal->get_option( 'api_signature' );
			    }
		    }

		    $args = array(
			    'sandbox'              => get_option( $this->gateway . '_sandbox' ),
			    'api_username'         => $api_username,
			    'api_password'         => $api_password,
			    'api_signature'        => $api_signature,
			    'payment_mail_subject' => get_option( $this->gateway . '_payment_mail_subject' ),
			    'ipn_notification_url' => site_url() . '/?paypal_ipn_response=true',
		    );

		    $args = wp_parse_args( $args, array() );

		    return $args;
	    }
    }
}