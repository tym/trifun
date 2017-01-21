<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * WPML Compatibility Class
 *
 * @class   YITH_WCPB_WPML_Compatibility
 * @package Yithemes
 * @since   1.0.11
 * @author  Yithemes
 *
 */
class YITH_WCPB_WPML_Compatibility {

    /**
     * Single instance of the class
     *
     * @var \YITH_WCPB_WPML_Compatibility
     */
    protected static $instance;

    /**
     * Returns single instance of the class
     *
     * @return \YITH_WCPB_WPML_Compatibility
     */
    public static function get_instance() {
        $self = __CLASS__;

        if ( is_null( $self::$instance ) ) {
            $self::$instance = new $self;
        }

        return $self::$instance;
    }

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct() {
        global $sitepress;

        if ( $sitepress ) {
            add_filter( 'wcml_exception_duplicate_products_in_cart', array( $this, 'duplicate_exception_in_cart' ), 10, 2 );

            add_action( 'woocommerce_before_calculate_totals', array( $this, 'save_temp_bundle_products' ), 101 );
            add_action( 'woocommerce_before_calculate_totals', array( $this, 'restore_temp_bundle_products' ), 999 );
        }

    }

    public function duplicate_exception_in_cart( $exclude, $cart_item ) {
        if ( isset( $cart_item[ 'bundled_items' ] ) || isset( $cart_item[ 'bundled_by' ] ) ) {
            $exclude = true;
        }

        return $exclude;
    }

    /**
     * @param WC_Cart $cart
     */
    public function save_temp_bundle_products( $cart ) {
        global $woocommerce;
        $temp_bundle_products = array();

        if ( !empty( $cart->temp_bundle_products ) ) {
            return;
        }

        foreach ( $cart->cart_contents as $key => $item ) {
            if ( isset( $item[ 'bundled_by' ] ) || isset( $item[ 'cartstamp' ] ) ) {
                $temp_bundle_products[ $key ] = $item;
                unset( $cart->cart_contents[ $key ] );
            }
        }

        $cart->temp_bundle_products = $temp_bundle_products;

        $woocommerce->session->cart = $cart;
    }

    /**
     * @param WC_Cart $cart
     */
    public function restore_temp_bundle_products( $cart ) {
        global $woocommerce;

        if ( empty( $cart->temp_bundle_products ) ) {
            return;
        }

        $temp_bundle_products = $cart->temp_bundle_products;
        unset( $cart->temp_bundle_products );

        $cart->cart_contents = array_merge( $cart->cart_contents, $temp_bundle_products );

        $woocommerce->session->cart = $cart;
    }

}

/**
 * Unique access to instance of YITH_WCPB_WPML_Compatibility class
 *
 * @return YITH_WCPB_WPML_Compatibility
 * @since 1.0.11
 */
function YITH_WCPB_WPML_Compatibility() {
    return YITH_WCPB_WPML_Compatibility::get_instance();
}