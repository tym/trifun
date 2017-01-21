<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * @var YITH_Vendor $vendor
 */
?>

<div class="wrap yith-vendor-admin-wrap" id="vendor-details">
    <form method="post" action="admin.php" enctype="multipart/form-data">
        <h3><?php _e( 'Payments information', 'yith-woocommerce-product-vendors' ) ?></h3>

        <input type="hidden" name="update_vendor_id" value="<?php echo $vendor->id ?>" />
        <input type="hidden" name="action" value="yith_admin_save_fields" />
        <input type="hidden" name="page" value="<?php echo ! empty( $_GET['page'] ) ? $_GET['page'] : '' ?>" />
        <input type="hidden" name="tab" value="<?php echo ! empty( $_GET['tab'] ) ? $_GET['tab'] : '' ?>" />

        <?php echo wp_nonce_field( 'yith_vendor_admin_payments', 'yith_vendor_admin_payments_nonce', true, false ) ?>

        <div class="form-field">
            <label for="yith_vendor_paypal_email"><?php _e( 'PayPal email address', 'yith-woocommerce-product-vendors' ); ?></label>
            <input type="text" class="regular-text" name="yith_vendor_data[paypal_email]" id="yith_vendor_paypal_email" value="<?php echo $vendor->paypal_email ?>" /><br />
            <span class="description"><?php _e( 'Vendor\'s PayPal email address where profits will be delivered.', 'yith-woocommerce-product-vendors' ); ?></span>
        </div>

        <div class="form-field">
            <label for="yith_vendor_bank_account"><?php _e( 'Bank Account (IBAN/BIC)', 'yith-woocommerce-product-vendors' ); ?></label>
            <input type="text" class="regular-text" name="yith_vendor_data[bank_account]" id="yith_vendor_bank_account" value="<?php echo $vendor->bank_account ?>" /><br />
            <span class="description"><?php _e( 'Vendor\'s IBAN/BIC bank account', 'yith-woocommerce-product-vendors' ); ?></span>
        </div>

        <?php if( 'choose' == get_option( 'payment_method' ) ) : ?>
            <div class="form-field">
                <label for="vendor_payment_type"><?php  _e( 'Payment type:', 'yith-woocommerce-product-vendors' ) ?></label>
                <select name="yith_vendor_data[payment_type]" id="vendor_payment_type" class="vendor_payment_type" >
                    <?php foreach( $payments_type as $value => $label ) : ?>
                        <option <?php selected( $vendor->payment_type, $value  ) ?> value="<?php echo $value ?>"><?php echo $label ?></option>
                    <?php endforeach; ?>
                </select>
                <br />
                <span class="description"><?php _e( 'Choose payment method for crediting commissions', 'yith-woocommerce-product-vendors' ); ?></span>
            </div>


            <div class="form-field">
                <label class="yith_vendor_payment_threshold" for="yith_vendor_payment_threshold"><?php _e( 'Threshold', 'yith-woocommerce-product-vendors' ); ?></label>
                <input type="number" class="payment-threshold-field" name="yith_vendor_data[threshold]" id="yith_vendor_payment_threshold" value="<?php echo $vendor->threshold ?>" min="<?php echo $min ?>"  step="<?php echo $step ?>" />
                <?php echo $currency_symbol ?>
                <br />
                <span class="description"><?php printf( '%s (%s: <strong>%s</strong>).',
                        __( "Minimum vendor's earning before a vendor's commissions can be paid", 'yith-woocommerce-product-vendors' ),
                        __('Minimum threshold allowed by site administrator is', 'yith-woocommerce-product-vendors' ),
                        wc_price( get_option( 'payment_minimum_withdrawals' ) )
                    );  ?></span>
            </div>
        <?php endif; ?>

         <div class="submit">
            <input name="Submit" type="submit" class="button-primary" value="<?php echo esc_attr( __( 'Save Payments Information', 'yith-woocommerce-product-vendors' ) ) ?>" />
        </div>
    </form>
</div>