<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * YITH WooCommerce Dynamic Pricing and Discount Compatibility Class
 *
 * @class   YITH_WCPB_Dynamic_Compatibility
 * @package Yithemes
 * @since   1.0.21
 * @author  Yithemes
 *
 */
class YITH_WCPB_Dynamic_Compatibility {

    /**
     * Single instance of the class
     *
     * @var \YITH_WCPB_Dynamic_Compatibility
     */
    protected static $instance;

    /**
     * Returns single instance of the class
     *
     * @return \YITH_WCPB_Role_Based_Compatibility
     */
    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct() {
        add_filter( 'ywdpd_get_price_exclusion', array( $this, 'dynamic_bundle_exclusion' ), 10, 3 );
    }

    /**
     * @param bool       $value
     * @param string     $price
     * @param WC_Product $product
     *
     * @return bool
     */
    public function dynamic_bundle_exclusion( $value, $price, $product ) {
        if ( $product && 'yith_bundle' === $product->product_type ) {
            return true;
        }

        return $value;
    }

}

/**
 * Unique access to instance of YITH_WCPB_Dynamic_Compatibility class
 *
 * @return YITH_WCPB_Dynamic_Compatibility
 * @since 1.0.21
 */
function YITH_WCPB_Dynamic_Compatibility() {
    return YITH_WCPB_Dynamic_Compatibility::get_instance();
}