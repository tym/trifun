<?php
/**
 * Gift Card product add to cart
 *
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.4.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div class="ywgc-template <?php echo $template_style; ?>">
	<?php if ( $company_logo_url ) : ?>
		<div class="ywgc-top-header">
			<img src="<?php echo $company_logo_url; ?>"
			     class="ywgc-logo-shop-image"
			     alt="<?php _e( "The shop logo for the gift card", 'yith-woocommerce-gift-cards' ); ?>"
			     title="<?php _e( "The shop logo for the gift card", 'yith-woocommerce-gift-cards' ); ?>">
		</div>
	<?php endif; ?>

	<div class="ywgc-preview">
		<div class="ywgc-main-image">
			<?php if ( $header_image_url ): ?>
				<img src="<?php echo $header_image_url; ?>"
				     id="ywgc-main-image" class="ywgc-main-image"
				     alt="<?php _e( "The main image for the gift card", 'yith-woocommerce-gift-cards' ); ?>"
				     title="<?php _e( "The main image for the gift card", 'yith-woocommerce-gift-cards' ); ?>">
			<?php endif; ?>

		</div>
		<div class="ywgc-card-values">
			<?php if ( $company_logo_url ) : ?>
				<div class="ywgc-logo-shop">
					<img src="<?php echo $company_logo_url; ?>"
					     class="ywgc-logo-shop-image"
					     alt="<?php _e( "The shop logo for the gift card", 'yith-woocommerce-gift-cards' ); ?>"
					     title="<?php _e( "The shop logo for the gift card", 'yith-woocommerce-gift-cards' ); ?>">
				</div>
			<?php endif; ?>
			<div class="ywgc-card-amount">
				<?php echo $formatted_price; ?>
			</div>

		</div>
		<div class="ywgc-card-code">
			<?php _e( "Your Gift Card code:", 'yith-woocommerce-gift-cards' ); ?>
			<span class="ywgc-generated-code"><?php echo $gift_card_code; ?></span>
		</div>
		<div class="ywgc-card-message"><?php echo $message; ?></div>
	</div>
</div>
