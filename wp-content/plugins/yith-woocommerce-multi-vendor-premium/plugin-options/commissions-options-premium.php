<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

// merge Unpaid with Processing
$views = array( 'all' => __( 'All', 'yith-woocommerce-product-vendors' ) ) + YITH_Commissions()->get_status();
$views['unpaid'] .= '/' . $views['processing'];
unset( $views['processing'] );

return apply_filters( 'yith_wcqw_panel_commissions_options', array(

        'commissions' => array(

            'commissions_default_table_view' => array(
                'title'             => __( 'Commission page view', 'yith-woocommerce-product-vendors' ),
                'type'              => 'select',
                'default'           => 'unpaid',
                'desc'              => __( 'Select the default view for commission page', 'yith-woocommerce-product-vendors' ),
                'id'                => 'yith_commissions_default_table_view',
                'options'           => $views
            ),

            'commissions_default_coupon_handling' => array(
                'title'             => __( 'Coupon handling', 'yith-woocommerce-product-vendors' ),
                'type'              => 'checkbox',
                'default'           => 'yes',
                'desc'              => __( 'Include coupons in commission calculations', 'yith-woocommerce-product-vendors' ),
                'desc_tip'          => __( 'Decide whether vendor commissions have to be calculated including coupon value or not.', 'yith-woocommerce-product-vendors' ),
                'id'                => 'yith_wpv_include_coupon',
            ),

            'commissions_default_tax_handling' => array(
                'title'             => __( 'Tax handling', 'yith-woocommerce-product-vendors' ),
                'type'              => 'checkbox',
                'default'           => 'no',
                'desc'              => __( 'Include tax in commission calculations', 'yith-woocommerce-product-vendors' ),
                'desc_tip'          => __( 'Decide whether vendor commissions have to be calculated including tax value or not.', 'yith-woocommerce-product-vendors' ),
                'id'                => 'yith_wpv_include_tax',
            ),
        )
    ), 'commissions'
);