<?php
if ( !defined( 'ABSPATH' ) || !defined( 'YITH_WCPB_PREMIUM' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements features of FREE version of YITH WooCommerce Product Bundles
 *
 * @class   YITH_WCPB_Premium
 * @package YITH WooCommerce Product Bundles
 * @since   1.0.0
 * @author  Yithemes
 */

if ( ! class_exists( 'YITH_WCPB_Premium' ) ) {
    /**
     * YITH WooCommerce Product Bundles
     *
     * @since 1.0.0
     */
    class YITH_WCPB_Premium extends YITH_WCPB {

        /**
         * @var YITH_WCPB_Compatibility
         */
        public $compatibility;

        /**
         * Returns single instance of the class
         *
         * @return \YITH_WCPB
         * @since 1.0.0
         */
        public static function get_instance(){
            if( is_null( self::$instance ) ){
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * Constructor
         *
         * @return mixed| YITH_WCPB_Admin | YITH_WCPB_Frontend
         * @since 1.0.0
         */
        public function __construct() {

            //parent::__construct();
            // Load Plugin Framework

            add_action( 'plugins_loaded', array( $this, 'plugin_fw_loader' ), 15 );

            // Class admin
            if ( is_admin() ) {
                YITH_WCPB_Admin_Premium();
                //YITH_WCPB_Admin();
            }
            // Class frontend
            YITH_WCPB_Frontend_Premium();

            $this->compatibility = YITH_WCPB_Compatibility::get_instance();
        }
    }
}

/**
 * Unique access to instance of YITH_WCPB_Premium class
 *
 * @return \YITH_WCPB_Premium
 * @since 1.0.0
 */
function YITH_WCPB_Premium(){
    return YITH_WCPB_Premium::get_instance();
}

?>