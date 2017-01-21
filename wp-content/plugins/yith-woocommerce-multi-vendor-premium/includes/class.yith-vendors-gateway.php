<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if( ! class_exists( 'YITH_Vendors_Gateway' ) ){
    /**
     * YITH Gateway
     *
     * Define methods and properties for class that manages admin payments
     *
     * @class      YITH_Vendors_Gateway
     * @package    Yithemes
     * @since      Version 2.0.0
     * @author     Your Inspiration Themes
     */
    class YITH_Vendors_Gateway{

        /**
         * Status for payments correctly sent
         *
         * @cont string Status for payments correctly sent
         *
         * @since 1.0
         */
        const PAYMENT_STATUS_OK = 'Success';

        /**
         * Status for payments failed
         *
         * @cont string Status for payments failed
         *
         * @since 1.0
         */
        const PAYMENT_STATUS_FAIL = 'Failure';

        /**
         * List of available gateways
         *
         * @var array Array of available gateways
         *
         * @since 1.0
         */
        public static $available_gateways = array(
            'paypal'
        );

        /**
         * Array of instances of the class, one for each available gateway
         *
         * @var mixed Array of instances of the class
         *
         * @since 1.0
         */
        static public $instances = array();

        /**
         * Name of the class of the actual used gateway
         *
         * @var string Gateway class name
         *
         * @since 1.0
         */
        public $gateway;

        /**
         * Constructor Method
         *
         * @return \YITH_Vendors_Gateway
         * @since 1.0
         * @author Antonio La Rocca <antonio.larocca@yithemes.it>
         */
        public function __construct( $gateway ){
			$this->gateway = $gateway;
        }

        /* === STATIC INITIALIZATION === */

        /**
         * Returns class name of a gateway, calculating it from slug
         *
         * @param $slug string Gateway slug
         *
         * @static
         * @return string Name of the gateway class
         * @since 1.0
         * @author Antonio La Rocca <antonio.larocca@yithemes.it>
         */
        static public function get_gateway_class_from_slug( $slug ){
            return 'YITH_Vendors_Gateway_' . str_replace( ' ', '_', ucwords( str_replace( '-', ' ', strtolower( $slug ) ) ) );
        }

        /**
         * Returns list of gateways that results enabled
         *
         * @static
         * @return array Array of enabled gateway, as slug => Class_Name
         * @since 1.0
         * @author Antonio La Rocca <antonio.larocca@yithemes.it>
         */
        static public function get_enabled_gateways(){
            $enabled_gateways = array();

            if( ! empty( self::$available_gateways ) ){
                foreach( self::$available_gateways as $gateway_slug ){
                    $enabled_gateways[ $gateway_slug ] = self::get_gateway_class_from_slug( $gateway_slug );
                }
            }

            return $enabled_gateways;
        }

        /**
         * Returns instance of the class, created specifically for the slug passed as parameter
         * Each gateway slug will generate at most one YITH_Vendors_Gateway instance
         *
         * @param $gateway string Gateway slug
         *
         * @static
         * @return \YITH_Vendors_Gateway Unique instance of the class for the passed gateway slug
         * @since 1.0
         * @author Antonio La Rocca <antonio.larocca@yithemes.it>
         */
        static public function get_instance( $gateway ){
	        if ( isset( self::$instances[ $gateway ] ) ) {
                return self::$instances[ $gateway ];
            }
            else{
	            if ( ! in_array( $gateway, self::$available_gateways ) ) {
		            return false;
	            }

	            require_once( 'gateways/class-yith-gateway-' . $gateway . '.php' );

	            if ( $class = self::get_gateway_class_from_slug( $gateway ) ) {
		            self::$instances[ $gateway ] = new $class( $gateway );

		            return self::$instances[ $gateway ];
	            }

	            return false;
            }
        }

        /* === DYNAMIC INITIALIZATION === */

        /**
         * Sends payment requests to gateway specific method
         *
         * @param $payment_detail mixed  Array used to identify payment to execute; it will be passed to gateway method, so can be anything
         *
         * @return array An array holding the status of the operation; it should have at least a boolean status, a verbose status and an array of messages
         * [
         *     status => bool (status of the operation)
         *     verbose_status => string (one between PAYMENT_STATUS_OK and PAYMENT_STATUS_FAIL)
         *     messages => string|array (one or more message describing operation status)
         * ]
         * If payment can be executed, method will return pay method result value
         * @since 1.0
         * @author Antonio La Rocca <antonio.larocca@yithemes.it>
         */
        public function pay( $payment_detail ){
            return array();
        }

	    /**
	     * Get the gateway options
	     *
	     * @return array
	     */
	    public function get_gateway_options() {
			return array();
	    }
    }
}

/**
 * Get the single instance of YITH_Vendors_Gateway_Panel class
 *
 *
 * @return \YITH_Vendors_Gateway Single instance of the class
 * @since  1.0
 * @author Antonio La Rocca <antonio.larocca@yithemes.com>
 */
function YITH_Vendors_Gateway( $gateway ){
    return YITH_Vendors_Gateway::get_instance( $gateway );
}