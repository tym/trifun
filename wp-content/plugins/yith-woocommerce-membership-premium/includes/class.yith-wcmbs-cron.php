<?php
/**
 * CRON
 *
 * @author  Yithemes
 * @package YITH WooCommerce Membership
 * @version 1.0.0
 */

if ( !defined( 'YITH_WCMBS' ) ) {
    exit;
} // Exit if accessed directly

if ( !class_exists( 'YITH_WCMBS_Cron' ) ) {
    /**
     * Notifier class.
     *
     * @since    1.0.0
     * @author   Leanza Francesco <leanzafrancesco@gmail.com>
     */
    class YITH_WCMBS_Cron {

        /**
         * Single instance of the class
         *
         * @var \YITH_WCMBS_Notifier
         * @since 1.0.0
         */
        protected static $instance;

        /**
         * Returns single instance of the class
         *
         * @return \YITH_WCMBS_Messages_Manager_Admin
         * @since 1.0.0
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
         * @since  1.0.0
         */
        public function __construct() {
            add_action( 'yith_wcmbs_check_expiring_membership', array( $this, 'check_expiring_membership' ) );
            add_action( 'yith_wcmbs_check_expired_membership', array( $this, 'check_expired_membership' ) );
            add_action( 'yith_wcmbs_check_credits_in_membership', array( $this, 'check_credits_in_membership' ) );

            add_action( 'wp_loaded', array( $this, 'set_cron' ), 30 );
        }

        public function set_cron() {
            if ( !wp_next_scheduled( 'yith_wcmbs_check_expiring_membership' ) ) {
                wp_schedule_event( time(), 'daily', 'yith_wcmbs_check_expiring_membership' );
            }

            if ( !wp_next_scheduled( 'yith_wcmbs_check_expired_membership' ) ) {
                wp_schedule_event( time(), 'daily', 'yith_wcmbs_check_expired_membership' );
            }

            if ( !wp_next_scheduled( 'yith_wcmbs_check_credits_in_membership' ) ) {
                wp_schedule_event( time(), 'daily', 'yith_wcmbs_check_credits_in_membership' );
            }
        }


        public function check_expiring_membership() {
            $meta_query = array(
                'relation' => 'AND',
                array(
                    'key'     => '_status',
                    'value'   => array( 'active', 'resumed' ),
                    'compare' => 'IN'
                ),
                array(
                    'key'     => '_end_date',
                    'value'   => 'unlimited',
                    'compare' => '!='
                ),
                array(
                    'key'     => '_end_date',
                    'value'   => strtotime( 'tomorrow midnight' ) + 10 * DAY_IN_SECONDS,
                    'compare' => '<='
                ),
            );

            $all_membership = YITH_WCMBS_Membership_Helper()->get_memberships_by_meta( $meta_query );

            if ( !empty( $all_membership ) ) {
                foreach ( $all_membership as $membership ) {
                    if ( $membership instanceof YITH_WCMBS_Membership ) {
                        $membership->check_is_expiring();
                    }
                }
            }
        }

        public function check_expired_membership() {
            $meta_query = array(
                'relation' => 'AND',
                array(
                    'key'     => '_status',
                    'value'   => array( 'active', 'resumed', 'expiring' ),
                    'compare' => 'IN'
                ),
                array(
                    'key'     => '_end_date',
                    'value'   => 'unlimited',
                    'compare' => '!='
                ),
                array(
                    'key'     => '_end_date',
                    'value'   => strtotime( 'tomorrow midnight' ),
                    'compare' => '<='
                ),
            );

            $all_membership = YITH_WCMBS_Membership_Helper()->get_memberships_by_meta( $meta_query );

            if ( !empty( $all_membership ) ) {
                foreach ( $all_membership as $membership ) {
                    if ( $membership instanceof YITH_WCMBS_Membership ) {
                        $membership->check_is_expired();
                    }
                }
            }
        }

        public function check_credits_in_membership() {
            $plans              = YITH_WCMBS_Manager()->plans;
            $plans_with_credits = array();

            $today = strtotime( 'now midnight' );

            foreach ( $plans as $plan ) {
                $download_limit = get_post_meta( $plan->ID, '_download-limit', true );
                if ( $download_limit > 0 ) {
                    $plans_with_credits[] = $plan->ID;
                }
            }

            if ( !empty( $plans_with_credits ) ) {
                $meta_query = array(
                    'relation' => 'AND',
                    array(
                        'key'     => '_status',
                        'value'   => array( 'active', 'resumed', 'expiring' ),
                        'compare' => 'IN'
                    ),
                    array(
                        'key'     => '_plan_id',
                        'value'   => $plans_with_credits,
                        'compare' => 'IN'
                    ),
                    array(
                        'key'     => '_next_credits_update',
                        'value'   => $today,
                        'compare' => '<='
                    ),
                );

                $all_membership = YITH_WCMBS_Membership_Helper()->get_memberships_by_meta( $meta_query );

                if ( !empty( $all_membership ) ) {
                    foreach ( $all_membership as $membership ) {
                        if ( $membership instanceof YITH_WCMBS_Membership ) {
                            $membership->check_credits();
                        }
                    }
                }
            }
        }

    }
}

/**
 * Unique access to instance of YITH_WCMBS_Cron class
 *
 * @return \YITH_WCMBS_Cron
 * @since 1.0.0
 */
function YITH_WCMBS_Cron() {
    return YITH_WCMBS_Cron::get_instance();
}

?>
