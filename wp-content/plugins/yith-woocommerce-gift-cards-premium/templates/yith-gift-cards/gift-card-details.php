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

<div class="gift-card-content-editor step-content">
	<span class="ywgc-editor-section-title"><?php _e( "Gift card details", 'yith-woocommerce-gift-cards' ); ?></span>

	<label
		for="ywgc-recipient-email">
		<?php if ( $mandatory_recipient ) {
			_e( "Recipient's email (*)", 'yith-woocommerce-gift-cards' );
		} else {
			_e( "Recipient's email", 'yith-woocommerce-gift-cards' );
		}
		?></label>

	<div class="ywgc-single-recipient">
		<input type="email"
		       name="ywgc-recipient-email[]" <?php echo ( $mandatory_recipient && ! $gift_this_product ) ? 'required' : ''; ?>
		       class="ywgc-recipient" />
		<a href="#" class="ywgc-remove-recipient hide-if-alone">x</a>
		<?php if ( ! $mandatory_recipient ): ?>
			<span class="ywgc-empty-recipient-note"><?php _e( "If empty, will be sent to your email address", 'yith-woocommerce-gift-cards' ); ?></span>
		<?php endif; ?>
	</div>

	<?php
	//Only with gift card product type you can use multiple recipients
	if ( $allow_multiple_recipients ) : ?>
		<a href="#" class="add-recipient"
		   id="add_recipient"><?php _e( "Add another recipient", 'yith-woocommerce-gift-cards' ); ?></a>
	<?php endif; ?>
	<div class="ywgc-recipient-name">
		<label
			for="ywgc-recipient-name"><?php _e( "Recipient name", 'yith-woocommerce-gift-cards' ); ?></label>
		<input type="text" name="ywgc-recipient-name" id="ywgc-recipient-name">
	</div>
	<div class="ywgc-sender-name">
		<label
			for="ywgc-sender-name"><?php _e( "Your name", 'yith-woocommerce-gift-cards' ); ?></label>
		<input type="text" name="ywgc-sender-name" id="ywgc-sender-name">
	</div>
	<div class="ywgc-message">
		<label
			for="ywgc-edit-message"><?php _e( "Message", 'yith-woocommerce-gift-cards' ); ?></label>
		<textarea id="ywgc-edit-message" name="ywgc-edit-message" rows="5"
		          placeholder="<?php _e( "Your message...", 'yith-woocommerce-gift-cards' ); ?>"></textarea>
	</div>

	<?php if ( $allow_send_later ) : ?>
		<div class="ywgc-postdated">
			<label
				for="ywgc-postdated"><?php _e( "Postpone delivery", 'yith-woocommerce-gift-cards' ); ?></label>
			<input type="checkbox" id="ywgc-postdated" name="ywgc-postdated">
			<input type="text" id="ywgc-delivery-date" name="ywgc-delivery-date"
			       class="datepicker ywgc-hidden">
		</div>
	<?php endif; ?>
</div>