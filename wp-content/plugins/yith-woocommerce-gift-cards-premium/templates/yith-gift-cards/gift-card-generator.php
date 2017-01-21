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
	<div class="ywgc-generator">

		<input type="hidden" name="ywgc-is-digital" value="1" />

		<?php if ( ! ( $product instanceof WC_Product_Gift_Card ) ): ?>
			<input type="hidden" name="ywgc-as-present-enabled" value="1">
		<?php endif; ?>

		<?php do_action( 'yith_ywgc_gift_card_preview', $product ); ?>

		<div class="gift-card-content-editor variations_button">

			<?php do_action( 'yith_ywgc_gift_card_preview_content', $product ); ?>

			<?php do_action( 'yith_ywgc_generator_buttons_before', $product ); ?>

		</div>
	</div>
<?php
do_action( 'yith_ywgc_gift_card_preview_end', $product );
