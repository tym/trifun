<?php
/**
 * Email Order Items
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     2.1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$vendor_products = $vendor->get_products( array( 'fields' => 'ids' ) ); ?>
<tbody>
<?php foreach ( $items as $item_id => $item ) :
	$_product = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );

    if( ! in_array( $_product->id, $vendor_products ) ) {
        continue;
    }

	$item_meta = new WC_Order_Item_Meta( $item['item_meta'], $_product );
    /** @var $commission YITH_Commission */
    $commission = ! empty( $item['commission_id'] ) ? YITH_Commission( $item['commission_id'] ) : false;

	if ( apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
		?>
		<tr class="<?php echo esc_attr( apply_filters( 'woocoomerce_order_item_class', 'order_item', $item, $order ) ); ?>">
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php

				// Show title/image etc
				if ( $show_image ) {
					echo apply_filters( 'woocommerce_order_item_thumbnail', '<img src="' . ( $_product->get_image_id() ? current( wp_get_attachment_image_src( $_product->get_image_id(), 'thumbnail') ) : wc_placeholder_img_src() ) .'" alt="' . __( 'Product Image', 'woocommerce' ) . '" height="' . esc_attr( $image_size[1] ) . '" width="' . esc_attr( $image_size[0] ) . '" style="vertical-align:middle; margin-right: 10px;" />', $item );
				}

				// Product name
				echo apply_filters( 'woocommerce_order_item_name', $item['name'], $item );

				// SKU
				if ( $show_sku && is_object( $_product ) && $_product->get_sku() ) {
					echo ' (#' . $_product->get_sku() . ')';
				}

				// allow other plugins to add additional product information here
				do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order );

				// Variation
				if ( $item_meta->meta ) {
					echo '<br/><small>' . nl2br( $item_meta->display( true, true, '_', "\n" ) ) . '</small>';
				}

                // Commission id
				if ( ! empty( $item['commission_id'] ) ) {
                    $link = '<a href="' . $commission->get_view_url( 'admin' ) . '">' . $commission->id . '</a>';
					echo '<br/><small>' . _x( 'Commission id:', 'New Order Email', 'yith-woocommerce-product-vendors' ) . ' ' . $link . '</small>';
				}

				// File URLs
				if ( $show_download_links && is_object( $_product ) && $_product->exists() && $_product->is_downloadable() ) {

					$download_files = $order->get_item_downloads( $item );
					$i              = 0;

					foreach ( $download_files as $download_id => $file ) {
						$i++;

						if ( count( $download_files ) > 1 ) {
							$prefix = sprintf( __( 'Download %d', 'woocommerce' ), $i );
						} elseif ( $i == 1 ) {
							$prefix = __( 'Download', 'woocommerce' );
						}

						echo '<br/><small>' . $prefix . ': <a href="' . esc_url( $file['download_url'] ) . '" target="_blank">' . esc_html( $file['name'] ) . '</a></small>';
					}
				}

				// allow other plugins to add additional product information here
				do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order );

			?></td>
			<td style="text-align:center; vertical-align:middle; border: 1px solid #eee;"><?php echo $item['qty'] ;?></td>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee;"><?php echo $order->get_formatted_line_subtotal( $item ); ?></td>
			<td style="text-align:center; vertical-align:middle; border: 1px solid #eee;"><?php echo $commission ? $commission->rate * 100 . '%' : '-'; ?></td>
            <td style="text-align:left; vertical-align:middle; border: 1px solid #eee;"><?php echo $commission ? wc_price( $commission->amount ) : '-'; ?></td>
		</tr>
		<?php
	}

	if ( $show_purchase_note && is_object( $_product ) && ( $purchase_note = get_post_meta( $_product->id, '_purchase_note', true ) ) ) : ?>
		<tr>
			<td colspan="3" style="text-align:left; vertical-align:middle; border: 1px solid #eee;"><?php echo wpautop( do_shortcode( wp_kses_post( $purchase_note ) ) ); ?></td>
		</tr>
	<?php endif; ?>

<?php endforeach; ?>