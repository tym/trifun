<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Orders Class
 *
 * @class   YITH_WCMBS_Orders_Premium
 * @package Yithemes
 * @since   1.2.6
 * @author  Yithemes
 *
 */
class YITH_WCMBS_Orders_Premium extends YITH_WCMBS_Orders {

    /**
     * Single instance of the class
     *
     * @var \YITH_WCMBS_Orders_Premium
     */
    protected static $instance;

    /**
     * Returns single instance of the class
     *
     * @return \YITH_WCMBS_Manager
     */
    public static function get_instance() {
        $self = __CLASS__ . ( class_exists( __CLASS__ . '_Premium' ) ? '_Premium' : '' );

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
        parent::__construct();
    }

    /**
     * set user membership when order is completed
     *
     * @param int $order_id id of order
     *
     * @access public
     * @since  1.0.0
     * @author Leanza Francesco <leanzafrancesco@gmail.com>
     */
    public function set_user_membership( $order_id ) {
        $memberships = YITH_WCMBS_Membership_Helper()->get_memberships_by_order( $order_id );

        if ( !empty( $memberships ) ) {
            foreach ( $memberships as $membership ) {
                if ( $membership instanceof YITH_WCMBS_Membership && $membership->status == 'not_active' ) {
                    $membership->update_status( 'resumed' );
                }
            }
        } else {
            $plan_product_ids    = array();
            $plans_product_array = array();
            $plans               = YITH_WCMBS_Manager()->plans;

            if ( !empty( $plans ) ) {
                foreach ( $plans as $plan ) {
                    $member_product_id = get_post_meta( $plan->ID, '_membership-product', true );
                    if ( $member_product_id ) {
                        $plan_product_ids[]                        = $member_product_id;
                        $plans_product_array[ $member_product_id ] = $plan;
                    }
                }
            }

            $order   = wc_get_order( $order_id );
            $user_id = $order->get_user_id();

            $member = YITH_WCMBS_Members()->get_member( $user_id );

            foreach ( $order->get_items() as $order_item_id => $item ) {
                $id = !empty( $item[ 'variation_id' ] ) ? $item[ 'variation_id' ] : $item[ 'product_id' ];

                /*
                 * if this product is subscription, no actions!
                 * Subscription plugin will manage the membership activation
                 */
                if ( YITH_WCMBS_Compatibility::has_plugin( 'subscription' ) ) {
                    if ( YITH_WC_Subscription()->is_subscription( $id ) ) {
                        continue;
                    }
                }

                $create_membership = true;
                $create_membership = apply_filters( 'yith_wcmbs_create_membership', $create_membership, $id, $order, $plan_product_ids );

                if ( !$create_membership ) {
                    continue;
                }

                if ( in_array( $id, $plan_product_ids ) ) {
                    $plan_post = $plans_product_array[ $id ];

                    $member->create_membership( $plan_post->ID, $order_id, $order_item_id );
                }
            }
        }
    }
}