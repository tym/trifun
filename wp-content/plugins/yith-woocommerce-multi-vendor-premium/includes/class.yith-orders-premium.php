<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined ( 'ABSPATH' ) ) {
    exit( 'Direct access forbidden.' );
}

/**
 *
 *
 * @class      YITH_Orders_Premium
 * @package    Yithemes
 * @since      Version 1.6
 * @author     Your Inspiration Themes
 *
 */
if ( ! class_exists ( 'YITH_Order_Premium' ) ) {

    class YITH_Orders_Premium extends YITH_Orders {

        /**
         * construct
         */
        public function __construct () {
            parent::__construct ();

            $refund_management = 'yes' == get_option ( 'yith_wpv_vendors_option_order_refund_synchronization', 'no' ) ? true : false;

            if ( $refund_management ) {
                add_action ( 'woocommerce_order_refunded', array ( $this, 'child_order_refunded' ), 10, 2 );
                add_action ( 'before_delete_post', array ( $this, 'before_delete_child_refund' ), 10, 1 );
            }

            add_action( 'woocommerce_after_order_itemmeta', array( $this, 'commission_info_in_order_line_item' ), 10, 3 );
            
            add_filter( 'woocommerce_resend_order_emails_available', array( $this, 'resend_order_emails_available' ) );
        }

        /**
         * Handle a refund via the edit order screen.
         * Called after wp_ajax_woocommerce_refund_line_items action
         *
         * @use woocommerce_order_refunded action
         * @see woocommerce\includes\class-wc-ajax.php:2295
         */
        public function child_order_refunded ( $order_id, $child_refund_id ) {
            $parent_order_id = wp_get_post_parent_id ( $order_id );
            if ( $parent_order_id ) {
                $create_refund          = true;
                $refund                 = false;
                $child_line_item_refund = $parent_total_refund = 0;
                $refund_amount          = wc_format_decimal ( sanitize_text_field ( $_POST[ 'refund_amount' ] ) );
                $refund_reason          = sanitize_text_field ( $_POST[ 'refund_reason' ] );
                $line_item_qtys         = json_decode ( sanitize_text_field ( stripslashes ( $_POST[ 'line_item_qtys' ] ) ), true );
                $line_item_totals       = json_decode ( sanitize_text_field ( stripslashes ( $_POST[ 'line_item_totals' ] ) ), true );
                $line_item_tax_totals   = json_decode ( sanitize_text_field ( stripslashes ( $_POST[ 'line_item_tax_totals' ] ) ), true );
                $api_refund             = $_POST[ 'api_refund' ] === 'true' ? true : false;
                $restock_refunded_items = $_POST[ 'restock_refunded_items' ] === 'true' ? true : false;
                $order                  = wc_get_order ( $order_id );
                $parent_order_total     = wc_format_decimal ( $order->get_total () );

                //calculate line items total from parent order
                foreach ( $line_item_totals as $item_id => $total ) {
                    $child_line_item_refund += wc_format_decimal ( $total );
                }
                
                $parent_order           = wc_get_order ( $parent_order_id );
                $parent_items_ids       = array_keys ( $parent_order->get_items () );
                $parent_total           = wc_format_decimal ( $parent_order->get_total () );
                $max_refund             = wc_format_decimal ( $parent_total - $parent_order->get_total_refunded () );
                $child_line_item_refund = 0;

                // Prepare line items which we are refunding
                $line_items = array ();
                $item_ids   = array_unique ( array_merge ( array_keys ( $line_item_qtys, $line_item_totals ) ) );

                foreach ( $item_ids as $item_id ) {
                    $parent_item_id = self::get_parent_item_id ( $order, $item_id );
                    if ( $parent_item_id && in_array ( $parent_item_id, $parent_items_ids ) ) {
                        $line_items[ $parent_item_id ] = array ( 'qty' => 0, 'refund_total' => 0, 'refund_tax' => array () );
                    }
                }

                foreach ( $line_item_qtys as $item_id => $qty ) {
                    $parent_item_id = self::get_parent_item_id ( $order, $item_id );
                    if ( $parent_item_id && in_array ( $parent_item_id, $parent_items_ids ) ) {
                        $line_items[ $parent_item_id ][ 'qty' ] = max ( $qty, 0 );
                    }
                }

                foreach ( $line_item_totals as $item_id => $total ) {
                    $parent_item_id = self::get_parent_item_id ( $order, $item_id );
                    if ( $parent_item_id && in_array ( $parent_item_id, $parent_items_ids ) ) {
                        $total = wc_format_decimal ( $total );
                        $child_line_item_refund += $total;
                        $line_items[ $parent_item_id ][ 'refund_total' ] = $total;
                    }
                }

                foreach ( $line_item_tax_totals as $item_id => $tax_totals ) {
                    $parent_item_id = self::get_parent_item_id ( $order, $item_id );
                    if ( $parent_item_id && in_array ( $parent_item_id, $parent_items_ids ) ) {
                        $line_items[ $parent_item_id ][ 'refund_tax' ] = array_map ( 'wc_format_decimal', $tax_totals );
                    }
                }

                //calculate refund amount percentage
                $parent_total_refund = wc_format_decimal ( $child_line_item_refund + $refund_amount );

                if ( ! $refund_amount || $max_refund < $child_line_item_refund || 0 > $child_line_item_refund ) {
                    /**
                     * Invalid refund amount.
                     * Check if suborder total != 0 create a partial refund, exit otherwise
                     */
                    $surplus             = wc_format_decimal ( $child_line_item_refund - $max_refund );
                    $parent_total_refund = $child_line_item_refund - $surplus;
                    $create_refund       = $parent_total_refund > 0 ? true : false;
                }

                if ( $create_refund ) {
                    // Create the refund object
                    $refund = wc_create_refund ( array (
                            'amount'     => $parent_total_refund,
                            'reason'     => $refund_reason,
                            'order_id'   => $parent_order->id,
                            'line_items' => $line_items,
                        )
                    );

                    add_post_meta ( $child_refund_id, '_parent_refund_id', $refund->id );
                }
            }
        }

        /**
         * Handle a refund via the edit order screen.
         * Need to delete parent refund from child order
         * Called in wp_ajax_woocommerce_delete_refund action
         *
         * @use before_delete_post
         * @see post.php:2634
         */
        public function before_delete_child_refund ( $post_id ) {
            $post = get_post ( $post_id );
            if ( $post && 'shop_order_refund' == $post->post_type ) {
                $order_id = wp_get_post_parent_id ( $post->post_parent );
                if ( $order_id ) {
                    //is child order
                    global $wpdb;
                    $parent_refund_id = $wpdb->get_var ( $wpdb->prepare ( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key=%s AND post_id=%d", '_parent_refund_id', $post_id ) );
                    wc_delete_shop_order_transients ( $order_id );
                    wp_delete_post ( $parent_refund_id );
                }
            }
        }

        /**
         * Add the commission information to order line item
         * 
         * @param $item_id
         * @param $item
         * @param $_product
         * 
         * @use woocommerce_after_order_itemmeta hook
         * @since 1.9.12
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function commission_info_in_order_line_item( $item_id, $item, $_product ){
            global $theorder;
            if( $theorder && wp_get_post_parent_id( $theorder->id ) != 0 && ! empty( $item['commission_id'] ) && apply_filters( 'yith_wcmv_show_commission_info_in_ordeR_line_item', true ) ){
                $commission = YITH_Commission( $item['commission_id'] );
                $coupon_management = 'yes' == get_option('yith_wpv_include_coupon', 'yes') ? __( 'Vendor commissions have been calculated including coupon value', 'yith-woocommerce-product-vendors' ) : __( 'Vendor commissions have been calculated excluding coupon value', 'yith-woocommerce-product-vendors' );
                echo sprintf( '<span class="yith-order-item-commission-details">%s: <strong>%s</strong> (%s %s)<br/><small><em>%s</em></small></span>',
                    __( 'Commission', 'yith-woocommerce-product-vendors'),
                    $commission->get_amount('display'),
                    $commission->get_rate('display'),
                    __( 'on product price', 'yith-woocommerce-product-vendors' ),
                    $coupon_management
                );
            }
        }

        /**
         * Add Order actions for vendors
         *
         * @param $email The email id
         *
         * @use    woocommerce_resend_order_emails_available hook
         * @since  1.9.14
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @return array
         */
        public function resend_order_emails_available( $emails ){
            /* Add vendor email to available wc emails */
            $emails[] = 'new_order_to_vendor';
            $emails[] = 'cancelled_order';

            $vendor = yith_get_vendor( 'current', 'user' );
            if( $vendor->is_valid() && $vendor->has_limited_access() ){
                $prevent_resend_email = 'no' == get_option( 'yith_wpv_vendors_option_order_prevent_resend_email', 'no' ) ? false : true;
                if( $prevent_resend_email ){
                    $emails = array();
                }
            }
            return $emails;
        }

        /**
         * Add input hidden with customer id
         *
         * @param $order WC_Order object
         *
         * @since  1.9.18
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @return void
         */
        public function hide_customer_info( $order ){
            if( $order instanceof WC_Order){
                $user_id     = absint( $order->customer_user );
                ob_start(); ?>
                <input type="hidden" name="customer_user" value="<?php echo $user_id; ?>" />
                <?php
                echo ob_get_clean();
            }
        }
    }
}