<?php
/**
 * Products items list
 *
 * Override this template by copying it to yourtheme/automatewoo/email/product-list.php
 *
 * @version 1.0.4
 */

$n = 1;

?>

	<?php if ( is_array( $products ) ): ?>

		<style>
			/** don't inline this css - hack for gmail */
			.aw-product-grid .aw-product-grid-item-2-col img {
				height: auto !important;
			}
		</style>

		<table cellspacing="0" cellpadding="0" class="aw-product-grid">
			<tbody><tr><td style="padding: 0;"><div class="aw-product-grid-container">

					<?php foreach ( $products as $product ): ?>

						<div class="aw-product-grid-item-2-col">

							<a href="<?php echo $product->get_permalink() ?>"><?php echo AW_Mailer_API::get_product_image( $product ) ?></a>
							<h3><a href="<?php echo $product->get_permalink() ?>"><?php echo $product->get_title(); ?></a></h3>
							
							<!-- custom WL edits below -->
							
								<p><?php echo $product->post->post_excerpt; ?></p>
								
							<!-- end custom WL edits -->
							
						</div>

						<?php endforeach; ?>

				</div></td></tr></tbody>
		</table>

	<?php endif; ?>