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
    exit; // Exit if accessed directly
}
$user               = get_user_by( 'id', get_current_user_id() );
$firstname          = ! empty( $_POST['vendor-owner-firstname'] )   ? sanitize_text_field( $_POST['vendor-owner-firstname'] ) : '';
$lastname           = ! empty( $_POST['vendor-owner-lastname'] )    ? sanitize_text_field( $_POST['vendor-owner-lastname'] ) : '';
$store_name         = ! empty( $_POST['vendor-name'] )              ? sanitize_text_field( $_POST['vendor-name'] ) : '';
$store_location     = ! empty( $_POST['vendor-location'] )          ? sanitize_text_field( $_POST['vendor-location'] ) : '';
$store_email        = ! empty( $_POST['vendor-email'] )             ? sanitize_email( $_POST['vendor-email'] ) : $user->user_email;
$store_telephone    = ! empty( $_POST['vendor-telephone'] )         ? sanitize_text_field( $_POST['vendor-telephone'] ) : '';
$vat                = ! empty( $_POST['vendor-vat'] )               ? sanitize_text_field( $_POST['vendor-vat'] ) : '';

?>

<div id="yith-become-a-vendor" class="woocommerce shortcodes">
    <?php wc_print_notices(); ?>
    <form method="post" class="register">
        <p class="form-row form-row-wide">
            <label for="vendor-name"><?php _e( 'Store name *', 'yith-woocommerce-product-vendors' )?></label>
            <input type="text" class="input-text yith-required" name="vendor-name" id="vendor-name" value="<?php echo $store_name ?>">
        </p>

        <p class="form-row form-row-wide">
            <label for="vendor-location"><?php _e( 'Address *', 'yith-woocommerce-product-vendors' )?></label>
            <input type="text" class="input-text yith-required" name="vendor-location" id="vendor-location" value="<?php echo $store_location ?>" placeholder="MyStore S.A. Avenue MyStore 55, 1800 Vevey, Switzerland">
        </p>

        <p class="form-row form-row-wide">
            <label for="vendor-email"><?php _e( 'Store email *', 'yith-woocommerce-product-vendors' )?></label>
            <input type="text" class="input-text yith-required" name="vendor-email" id="vendor-email" value="<?php echo $store_email ?>">
        </p>

        <p class="form-row form-row-wide">
            <label for="vendor-telephone"><?php _e( 'Telephone *', 'yith-woocommerce-product-vendors' )?></label>
            <input type="text" class="input-text yith-required" name="vendor-telephone" id="vendor-telephone" value="<?php echo $store_telephone ?>">
        </p>

        <p class="form-row form-row-wide">
            <?php $vat_field_required =  $is_vat_require ? '*' : ''; ?>
            <label for="vendor-vat"><?php echo __( 'VAT/SSN', 'yith-woocommerce-product-vendors' ) . ' ' . $vat_field_required ?></label>
            <input type="text" class="input-text <?php echo $is_vat_require ? 'yith-required' : '' ?>" name="vendor-vat" id="vendor-vat" value="<?php echo $vat ?>">
        </p>

        <?php if( $is_terms_and_conditions_require ) : ?>
            <p class="form-row form-row-wide last-child">
                <input type="checkbox" class="input-checkbox yith-required" name="vendor-terms" <?php checked( apply_filters( 'yith_wcmv_terms_is_checked_default', isset( $_POST['vendor-terms'] ) ), true ); ?> id="vendor-terms" required />
                <label for="vendor-terms" class="checkbox"><?php printf( __( 'I&rsquo;ve read and accept the <a href="%s" target="_blank">terms &amp; conditions</a>', 'woocommerce' ), esc_url( get_permalink( get_option( 'yith_wpv_terms_and_conditions_page_id' ) ) ) ); ?> <span class="required">*</span></label>
                <input type="hidden" name="terms-field" value="1" />
            </p>
        <?php endif; ?>

        <p class="form-row">
            <?php wp_nonce_field( 'woocommerce-register' ); ?>
            <input type="button" id="yith-become-a-vendor-submit" class="<?php apply_filters( 'yith_wpv_become_a_vendor_button_class', 'button' ) ?>" name="register" value="<?php echo $become_a_vendor_label; ?>" />
            <input type="hidden" id="yith-vendor-register" name="vendor-register" value="1">
            <input type="hidden" id="vendor-antispam" name="vendor-antispam" value="">
        </p>
    </form>
</div>