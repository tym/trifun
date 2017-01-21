<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Members Class
 *
 * @class   YITH_WCMBS_Downloads_Report
 * @package Yithemes
 * @since   1.0.5
 * @author  Yithemes
 *
 */
class YITH_WCMBS_Downloads_Report {

    /**
     * Single instance of the class
     *
     * @var \YITH_WCMBS_Downloads_Report
     * @since 1.0.5
     */
    protected static $instance;

    /**
     * the name of the Downlod Report table
     *
     * @type string
     */
    public $table_name = '';

    /**
     * Version of database
     *
     * @type string
     */
    protected static $db_version = '1.0.0';

    /**
     * Returns single instance of the class
     *
     * @return \YITH_WCMBS_Downloads_Report
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
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'yith_wcmbs_downloads_log';
    }

    /**
     * Add new Report in db
     *
     * @param int $product_id
     * @param int $user_id
     * @param int $membership_id
     *
     * @since  1.0.5
     * @author Leanza Francesco <leanzafrancesco@gmail.com>
     */
    public function add_report( $product_id, $user_id ) {
        global $wpdb;

        $insert_query = "INSERT INTO $this->table_name (`product_id`, `user_id`, `timestamp_date`) VALUES ('" . $product_id . "', '" . $user_id . "', CURRENT_TIMESTAMP() )";
        $wpdb->query( $insert_query );
    }

    /**
     * create table for Downloads Log
     *
     * @param bool $force
     */
    public static function create_db_table( $force = false ) {
        global $wpdb;

        $current_version = get_option( "yith_wcmbs_db_version" );

        if ( $force || $current_version != self::$db_version ) {
            $wpdb->hide_errors();

            $table_name      = $wpdb->prefix . 'yith_wcmbs_downloads_log';
            $charset_collate = $wpdb->get_charset_collate();

            $sql
                = "CREATE TABLE $table_name (
                    `id` bigint(20) NOT NULL AUTO_INCREMENT,
                    `product_id` bigint(20) NOT NULL,
                    `user_id` bigint(20) NOT NULL,
                    `timestamp_date` datetime NOT NULL,
                    PRIMARY KEY (id)
                    ) $charset_collate;";

            if ( !function_exists( 'dbDelta' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            }
            dbDelta( $sql );
            update_option( 'yith_wcmbs_db_version', self::$db_version );
        }
    }


    /**
     * get the downloads count
     *
     * @param $args
     *
     * @return int
     */
    public function count_downloads( $args ) {
        global $wpdb;

        $where    = '';
        $distinct = '*';

        $where_array = array();
        if ( isset( $args[ 'where' ] ) ) {
            foreach ( $args[ 'where' ] as $s_where ) {
                if ( isset( $s_where[ 'key' ] ) ) {
                    $value   = '';
                    $compare = '=';
                    if ( isset( $s_where[ 'value' ] ) ) {
                        $value = $s_where[ 'value' ];
                    } else {
                        $compare = '!=';
                    }

                    if ( isset( $s_where[ 'compare' ] ) ) {
                        $compare = $s_where[ 'compare' ];
                    }

                    $where_array[] = $s_where[ 'key' ] . ' ' . $compare . ' "' . $value . '"';
                }
            }
        }

        if ( !empty( $where_array ) ) {
            $where = 'WHERE ' . implode( ' AND ', $where_array );
        }

        if ( isset( $args[ 'distinct' ] ) ) {
            $distinct = 'DISTINCT ' . $args[ 'distinct' ];
        }

        $results = $wpdb->get_var( "SELECT COUNT($distinct) FROM $this->table_name $where" );

        return absint( $results );
    }

    public function get_download_reports( $args ) {
        global $wpdb;

        $where    = '';
        $order_by = '';
        $group_by = '';
        $select   = '*';

        if ( isset( $args[ 'select' ] ) ) {
            $select = $args[ 'select' ];
        }

        if ( isset( $args[ 'group_by' ] ) ) {
            $group_by = 'GROUP BY ' . $args[ 'group_by' ];
        }

        if ( isset( $args[ 'order_by' ] ) ) {
            $order_by = 'ORDER BY ' . $args[ 'order_by' ];
            if ( isset( $args[ 'order' ] ) ) {
                $order_by .= ' ' . $args[ 'order' ];
            }
        }

        $where_array = array();
        if ( isset( $args[ 'where' ] ) ) {
            foreach ( $args[ 'where' ] as $s_where ) {
                if ( isset( $s_where[ 'key' ] ) ) {
                    $value   = '';
                    $compare = '=';
                    if ( isset( $s_where[ 'value' ] ) ) {
                        $value = $s_where[ 'value' ];
                    } else {
                        $compare = '!=';
                    }

                    if ( isset( $s_where[ 'compare' ] ) ) {
                        $compare = $s_where[ 'compare' ];
                    }

                    $where_array[] = $s_where[ 'key' ] . ' ' . $compare . ' "' . $value . '"';
                }
            }
        }

        if ( !empty( $where_array ) ) {
            $where = 'WHERE ' . implode( ' AND ', $where_array );
        }

        $query   = "SELECT $select FROM $this->table_name $where $group_by $order_by";
        $results = $wpdb->get_results( $query );

        return $results;
    }

    public function get_download_ids_for_user( $user_id ) {
        global $wpdb;
        $query = "SELECT product_id FROM $this->table_name WHERE user_id = %s";

        $ids = $wpdb->get_col( $wpdb->prepare( $query, absint( $user_id ) ) );

        return array_unique( $ids );
    }

}

/**
 * Unique access to instance of YITH_WCMBS_Downloads_Report class
 *
 * @return YITH_WCMBS_Downloads_Report
 * @since 1.0.5
 */
function YITH_WCMBS_Downloads_Report() {
    return YITH_WCMBS_Downloads_Report::get_instance();
}