<?php
/**
 * Customer completed order email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-completed-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     2.5.0
 */

// load customer data and user email into email template
$user_email = $user_login;
$user       = get_user_by('login', $user_login);
if ( $user ) {
    $user_email = $user->user_email;
}

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @hooked WC_Emails::email_header() Output the email header
 */

?>
 <?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>
 
<h2>Thanks for purchasing a Tri-Fun membership! You’re all set to start making new memories now! </h2>

<h3><?php printf( __( "Thanks for creating an account on %s. Your username is <strong>%s</strong>.", 'woocommerce' ), esc_html( $blogname ), esc_html( $user_email ) ); ?></h3>

<p><strong>Here’s how to begin using your membership:</strong></p>

<p>Visit our website at www.tri-fun.com and log into your account where you can view and/or print your membership pass as well as see your order details or change your password. You can show your pass to the venues directly from your mobile device or print it out and take it with you! Just make sure to visit our venues’ pages on our website for individual redemption instructions.</p>

<p>Now go have some fun!</p>

<p>Sincerely,<br/>
Your Friends at Tri-Fun</p>

<p><em>p.s. We want to see your pictures, so when you share them on social media make sure to use #HowDoYouTriFun</em></p>

<?php

/**
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Emails::order_schema_markup() Adds Schema.org markup.
 * @since 2.5.0
 */
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

/**
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/**
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
?>
