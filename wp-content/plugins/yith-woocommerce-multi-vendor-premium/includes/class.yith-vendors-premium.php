<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( !defined( 'YITH_WPV_VERSION' ) ) {
    exit( 'Direct access forbidden.' );
}

/**
 *
 *
 * @class      YITH_Vendors
 * @package    Yithemes
 * @since      Version 2.0.0
 * @author     Your Inspiration Themes
 *
 */

if ( !class_exists( 'YITH_Vendors_Premium' ) ) {
    /**
     * Class YITH_Vendors
     *
     * @author Andrea Grillo <andrea.grillo@yithemes.com>
     */
    class YITH_Vendors_Premium extends YITH_Vendors {

        /**
         * Vendors sidebar id
         */
        public $vendors_sidebar_id;

        /**
         * @var \YITH_Orders
         */
        public $orders;

        /**
         * @var \YITH_WCMV_Addons
         */
        public $addons;

        /**
         * Construct
         */
        public function __construct() {
            add_filter( 'yith_wcpv_require_class', array( $this, 'require_class' ) );
            add_filter( 'yith_vendor_commission', array( $this, 'get_commission' ), 10, 3 );
            add_filter( 'yith_wpv_register_widgets', array( $this, 'register_premium_widgets' ) );

            /* init emails */
            add_filter( 'woocommerce_email_classes', array( $this, 'register_emails' ) );
            add_filter( 'woocommerce_locate_core_template', array( $this, 'locate_core_template' ), 10, 3 );

            /* Vendor approve email */
            add_action( 'woocommerce_init', array( $this, 'load_wc_mailer' ) );

            /* Load modules */
            add_action( 'admin_menu', array( $this, 'load_modules' ), 5 );
            add_action( 'wp_loaded',  array( $this, 'load_vacation_modules' ) );

            parent::__construct();

            if ( is_admin() ) {
                $this->addons = YITH_WCMV_Addons::get_instance();
            }
        }

        /**
         * Class Initializzation
         *
         * Instance the admin or frontend classes
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since  1.0
         * @return void
         * @access protected
         */
        public function init() {
            if ( is_admin() ) {
                $this->admin = new YITH_Vendors_Admin_Premium();
            }

            if ( !is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
                $this->frontend = new YITH_Vendors_Frontend_Premium();
            }

            $this->orders = new YITH_Orders_Premium();
        }

        /**
         * Add the premium class to require array
         *
         * @param $require The required file array
         *
         * @return array The required file
         * @since  1.0
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @use    yith_wcpv_require_class filter
         */
        public function require_class( $require ) {
            /* === Load Premium Classes === */

            $require['admin'][]  = 'includes/class.yith-vendors-admin-premium.php';
            $require['admin'][]  = 'includes/class.yith-reports.php';
            $require['admin'][]  = 'includes/modules/class.yith-wcmv-addons.php';
            $require['admin'][]  = 'includes/modules/class.yith-wcmv-addons-compatibility.php';
            $require['common'][] = 'includes/class.yith-vendors-frontend-premium.php';
            $require['common'][] = 'includes/class.yith-orders-premium.php';

            /* === Load Widgets === */

            $require['common'][] = 'includes/widgets/class.yith-vendor-store-location.php';
            $require['common'][] = 'includes/widgets/class.yith-vendor-quick-info.php';

            /* === Load Shortcodes === */

            $require['frontend'][] = 'includes/shortcodes/class.yith-multi-vendor-shortcodes.php';

            return $require;
        }

        /**
         * Load plugin modules
         *
         * @return void
         * @since  1.9
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function load_modules(){
            $require = array();

            //Coupon Module
            if ( 'yes' == get_option( 'yith_wpv_vendors_option_coupon_management', 'no' ) ) {
                $require['admin'][] = 'includes/modules/module.yith-vendor-coupons.php';
            }

            //Seller Vacation Module
            if ( 'yes' == get_option( 'yith_wpv_vendors_option_seller_vacation_management', 'no' ) ) {
                $require['common'][] = 'includes/modules/module.yith-vendor-vacation.php';
            }

            //Reqeust a quote Module
            if( 'yes' == get_option( 'yith_wpv_vendors_enable_request_quote', 'no' ) && YITH_Vendors()->addons->has_plugin( 'request-quote' ) ){
                $require['admin'][] = 'includes/modules/module.yith-vendor-quote.php';
            }

            // GeoDirectory Module
            if( function_exists( 'geodir_allow_wpadmin' ) ){
                $require['admin'][] = 'includes/modules/module.yith-geodirectory-support.php';
            }

            // WP User Avatar Module
            if( class_exists( 'WP_User_Avatar_Subscriber' ) ){
                $require['admin'][] = 'includes/modules/module.yith-wp-user-avatar-support.php';
            }

            // WordPress User Frontend
            if( function_exists( 'wpuf' ) ){
                $vendor = yith_get_vendor( 'current', 'user' );
                if( $vendor->is_valid() && $vendor->has_limited_access() ){
                    remove_action( 'admin_init', array( wpuf(), 'block_admin_access' ) );
                }
            }

            // WooCommerce Customer/Order CSV Export
            if( function_exists( 'wc_customer_order_csv_export' ) ){
                $require['admin'][] = 'includes/modules/module.yith-wc-customer-order-export-support.php';
            }

            // SMS Notifications Module
            if( function_exists( 'YITH_WSN' ) ){
                $require['common'][] = 'includes/modules/module.yith-sms-notifications.php';
            }

            ! empty( $require ) && $this->_require( $require );
        }

        /**
         * Load vacation plugin modules
         *
         * @return void
         * @since  1.9
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function load_vacation_modules(){
            $require = array();

            //Seller Vacation Module
            if ( 'yes' == get_option( 'yith_wpv_vendors_option_seller_vacation_management', 'no' ) ) {
                $require['common'][] = 'includes/modules/module.yith-vendor-vacation.php';
            }

            ! empty( $require ) && $this->_require( $require );
        }

        /**
         * Main plugin Instance
         *
         * @return YITH_Vendors Main instance
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * Register Emails for Vendors
         *
         * @since  1.0.0
         * @return string The taxonomy name
         */
        public function register_emails( $emails ) {
            $emails[ 'YITH_WC_Email_Commissions_Unpaid' ]      = include( 'emails/class-yith-wc-email-commissions-unpaid.php' );
            $emails[ 'YITH_WC_Email_Commissions_Paid' ]        = include( 'emails/class-yith-wc-email-commissions-paid.php' );
            $emails[ 'YITH_WC_Email_Vendor_Commissions_Paid' ] = include( 'emails/class-yith-wc-email-vendor-commissions-paid.php' );
            $emails[ 'YITH_WC_Email_New_Vendor_Registration' ] = include( 'emails/class-yith-wc-email-new-vendor-registration.php' );
            $emails[ 'YITH_WC_Email_Vendor_New_Account' ]      = include( 'emails/class-yith-wc-email-vendor-new-account.php' );
            $emails[ 'YITH_WC_Email_New_Order' ]               = include( 'emails/class-yith-wc-email-new-order.php' );
            $emails[ 'YITH_WC_Email_Cancelled_Order' ]         = include( 'emails/class-yith-wc-email-cancelled-order.php' );

            return $emails;
        }

        /**
         * Save extra taxonomy fields for product vendors taxonomy
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         *
         * @param $commission   string The commission
         * @param $vendor_id    string The vendor id
         * @param $vendor       YITH_Vendor The vendor object
         *
         * @return string The vendor commissions
         * @since  1.0
         * @use    yith_vendor_commission filter
         */
        public function get_commission( $commission, $vendor_id, $vendor ) {
            /* Add Tag Ajax Hack */
            if ( isset( $_POST[ 'screen' ] ) && 'edit-yith_shop_vendor' == $_POST[ 'screen' ] && isset( $_POST[ 'action' ] ) && 'add-tag' == $_POST[ 'action' ] && isset( $_POST[ 'yith_vendor_data' ][ 'commission' ] ) ) {
                return $_POST[ 'yith_vendor_data' ][ 'commission' ] / 100;
            }

            return isset( $vendor->commission ) ? $vendor->commission / 100 : $commission;
        }

        /**
         * Register premium widgets
         *
         * @param $widgets The widgets to register
         *
         * @return array The widgets array
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @use      yith_wpv_register_widgets filter
         */
        public function register_premium_widgets( $widgets ) {
            $widgets[] = 'YITH_Vendor_Store_Location_Widget';
            $widgets[] = 'YITH_Vendor_Quick_Info_Widget';

            return $widgets;
        }

        /**
         * Set up array of vendor admin capabilities
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         *
         * @return array Vendor capabilities
         * @since  1.0
         */
        public function vendor_enabled_capabilities() {
            $caps = parent::vendor_enabled_capabilities();

            $live_chat_caps = $membership_caps = $size_charts_caps = $subscription_caps = $surveys_caps = array();

            /* === View Report Capability === */
            $caps[ 'view_woocommerce_reports' ] = true;

            /* === Coupon Capabilities === */
            if ( 'yes' == get_option( 'yith_wpv_vendors_option_coupon_management', 'no' ) ) {
                $caps[ 'edit_shop_coupons' ]             = true;
                $caps[ 'read_shop_coupons' ]             = true;
                $caps[ 'delete_shop_coupons' ]           = true;
                $caps[ 'publish_shop_coupons' ]          = true;
                $caps[ 'edit_published_shop_coupons' ]   = true;
                $caps[ 'delete_published_shop_coupons' ] = true;
                $caps[ 'edit_others_shop_coupons' ]      = true;
                $caps[ 'delete_others_shop_coupons' ]    = true;
            }

            /* === Product reviews === */
            if ( 'yes' == get_option( 'yith_wpv_vendors_option_review_management', 'no' ) ) {
                $caps[ 'moderate_comments' ] = true;
                $caps[ 'edit_posts' ]        = true;
            }

            if( YITH_Vendors()->addons ){
                /* === YITH Live Chat === */
                if ( YITH_Vendors()->addons->has_plugin( 'live-chat' ) && 'yes' == get_option( 'yith_wpv_vendors_option_live_chat_management', 'no' ) ) {
                    $live_chat_caps = apply_filters( 'yith_wcmv_live_chat_caps', array() );
                }

                /* === Surveys === */
                if ( YITH_Vendors()->addons->has_plugin( 'surveys' ) && 'yes' == get_option( 'yith_wpv_vendors_option_surveys_management', 'no' ) ) {
                    $surveys_caps = apply_filters( 'yith_wcmv_surveys_caps', array() );
                }
            }

            /* === Add-Ons capabilities === */
            $addons_caps = array();
            if ( YITH_Vendors()->addons->compatibility ) {
                foreach ( YITH_Vendors()->addons->compatibility->plugin_with_capabilities as $plugin_name => $plugin_options ) {
                    $slug = YITH_Vendors()->addons->compatibility->get_slug( $plugin_name );
                    if ( YITH_Vendors()->addons->has_plugin( $plugin_name ) && 'yes' == get_option( 'yith_wpv_vendors_option_' . $slug . '_management', 'no' ) ) {
                        $addons_caps = array_merge( $addons_caps, (array) $plugin_options['capabilities'] );
                    }
                }
            }

            return apply_filters( 'yith_wcmv_vendor_capabilities', array_merge( $caps, $live_chat_caps, $membership_caps, $size_charts_caps, $subscription_caps, $surveys_caps, $addons_caps ) );
        }

        /**
         * Locate core template file
         *
         * @param $core_file
         * @param $template
         * @param $template_base
         *
         * @return array Vendor capabilities
         * @since  1.0
         */
        public function locate_core_template( $core_file, $template, $template_base ) {
            $custom_template = array(
                //HTML Email
                'emails/commissions-paid.php',
                'emails/commissions-unpaid.php',
                'emails/vendor-commissions-paid.php',
                'emails/new-vendor-registration.php',
                'emails/vendor-new-account.php',
                'emails/vendor-new-order.php',
                'emails/vendor-cancelled-order.php',

                // Plain Email
                'emails/plain/commissions-paid.php',
                'emails/plain/commissions-unpaid.php',
                'emails/plain/vendor-commissions-paid.php',
                'emails/plain/new-vendor-registration.php',
                'emails/plain/vendor-new-account.php',
                'emails/plain/vendor-new-order.php',
                'emails/plain/vendor-cancelled-order.php',
            );

            if ( in_array( $template, $custom_template ) ) {
                $core_file = YITH_WPV_TEMPLATE_PATH . $template;
            }

            return $core_file;
        }

        /**
         * Loads WC Mailer when needed
         *
         * @return void
         * @since  1.0
         * @author andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function load_wc_mailer() {
            add_action( 'yith_vendors_account_approved', array( 'WC_Emails', 'send_transactional_email' ), 10 );
        }

        /**
         * Get the social fields array
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since  1.8.4
         * @return array
         */
        public function get_social_fields() {
            return apply_filters( 'yith_vendors_admin_social_fields', array(
                    'social_fields' => array(
                        'facebook'  => array(
                            'label' => __( 'Facebook', 'yith-woocommerce-product-vendors' ),
                            'icon'  => 'fa-facebook-square'
                        ),
                        'twitter'   => array(
                            'label' => __( 'Twitter', 'yith-woocommerce-product-vendors' ),
                            'icon'  => 'fa-twitter-square'
                        ),
                        'google'    => array(
                            'label' => __( 'Google+', 'yith-woocommerce-product-vendors' ),
                            'icon'  => 'fa-google-plus-square'
                        ),
                        'linkedin'  => array(
                            'label' => __( 'Linkedin', 'yith-woocommerce-product-vendors' ),
                            'icon'  => 'fa-linkedin-square'
                        ),
                        'youtube'   => array(
                            'label' => __( 'Youtube', 'yith-woocommerce-product-vendors' ),
                            'icon'  => 'fa-youtube-square'
                        ),
                        'vimeo'   => array(
                            'label' => __( 'Vimeo', 'yith-woocommerce-product-vendors' ),
                            'icon'  => 'fa-vimeo-square'
                        ),
                        'instagram' => array(
                            'label' => __( 'Instagram', 'yith-woocommerce-product-vendors' ),
                            'icon'  => 'fa-instagram'
                        ),
                        'pinterest' => array(
                            'label' => __( 'Pinterest', 'yith-woocommerce-product-vendors' ),
                            'icon'  => 'fa-pinterest-square'
                        ),
                        'flickr'    => array(
                            'label' => __( 'Flickr', 'yith-woocommerce-product-vendors' ),
                            'icon'  => 'fa-flickr'
                        ),
                        'behance'   => array(
                            'label' => __( 'Behance', 'yith-woocommerce-product-vendors' ),
                            'icon'  => 'fa-behance-square'
                        ),
                        'tripadvisor'   => array(
                            'label' => __( 'Tripadvisor  ', 'yith-woocommerce-product-vendors' ),
                            'icon'  => 'fa-tripadvisor  '
                        ),
                    )
                )
            );
        }

        /**
         * Add or Remove publish_products capabilities to vendor admins when global option change
         *
         * @return   void|string
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @since    1.0
         */
        public function force_skip_review_option( $vendors = array() ) {
            //on my signal unleash hell
            if( empty( $vendors ) ) {
                $vendors = YITH_Vendors()->get_vendors();
            }
            $skip_option = get_option( 'yith_wpv_vendors_option_skip_review', 'no' );
            $method      = 'yes' == $skip_option ? 'add_cap' : 'remove_cap';

            foreach ( $vendors as $vendor ) {
                $admin_ids = $vendor->get_admins();
                foreach ( $admin_ids as $user_id ) {
                    $user = get_user_by( 'id', $user_id );
                    $user->$method( 'publish_products' );
                }
                $vendor->skip_review = $skip_option;
            }

            if( defined( 'DOING_AJAX' ) && DOING_AJAX ){
                wp_send_json( 'complete' );
            }
        }
    }
}
