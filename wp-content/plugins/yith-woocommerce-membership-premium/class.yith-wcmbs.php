<?php
/**
 * Main class
 *
 * @author  Yithemes
 * @package YITH WooCommerce Membership
 * @version 1.0.0
 */


if ( !defined( 'YITH_WCMBS' ) ) {
    exit;
} // Exit if accessed directly

if ( !class_exists( 'YITH_WCMBS' ) ) {
    /**
     * YITH WooCommerce Membership
     *
     * @since 1.0.0
     */
    class YITH_WCMBS {

        /**
         * Single instance of the class
         *
         * @var \YITH_WCMBS
         * @since 1.0.0
         */
        protected static $instance;

        /**
         * Plugin version
         *
         * @var string
         * @since 1.0.0
         */
        public $version = YITH_WCMBS_VERSION;

        /**
         * Plugin object
         *
         * @var string
         * @since 1.0.0
         */
        public $obj = null;


        /**
         * Returns single instance of the class
         *
         * @return \YITH_WCMBS
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
         * @return mixed| YITH_WCMBS_Admin | YITH_WCMBS_Frontend
         * @since 1.0.0
         */
        public function __construct() {
            // Load Plugin Framework
            add_action( 'plugins_loaded', array( $this, 'plugin_fw_loader' ), 15 );

            add_action( 'yith_wcmbs_delete_transients', array( YITH_WCMBS_Manager(), 'delete_transients' ) );

            if ( defined( 'YITH_WCMBS_PREMIUM' ) && YITH_WCMBS_PREMIUM ) {
                YITH_WCMBS_Products_Manager();
                YITH_WCMBS_Compatibility();
                YITH_WCMBS_Protected_Media();
                YITH_WCMBS_Cron();
                YITH_WCMBS_Reports();

                YITH_WCMBS_Protected_Links::get_instance();
            }

            // Class admin
            if ( is_admin() ) {
                YITH_WCMBS_Admin();
            } else {
                YITH_WCMBS_Frontend();
            }

            YITH_WCMBS_Orders();

            // Add widget for Messages if Premium
            if ( defined( 'YITH_WCMBS_PREMIUM' ) && YITH_WCMBS_PREMIUM ) {
                /* Shortcodes Handler */
                YITH_WCMBS_Shortcodes();

                YITH_WCMBS_Messages_Manager_Frontend();
                YITH_WCMBS_Notifier();

                add_action( 'widgets_init', array( $this, 'register_widgets' ) );

                // Set membership on user registration
                add_action( 'user_register', array( $this, 'apply_membership_on_user_registration' ), 10, 1 );

                if ( version_compare( WC()->version, '2.6.0', '>=' ) ) {
                    add_filter( 'woocommerce_shipping_methods', array( $this, 'add_membership_free_shipping' ) );
                }
            }
        }

        public function add_membership_free_shipping( $methods ) {
            $methods[ 'membership_free_shipping' ] = 'WC_Shipping_Membership_Free_Shipping';

            return $methods;
        }

        /**
         * Set Membership on user registration
         *
         * @param $user_id
         */
        public function apply_membership_on_user_registration( $user_id ) {
            $plan_ids = get_option( 'yith-wcmbs-memberships-on-user-register', false );

            $apply = apply_filters( 'yith_wcmbs_apply_membership_on_user_register', true );

            if ( $apply && $plan_ids && is_array( $plan_ids ) ) {
                $plan_ids = array_map( 'absint', $plan_ids );
                $member   = YITH_WCMBS_Members()->get_member( $user_id );

                foreach ( $plan_ids as $plan_id ) {
                    $member->create_membership( $plan_id );
                }
            }
        }


        /**
         * Load Plugin Framework
         *
         * @since  1.0
         * @access public
         * @return void
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function plugin_fw_loader() {
            if ( !defined( 'YIT_CORE_PLUGIN' ) ) {
                global $plugin_fw_data;
                if ( !empty( $plugin_fw_data ) ) {
                    $plugin_fw_file = array_shift( $plugin_fw_data );
                    require_once( $plugin_fw_file );
                }
            }
        }


        /**
         * register Widget for Messages
         *
         * @access public
         * @since  1.0.0
         * @author Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function register_widgets() {
            register_widget( 'YITH_WCBSL_Messages_Widget' );
        }
    }
}

/**
 * Unique access to instance of YITH_WCMBS class
 *
 * @return \YITH_WCMBS
 * @since 1.0.0
 */
function YITH_WCMBS() {
    return YITH_WCMBS::get_instance();
}

?>