<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Members Class
 *
 * @class   YITH_WCMBS_Members
 * @package Yithemes
 * @since   1.0.0
 * @author  Yithemes
 *
 */
class YITH_WCMBS_Members {

    /**
     * Single instance of the class
     *
     * @var \YITH_WCMBS_Members
     * @since 1.0.0
     */
    protected static $instance;

    /**
     * Returns single instance of the class
     *
     * @return \YITH_WCMBS_Members
     * @since 1.0.0
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
     * @since  1.0.0
     */
    public function __construct() {

    }


    /**
     * Get a Member obj by user_id
     *
     * @param $user_id int the id of the user
     *
     * @access public
     * @return YITH_WCMBS_Member
     * @since  1.0.0
     */
    public function get_member( $user_id ) {
        $member = new YITH_WCMBS_Member( $user_id );

        return $member;
    }
}

/**
 * Unique access to instance of YITH_WCMBS_Members class
 *
 * @return YITH_WCMBS_Members|YITH_WCMBS_Members_Premium
 * @since 1.0.0
 */
function YITH_WCMBS_Members() {
    if ( defined( 'YITH_WCMBS_PREMIUM' ) && YITH_WCMBS_PREMIUM ) {
        return YITH_WCMBS_Members_Premium::get_instance();
    }

    return YITH_WCMBS_Members::get_instance();
}