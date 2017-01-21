<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Compatibility Class
 *
 * @class   YITH_WCPB_Compatibility
 * @package Yithemes
 * @since   1.1.15
 * @author  Yithemes
 *
 */
class YITH_WCPB_Compatibility {

    /**
     * Single instance of the class
     *
     * @var \YITH_WCPB_Compatibility
     */
    protected static $instance;

    /**
     * Returns single instance of the class
     *
     * @return \YITH_WCPB_Compatibility
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
        $this->include_files();

        // Instances compatibility classes

        YITH_WCPB_WPML_Compatibility();
        YITH_WCPB_Dynamic_Compatibility();

        if ( self::has_plugin( 'role-based' ) ) {
            YITH_WCPB_Role_Based_Compatibility();
        }

        if ( self::has_plugin( 'request-a-quote' ) ) {
            YITH_WCPB_Request_A_Quote_Compatibility();
        }

    }

    public function include_files() {
        $dir = YITH_WCPB_INCLUDES_PATH . '/compatibility/';

        $files = array(
            $dir . 'class.yith-wcpb-wpml-compatibility.php',
            $dir . 'class.yith-wcpb-role-based-compatibility.php',
            $dir . 'class.yith-wcpb-dynamic-compatibility.php',
            $dir . 'class.yith-wcpb-request-a-quote-compatibility.php',
        );

        foreach ( $files as $file ) {
            if ( file_exists( $file ) ) {
                require_once( $file );
            }
        }
    }

    /**
     * Check if user has plugin
     *
     * @param string $plugin_name
     *
     * @author  Leanza Francesco <leanzafrancesco@gmail.com>
     * @since   1.1.15
     * @return bool
     */
    static function has_plugin( $plugin_name ) {

        switch ( $plugin_name ) {
            case 'role-based':
                return defined( 'YWCRBP_PREMIUM' ) && YWCRBP_PREMIUM && defined( 'YWCRBP_VERSION' ) && version_compare( YWCRBP_VERSION, '1.0.9', '>=' );
            case 'request-a-quote':
                return defined( 'YITH_YWRAQ_PREMIUM' ) && YITH_YWRAQ_PREMIUM && defined( 'YITH_YWRAQ_VERSION' ) && version_compare( YITH_YWRAQ_VERSION, '1.5.7', '>=' );
            default:
                return false;
        }
    }
}