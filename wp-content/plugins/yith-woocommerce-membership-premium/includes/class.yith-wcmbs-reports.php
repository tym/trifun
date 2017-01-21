<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Members Class
 *
 * @class   YITH_WCMBS_Reports
 * @package Yithemes
 * @since   1.0.5
 * @author  Yithemes
 *
 */
class YITH_WCMBS_Reports {

    /**
     * Single instance of the class
     *
     * @var \YITH_WCMBS_Reports
     * @since 1.0.5
     */
    protected static $instance;

    /**
     * Returns single instance of the class
     *
     * @return \YITH_WCMBS_Reports
     * @since 1.0.5
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
     * @since  1.0.5
     */
    public function __construct() {
        if ( is_admin() ) {
            add_action( 'admin_menu', array( $this, 'add_reports_submenu' ) );
            add_action( 'yith_wcmbs_membership_reports', array( $this, 'render_membership_reports' ) );
            add_action( 'yith_wcmbs_download_reports', array( $this, 'render_download_reports' ) );

            add_action( 'wp_ajax_yith_wcmbs_get_download_table_reports', array( $this, 'get_download_table_reports' ) );

            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 11 );

            if ( YITH_WCMBS_Products_Manager()->is_allowed_download() ) {
                add_action( 'add_meta_boxes', array( $this, 'add_user_donwloads_metabox_in_orders' ) );
            }
        }
    }

    /**
     * Add Metaboxes
     *
     * @param string $post_type
     *
     * @since    1.0
     * @author   Leanza Francesco <leanzafrancesco@gmail.com>
     */
    public function add_user_donwloads_metabox_in_orders( $post_type ) {
        if ( $post_type == 'shop_order' ) {
            add_meta_box( 'yith-wcmbs-user-download-reports', __( 'Membership download reports', 'yith-woocommerce-membership' ), array(
                $this,
                'render_user_donwloads_metabox_in_orders'
            ), null, 'normal', 'low' );
        }
    }

    public function render_user_donwloads_metabox_in_orders( $post ) {
        $order   = wc_get_order( $post->ID );
        $user_id = $order->get_user_id();

        echo '<div class="yith-wcmbs-reports-content">';
        wc_get_template( '/reports/download-reports-graphics.php', array( 'user_id' => $user_id ), YITH_WCMBS_TEMPLATE_PATH, YITH_WCMBS_TEMPLATE_PATH );
        echo '</div>';

        echo '<div id="yith-wcmbs-reports-download-reports-table">';
        wc_get_template( '/reports/download-reports-table.php', array( 'user_id' => $user_id ), YITH_WCMBS_TEMPLATE_PATH, YITH_WCMBS_TEMPLATE_PATH );
        echo '</div>';
    }

    /**
     * add Reports Submenu to Membership Admin Menu
     */
    public function add_reports_submenu() {
        add_submenu_page( 'edit.php?post_type=yith-wcmbs-plan',     //parent_slug
                          __( 'Reports', 'yith-woocommerce-membership' ),         //page_title
                          __( 'Reports', 'yith-woocommerce-membership' ),         //menu_title
                          'edit_users',                                           // capability
                          'yith-wcmbs-reports',                                   // menu_slug
                          array( $this, 'render_reports_page' )                   // callback function
        );
    }

    public function render_reports_page() {
        wc_get_template( '/reports/reports.php', array(), YITH_WCMBS_TEMPLATE_PATH, YITH_WCMBS_TEMPLATE_PATH );
    }

    public function render_membership_reports() {
        wc_get_template( '/reports/membership-reports.php', array(), YITH_WCMBS_TEMPLATE_PATH, YITH_WCMBS_TEMPLATE_PATH );
    }

    public function render_download_reports() {
        wc_get_template( '/reports/download-reports.php', array(), YITH_WCMBS_TEMPLATE_PATH, YITH_WCMBS_TEMPLATE_PATH );
    }

    /*
     * get download table reports [AJAX]
     */
    public function get_download_table_reports() {
        wc_get_template( '/reports/download-reports-table.php', array(), YITH_WCMBS_TEMPLATE_PATH, YITH_WCMBS_TEMPLATE_PATH );
        die();
    }

    public function admin_enqueue_scripts() {
        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
        $screen = get_current_screen();

        wp_register_style( 'yith_wcmbs_menu_styles', YITH_WCMBS_ASSETS_URL . '/css/menu.css' );
        wp_register_style( 'yith_wcmbs_reports_styles', YITH_WCMBS_ASSETS_URL . '/css/reports.css' );

        wp_register_script( 'yith_wcmbs_menu_js', YITH_WCMBS_ASSETS_URL . '/js/menu' . $suffix . '.js', array( 'jquery' ), YITH_WCMBS_VERSION, true );
        wp_register_script( 'yith_wcmbs_reports_js', YITH_WCMBS_ASSETS_URL . '/js/reports' . $suffix . '.js', array( 'jquery', 'jquery-blockui' ), YITH_WCMBS_VERSION, true );

        if ( 'yith-wcmbs-plan_page_yith-wcmbs-reports' === $screen->id ) {
            wp_enqueue_style( 'yith_wcmbs_menu_styles' );
            wp_enqueue_style( 'yith_wcmbs_reports_styles' );

            wp_enqueue_script( 'yith_wcmbs_menu_js' );
            wp_enqueue_script( 'yith_wcmbs_reports_js' );
        }

    }

}

/**
 * Unique access to instance of YITH_WCMBS_Reports class
 *
 * @return YITH_WCMBS_Reports
 * @since 1.0.5
 */
function YITH_WCMBS_Reports() {
    return YITH_WCMBS_Reports::get_instance();
}