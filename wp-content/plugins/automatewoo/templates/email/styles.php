<?php
/**
 * Additional email styles that are added to every email template.
 * Override this template by copying it to yourtheme/automatewoo/email/
 */

$bg              = get_option( 'woocommerce_email_background_color' );
$body            = get_option( 'woocommerce_email_body_background_color' );
$base            = get_option( 'woocommerce_email_base_color' );
$text            = get_option( 'woocommerce_email_text_color' );
$base_text       = wc_light_or_dark( $base, '#202020', '#ffffff' );

?>

<?php /** Basic */ ?>

img.aligncenter {
	margin: 0 auto 10px;
	display: block;
}

img.alignleft {
	float: left;
	margin-right: 15px;
	margin-bottom: 10px;
}

img.alignright {
	float: right;
	margin-left: 15px;
	margin-bottom: 10px;
}


<?php /** Products display */ ?>

table.aw-product-grid {
	width: 100%;
}

.aw-product-grid-container {
	font-size: 0px;
	margin: 10px 0 10px;
}

.aw-product-grid-item-3-col {
	width: 30.5%;
	display: inline-block;
	text-align:left;
	padding: 0 0 30px;
	vertical-align:top;
	word-wrap:break-word;
	margin-right: 4%;
	font-size: 14px;
}

.aw-product-grid-item-2-col {
	width: 46%;
	display: inline-block;
	text-align:left;
	padding: 0 0 30px;
	vertical-align:top;
	word-wrap:break-word;
	margin-right: 6%;
	font-size: 14px;
}

table.aw-product-rows  {
	margin: 10px 0;
	border-top: 1px solid #dddddd;
}

table.aw-product-rows td {
	border-bottom: 1px solid #dddddd;
}

table.aw-order-table img,
table.aw-product-grid img,
table.aw-product-rows td img {
	max-width: 100%;
	height: auto;
}


table.aw-order-table {
	width: 100%;
	border: 1px solid #eee;
}

table.aw-order-table tr td,
table.aw-order-table tr th {
	text-align:left;
	vertical-align:middle;
	border: 1px solid #eee;
	font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;
	word-wrap:break-word;
}

.aw-coupon-code {
	font-weight: bold;
	letter-spacing: 2px;
	display: inline-block;
	padding: 12px 25px 13px;
	font-size: 17px;
	color: <?php echo esc_attr( $text ); ?>;
	border: 2px solid <?php echo esc_attr( $text ); ?>
}

a.aw-btn-1 {
	background-color: <?php echo esc_attr( $base ); ?>;
	border-radius: 4px;
	font-weight: 500;
	display: inline-block;
	padding: 14px 40px 15px;
	margin: 10px auto;
	font-size: 16px;
	text-decoration: none !important;
	color: #ffffff !important;
}
