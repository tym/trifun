<?php
/**
 * Admin class
 *
 * @author  Yithemes
 * @package YITH WooCommerce Membership
 * @version 1.0.0
 */

if ( !defined( 'YITH_WCMBS' ) ) {
    exit;
} // Exit if accessed directly

if ( !class_exists( 'YITH_WCMBS_Admin' ) ) {
    /**
     * Admin class.
     * The class manage all the admin behaviors.
     *
     * @since    1.0.0
     * @author   Leanza Francesco <leanzafrancesco@gmail.com>
     */
    class YITH_WCMBS_Admin {

        /**
         * Single instance of the class
         *
         * @var \YITH_WCMBS_Admin
         * @since 1.0.0
         */
        protected static $instance;

        public static $membership_post_type = 'ywcmbs-membership';

        /**
         * Plugin options
         *
         * @var array
         * @access public
         * @since  1.0.0
         */
        public $options = array();

        /**
         * Plugin version
         *
         * @var string
         * @since 1.0.0
         */
        public $version = YITH_WCMBS_VERSION;

        /**
         * @var $_panel Panel Object
         */
        protected $_panel;

        /**
         * @var string Premium version landing link
         */
        protected $_premium_landing = 'http://yithemes.com/themes/plugins/yith-woocommerce-membership';

        /**
         * @var string Quick View panel page
         */
        protected $_panel_page = 'yith_wcmbs_panel';

        /**
         * Various links
         *
         * @var string
         * @access public
         * @since  1.0.0
         */
        public $doc_url = 'http://yithemes.com/docs-plugins/yith-woocommerce-membership/';

        public $templates = array();

        /**
         * @var YITH_WCMBS_Admin_Assets
         */
        public $assets;

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
         * @access public
         * @since  1.0.0
         */
        public function __construct() {

            $this->assets = YITH_WCMBS_Admin_Assets::get_instance();

            add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );

            // Register Membership Post Type
            add_action( 'init', array( $this, 'register_membership_post_type' ) );

            //Add action links
            add_filter( 'plugin_action_links_' . plugin_basename( YITH_WCMBS_DIR . '/' . basename( YITH_WCMBS_FILE ) ), array( $this, 'action_links' ) );
            add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );

            add_action( 'add_meta_boxes', array( $this, 'register_metaboxes' ) );
            add_action( 'save_post', array( $this, 'save_metaboxes' ) );

            YITH_FL_Ajax_Products();

            foreach ( YITH_WCMBS_Manager()->post_types as $post_type ) {
                add_filter( 'manage_' . $post_type . '_posts_columns', array( $this, 'add_columns' ) );
            }
            add_action( 'manage_posts_custom_column', array( $this, 'custom_columns' ), 10, 2 );
            add_action( 'manage_pages_custom_column', array( $this, 'custom_columns' ), 10, 2 );
        }


        /**
         * Register Membership custom post type
         *
         * @return   void
         * @since    1.0
         * @author   Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function register_membership_post_type() {

            $labels = array(
                'menu_name'          => _x( 'All Memberships', 'plugin name in admin WP menu', 'yith-woocommerce-membership' ),
                'all_items'          => __( 'All Memberships', 'yith-woocommerce-membership' ),
                'name'               => __( 'All Memberships', 'yith-woocommerce-membership' ),
                'singular_name'      => __( 'Membership', 'yith-woocommerce-membership' ),
                'add_new'            => __( 'Membership', 'yith-woocommerce-membership' ),
                'add_new_item'       => __( 'New Membership', 'yith-woocommerce-membership' ),
                'edit_item'          => __( 'Membership', 'yith-woocommerce-membership' ),
                'view_item'          => __( 'View Membership', 'yith-woocommerce-membership' ),
                'not_found'          => __( 'Membership not found', 'yith-woocommerce-membership' ),
                'not_found_in_trash' => __( 'Membership not found in trash', 'yith-woocommerce-membership' )
            );

            $caps = array(
                'create_posts' => false,
            );

            $args = array(
                'labels'              => $labels,
                'public'              => false,
                'show_ui'             => false,
                'menu_position'       => 10,
                'exclude_from_search' => true,
                'capability_type'     => 'post',
                'capabilities'        => $caps,
                'map_meta_cap'        => true,
                'rewrite'             => true,
                'has_archive'         => true,
                'hierarchical'        => false,
                'show_in_nav_menus'   => false,
                'supports'            => array( 'title' ),
            );

            if ( defined( 'YITH_WCMBS_PREMIUM' ) && YITH_WCMBS_PREMIUM ) {
                $args[ 'show_in_menu' ] = 'edit.php?post_type=yith-wcmbs-plan';
                $args[ 'show_ui' ]      = true;
            }

            register_post_type( self::$membership_post_type, $args );
            remove_post_type_support( self::$membership_post_type, 'title' );
        }

        /**
         * Add column in product table list
         *
         * @access public
         * @since  1.0.0
         * @author Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function add_columns( $columns ) {
            $columns[ 'yith_wcmbs_restrict_access' ] = '<span class="dashicons dashicons-lock"></span>';

            return $columns;
        }

        /**
         * Add content in custom column in product table list
         *
         * @access public
         * @since  1.0.0
         * @author Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function custom_columns( $column, $post_id ) {
            if ( $column == 'yith_wcmbs_restrict_access' ) {
                $restrict_access = get_post_meta( $post_id, '_yith_wcmbs_restrict_access', true );

                switch ( $restrict_access ) {
                    case 'all_members':
                        $restrict_access = '<span class="dashicons dashicons-groups tips" data-tip="' . __( 'All Members', 'yith-woocommerce-membership' ) . '"></span>';
                        break;
                    case 'non_members':
                        $restrict_access = '<span class="dashicons dashicons-businessman tips" data-tip="' . __( 'All Non-Members', 'yith-woocommerce-membership' ) . '"></span>';
                        break;
                    case 'none':
                        $restrict_access = '';
                        break;
                }

                echo $restrict_access;
            }
        }


        /**
         * Action Links
         *
         * add the action links to plugin admin page
         *
         * @param $links | links plugin array
         *
         * @return   mixed Array
         * @since    1.0
         * @author   Leanza Francesco <leanzafrancesco@gmail.com>
         * @return mixed
         * @use      plugin_action_links_{$plugin_file_name}
         */
        public function action_links( $links ) {

            $links[] = '<a href="' . admin_url( "admin.php?page={$this->_panel_page}" ) . '">' . __( 'Settings', 'yith-woocommerce-membership' ) . '</a>';

            return $links;
        }

        /**
         * plugin_row_meta
         *
         * add the action links to plugin admin page
         *
         * @param $plugin_meta
         * @param $plugin_file
         * @param $plugin_data
         * @param $status
         *
         * @return   Array
         * @since    1.0
         * @use      plugin_row_meta
         */
        public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {

            if ( defined( 'YITH_WCMBS_FREE_INIT' ) && YITH_WCMBS_FREE_INIT == $plugin_file || defined( 'YITH_WCMBS_INIT' ) && YITH_WCMBS_INIT == $plugin_file ) {
                $plugin_meta[] = '<a href="' . $this->doc_url . '" target="_blank">' . __( 'Plugin Documentation', 'yith-woocommerce-membership' ) . '</a>';
            }

            return $plugin_meta;
        }

        /**
         * Add a panel under YITH Plugins tab
         *
         * @return   void
         * @since    1.0
         * @author   Leanza Francesco <leanzafrancesco@gmail.com>
         * @use      /Yit_Plugin_Panel class
         * @see      plugin-fw/lib/yit-plugin-panel.php
         */
        public function register_panel() {

            if ( !empty( $this->_panel ) ) {
                return;
            }

            $admin_tabs_free = array(
                'settings' => __( 'Settings', 'yith-woocommerce-membership' ),
                //'premium'       => __( 'Premium Version', 'yith-woocommerce-membership' )
            );

            $admin_tabs = apply_filters( 'yith_wcmbs_settings_admin_tabs', $admin_tabs_free );

            $args = array(
                'create_menu_page' => true,
                'parent_slug'      => '',
                'page_title'       => _x( 'Membership', 'plugin name in admin page title', 'yith-woocommerce-membership' ),
                'menu_title'       => _x( 'Membership', 'plugin name in admin WP menu', 'yith-woocommerce-membership' ),
                'capability'       => 'manage_options',
                'parent'           => '',
                'parent_page'      => 'yit_plugin_panel',
                'page'             => $this->_panel_page,
                'links'            => $this->get_panel_sidebar_links(),
                'admin-tabs'       => $admin_tabs,
                'options-path'     => YITH_WCMBS_DIR . '/plugin-options'
            );

            /* === Fixed: not updated theme  === */
            if ( !class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
                require_once( 'plugin-fw/lib/yit-plugin-panel-wc.php' );
            }

            $this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );

            add_action( 'woocommerce_admin_field_yith_wcmbs_upload', array( $this->_panel, 'yit_upload' ), 10, 1 );
        }

        /**
         * get links for yit panel sidebar
         *
         * @return array
         */
        public function get_panel_sidebar_links() {
            return array(
                array(
                    'url'   => 'http://www.yithemes.com',
                    'title' => __( 'Your Inspiration Themes', 'yith-woocommerce-membership' ),
                ),
                array(
                    'url'   => $this->_premium_landing,
                    'title' => __( 'Premium', 'yith-woocommerce-membership' ),
                ),
                array(
                    'url'   => $this->doc_url,
                    'title' => __( 'Plugin Documentation', 'yith-woocommerce-membership' ),
                ),
                array(
                    'url'   => 'http://plugins.yithemes.com/yith-woocommerce-membership/product/membership/?preview',
                    'title' => __( 'Live Demo', 'yith-woocommerce-membership' ),
                )
            );
        }

        /**
         * Add Metaboxes
         *
         * @param string $post_type
         *
         * @since    1.0
         * @author   Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function register_metaboxes( $post_type ) {
            if ( in_array( $post_type, YITH_WCMBS_Manager()->post_types ) ) {
                add_meta_box( 'yith-wcmbs-restrict-access-metabox', __( 'Allow access to', 'yith-woocommerce-membership' ), array( $this, 'restrict_access_metabox_render' ), null, 'side', 'high' );
            }
        }

        /**
         * Save meta for the metabox containing the chart table
         *
         * @since       1.0.0
         *
         * @param       $post_id
         *
         * @author      Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function save_metaboxes( $post_id ) {
            if ( !empty( $_POST[ '_yith_wcmbs_restrict_access' ] ) ) {
                $restrict_access_meta = $_POST[ '_yith_wcmbs_restrict_access' ];

                //var_dump($table_meta);
                update_post_meta( $post_id, '_yith_wcmbs_restrict_access', $restrict_access_meta );
            }
        }

        /**
         * Renders the Restrict Access Metabox for all post types
         *
         * @since    1.0
         * @author   Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function restrict_access_metabox_render( $post ) {
            $restrict_access = get_post_meta( $post->ID, '_yith_wcmbs_restrict_access', true );

            $t_args = array(
                'post'            => $post,
                'restrict_access' => $restrict_access
            );

            wc_get_template( '/metaboxes/restrict_access.php', $t_args, YITH_WCMBS_TEMPLATE_PATH, YITH_WCMBS_TEMPLATE_PATH );
        }

    }
}

/**
 * Unique access to instance of YITH_WCMBS_Admin class
 *
 * @return \YITH_WCMBS_Admin
 * @since 1.0.0
 */
function YITH_WCMBS_Admin() {
    if ( defined( 'YITH_WCMBS_PREMIUM' ) && YITH_WCMBS_PREMIUM ) {
        return YITH_WCMBS_Admin_Premium::get_instance();
    }

    return YITH_WCMBS_Admin::get_instance();
}

?>
