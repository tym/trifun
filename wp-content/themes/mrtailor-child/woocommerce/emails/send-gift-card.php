<?php
/**
 * Send the gift card code email
 *
 * @author  Yithemes
 * @package yith-woocommerce-gift-cards-premium\templates\emails
 */

if ( !defined ( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * @hooked YITH_WooCommerce_Gift_Cards_Premium::include_css_for_emails() Add CSS style to gift card emails header
 */

do_action ( 'woocommerce_email_header' , $email_heading , $email ); ?>

<!-- <h2>Thank you for your Gift Card purchase at Tri-Fun. The recipients have received an email with instructions how to redeem their gift.</h2> -->


<?php
do_action ( 'ywgc_gift_cards_email_before_preview' , $introductory_text , $gift_card );

do_action( 'woocommerce_email_order_meta', $order, $email );

YITH_YWGC ()->preview_digital_gift_cards ( $gift_card, 'email' );

do_action ( 'ywgc_gift_card_email_after_preview' , $gift_card );
?>

<a href="<?php echo get_home_url('/buy-membership'); ?>" style="text-align:center;"><h3>Click here to redeem Your Gift Card</h3></a>

<?php
/**
 * @hooked YITH_WooCommerce_Gift_Cards_Premium::add_footer_information() Output the email footer
 */
do_action ( 'woocommerce_email_footer' , $email );

?>
