<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'Direct access forbidden.' );
}

/**
 * @class      YITH_Vendor_Request_Quote
 * @package    Yithemes
 * @since      Version 1.7
 * @author     Your Inspiration Themes
 *
 */
if ( ! class_exists( 'YITH_Vendor_Request_Quote' ) && YITH_Vendors()->addons->has_plugin( 'request-quote' ) ) {

    /**
     * YITH_Vendor_Request_Quote Class
     */
    class YITH_Vendor_Request_Quote {

        /**
         * Main instance
         */
        private static $_instance = null;

        /**
         * Order quote status
         */
        public $quote_status = array();

        /**
         * Construct
         */
        public function __construct(){

            $this->quote_status = $this->get_quote_status();
        }

        public function get_quote_status( $filtered = true ){
            $raq_status = YITH_YWRAQ_Order_Request()->raq_order_status;
            if( $filtered ){
                array_walk( $raq_status, 'self::filter_status', 'wc-' );
            }
            return $raq_status;
        }

        public function is_quote( $order ) {
            $is_quote = false;

            if( ! is_object( $order ) ){
                $order_id = $order;
                $order    = wc_get_order( $order_id );
            }

            if( $order instanceof WC_Order && $order->has_status( $this->quote_status ) ) {
                $is_quote = true;
            }

            elseif( $order instanceof WP_POST && 'shop_order' == $order->post_type ) {
                $_post = $order;
                $order = wc_get_order( $_post->ID );
                $is_quote = in_array( $order->get_status(), $this->quote_status ) ? true : false;
            }

            return $is_quote;
        }

        /**
         * Main plugin Instance
         *
         * @static
         * @return YITH_Vendor_Request_Quote Main instance
         *
         * @since  1.7
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        public static function filter_status( &$status, $key, $prefix ){
            $status = 'wc-' === substr( $status, 0, 3 ) ? substr( $status, 3 ) : $status;
        }
    }
}

/**
 * Main instance of plugin
 *
 * @return /YITH_Vendor_Request_Quote
 * @since  1.9
 * @author Andrea Grillo <andrea.grillo@yithemes.com>
 */
if ( ! function_exists( 'YITH_Vendor_Request_Quote' ) ) {
    function YITH_Vendor_Request_Quote() {
        return YITH_Vendor_Request_Quote::instance();
    }
}