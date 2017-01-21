<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Compatibility Class
 *
 * @class   YITH_WCMBS_Compatibility
 * @package Yithemes
 * @since   1.0.0
 * @author  Yithemes
 *
 */
class YITH_WCMBS_Compatibility {

    /**
     * Single instance of the class
     *
     * @var \YITH_WCMBS_Compatibility
     * @since 1.0.0
     */
    protected static $instance;

    protected $_plugins = array();

    /**
     * Returns single instance of the class
     *
     * @return \YITH_WCMBS_Compatibility
     * @since 1.0.0
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
     * @since  1.0.0
     */
    public function __construct() {
        $this->_plugins = array(
            'multivendor'     => 'Multivendor',
            'subscription'    => 'Subscription',
            'dynamic-pricing' => 'Dynamic_Pricing',
        );
        $this->_load();
    }

    private function _load() {
        foreach ( $this->_plugins as $slug => $class_slug ) {
            $filename  = YITH_WCMBS_INCLUDES_PATH . '/compatibility/class.yith-wcmbs-' . $slug . '-compatibility.php';
            $classname = 'YITH_WCMBS_' . $class_slug . '_Compatibility';

            $var = str_replace( '-', '_', $slug );
            if ( $this::has_plugin( $slug ) && file_exists( $filename ) && !function_exists( $classname ) ) {
                require_once( $filename );
            }

            if ( function_exists( $classname ) ) {
                $this->$var = $classname();
            }
        }
    }

    /**
     * Check if user has a plugin
     *
     * @param string $slug
     *
     * @author Leanza Francesco <leanzafrancesco@gmail.com>
     * @return bool
     */
    public static function has_plugin( $slug ) {
        switch ( $slug ) {
            case 'dynamic-pricing':
                return defined( 'YITH_YWDPD_PREMIUM' ) && YITH_YWDPD_PREMIUM && defined( 'YITH_YWDPD_VERSION' ) && version_compare( YITH_YWDPD_VERSION, '1.1.0', '>=' );
                break;

            case 'subscription':
                return defined( 'YITH_YWSBS_PREMIUM' ) && YITH_YWSBS_PREMIUM;
                break;

            case 'multivendor':
                return defined( 'YITH_WPV_PREMIUM' ) && YITH_WPV_PREMIUM && defined( 'YITH_WPV_VERSION' ) && version_compare( YITH_WPV_VERSION, apply_filters( 'yith_wcmbs_multivendor_min_version', '1.5.0' ), '>' );
                break;

            default:
                return false;
        }
    }
}

/**
 * Unique access to instance of YITH_WCMBS_Compatibility class
 *
 * @return YITH_WCMBS_Compatibility
 * @since 1.0.0
 */
function YITH_WCMBS_Compatibility() {
    return YITH_WCMBS_Compatibility::get_instance();
}