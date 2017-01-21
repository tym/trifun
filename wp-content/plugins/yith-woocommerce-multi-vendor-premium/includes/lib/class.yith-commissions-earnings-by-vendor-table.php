<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'Direct access forbidden.' );
}


if ( ! class_exists( 'YITH_Commissions_Earnings_By_Vendor_Table' ) ) {
    /**
     *
     *
     * @class class.yith-commissions-list-table
     * @package    Yithemes
     * @since      Version 1.0.0
     * @author     Your Inspiration Themes
     *
     */
    class YITH_Commissions_Earnings_By_Vendor_Table extends WP_List_Table {

        /**
         * The vendor object for current user
         *
         * @var array
         * @since 1.0
         */
        protected $_vendor = null;

        /**
         * Months Dropdown value
         *
         * @var array
         * @since 1.0
         */
        protected $_months_dropdown = array();

        /**
         * Construct
         */
        public function __construct(){
            parent::__construct();

            $this->_vendor = yith_get_vendor( 'current', 'user' );
        }

        /**
         * Returns columns available in table
         *
         * @return array Array of columns of the table
         * @since 1.0.0
         */
        public function get_columns() {
            $columns = array(
                'vendor'            => YITH_Vendors()->get_vendors_taxonomy_label( 'singular_name' ),
                'user'              => __( 'User', 'yith-woocommerce-product-vendors' ),
                'oldest_commission' => __( 'Oldest unpaid commission', 'yith-woocommerce-product-vendors' ),
                'amount'            => __( 'Amount', 'yith-woocommerce-product-vendors' ),
                'count'             => __( 'Sold products', 'yith-woocommerce-product-vendors' ),
                'user_actions'      => __( 'Actions', 'yith-woocommerce-product-vendors' ),
            );

            return $columns;
        }

        /**
         * Print the columns information
         *
         * @param $rec  \YITH_Commission
         * @param $column_name
         *
         * @return string
         */
        public function column_default( $rec, $column_name ) {
            switch ( $column_name ) {

                case 'user':
                    if ( empty( $rec->display_name ) ) {
                        return "<em>" . __( 'User deleted', 'yith-woocommerce-product-vendors' ) . "</em>";
                    }

                    $user_url  = get_edit_user_link( $rec->ID );
                    $user_name = $rec->display_name;
                    return ! empty( $user_url ) ? "<a href='{$user_url}' target='_blank'>{$user_name}</a>" : $user_name;
                    break;

                case 'vendor':
                    $vendor = yith_get_vendor( $rec->ID, 'user' );

                    if ( ! $vendor->is_valid() ) {
                        return "<em>" . __( 'Vendor deleted', 'yith-woocommerce-product-vendors' ) . "</em>";
                    }

                    $vendor_url  = get_edit_term_link( $vendor->id, $vendor->taxonomy );
                    $vendor_name = $vendor->name;
                    return ! empty( $vendor_url ) ? "<a href='{$vendor_url}' target='_blank'>{$vendor_name}</a>" : $vendor_name;
                    break;

                case 'amount':
                    $vendor = yith_get_vendor( $rec->ID, 'user' );
                    $amount = $vendor->get_unpaid_commissions_amount();
                    return wc_price( $amount );
                    break;

                case 'count':
                    $vendor      = yith_get_vendor( $rec->ID, 'user' );
                    $commissions = $vendor->get_unpaid_commissions();
                    return count( $commissions );
                    break;

                case 'user_actions':
                    $vendor = yith_get_vendor( $rec->ID, 'user' );
                    if ( $vendor->is_super_user() ) {
                        $commissions_ids = $vendor->get_unpaid_commissions();
                        $args = array(
                            'vendor_id'      => $vendor->id,
                            'action'         => 'pay_commissions',
                            'commission_ids' => implode( ',', $commissions_ids ),
                            'amount'         => $vendor->get_unpaid_commissions_amount()
                        );
                        return sprintf( '<a class="button tips pay" href="%1$s" data-tip="%2$s">%2$s</a>',
                            esc_url( wp_nonce_url( add_query_arg( $args, admin_url( 'admin.php' ) ),
                                'yith-vendors-pay-commissions' ) ),
                            __( 'Pay', 'yith-woocommerce-product-vendors' )
                        );
                    }
                    break;

                case 'oldest_commission':
                    $commissions = YITH_Commissions()->get_commissions(
                        array(
                            'user_id'   => $rec->ID,
                            'fields'    => 'last_edit',
                            'order'     => 'ASC',
                        )
                    );

                    $oldest_commission_date = array_shift( $commissions );
                    $t_time = date_i18n( __( 'Y/m/d g:i:s A', 'yith-woocommerce-product-vendors' ), mysql2date( 'U', $oldest_commission_date ) );
                    $m_time = $oldest_commission_date;
                    $time   = mysql2date( 'G', $oldest_commission_date );

                    $time_diff = time() - $time;

                    if ( $time_diff > 0 && $time_diff < DAY_IN_SECONDS ) {
                        $h_time = sprintf( __( '%s ago', 'yith-woocommerce-product-vendors' ), human_time_diff( $time ) );
                    }
                    else {
                        $h_time = mysql2date( __( 'Y/m/d', 'yith-woocommerce-product-vendors' ), $m_time );
                    }

                    return $h_time ? '<abbr title="' . $t_time . '">' . $h_time . '</abbr>' : '<small class="meta">-</small>';
                    break;
            }

            return null;
        }

        /**
         * Prepare items for table
         *
         * @return void
         * @since 1.0.0
         */
        public function prepare_items() {

            // sets pagination arguments
            $per_page     = $this->get_items_per_page( 'edit_commissions_per_page' );
            $current_page = absint( $this->get_pagenum() );

            // commissions args
            $vendors = get_users(
                array(
                    'meta_key'      => '_vendor_commission_credit',
                    'meta_value'    => 0,
                    'meta_compare'  => '>',
                    'fields'        => array( 'ID', 'display_name' ),
                )
            );

            // sets columns headers
            $columns                = $this->get_columns();
            $hidden                 = array();
            $sortable               = $this->get_sortable_columns();
            $this->_column_headers  = array( $columns, $hidden, $sortable );
            $total_items            = count( $vendors );

            // retrieve data for table
            $this->items = ! empty( $vendors ) ? $vendors : array();

            // sets pagination args
            $this->set_pagination_args( array(
                    'total_items' => $total_items,
                    'per_page'    => $per_page,
                    'total_pages' => ceil( $total_items / $per_page )
                )
            );
        }

        /**
         * Extra controls to be displayed between bulk actions and pagination
         *
         * @since 3.1.0
         * @access protected
         */
        protected function get_views() {

            //TODO: da completare
            $views = array(
                'unpaid' => __( 'Unpaid', 'yith-woocommerce-product-vendors' ),
                'paid'   => __( 'Payment Request', 'yith-woocommerce-product-vendors' ),
            );

            $current_view = 'unpaid';
            $args = array( 'status' => 0 );

            foreach ( $views as $id => $view ) {
                $href           = esc_url( add_query_arg( 'status', $id ) );
                $class          = $id == $current_view ? 'current' : '';
                $args['status'] = 'unpaid' == $id ? array( $id, 'processing' ) : $id;
                $count          = YITH_Commissions()->count_commissions( $args );
                $views[$id]     = sprintf( "<a href='%s' class='%s'>%s <span class='count'>(%d)</span></a>", $href, $class, $view, $count );
            }

            return $views;
        }
    }
}

