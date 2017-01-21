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
}
?>

<div id="ywgc-choose-design" class="ywgc-template-design" style="display: none">
	<div>
		<?php if ( count( $categories ) > 1 ): ?>
			<ul class="ywgc-template-categories">
				<li class="ywgc-template-item ywgc-category-all">
					<a href="#" class="ywgc-show-category ywgc-category-selected"
					   data-category-id="all">
						<?php _e( "Show all design", 'yith-woocommerce-gift-cards' ); ?>
					</a>
				</li>
				<?php foreach ( $categories as $item ): ?>
					<li class="ywgc-template-item ywgc-category-<?php echo $item->term_id; ?>">
						<a href="#" class="ywgc-show-category"
						   data-category-id="ywgc-category-<?php echo $item->term_id; ?>">
							<?php echo $item->name; ?>
						</a>
					</li>

				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<div class="ywgc-design-list">

			<?php foreach ( $item_categories as $item_id => $categories ): ?>

				<div class="ywgc-design-item <?php echo $categories; ?> template-<?php echo $item_id; ?>">

					<?php echo wp_get_attachment_image( intval( $item_id ), 'shop_catalog' ); ?>
					<button class="ywgc-choose-preset"
					        data-design-id="<?php echo $item_id; ?>"
					        data-design-url="<?php echo wp_get_attachment_image_url( intval( $item_id ), 'full' ); ?>"><?php _e( "Choose design", 'yith-woocommerce-gift-cards' ); ?></button>
				</div>

			<?php endforeach; ?>

		</div>
	</div>
</div>
