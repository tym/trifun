<?php
/**
 * Admin new order email
 *
 * @author WooThemes
 * @package WooCommerce/Templates/Emails/HTML
 * @version 2.0.0
 *
 * @var YITH_Commission $commission
 * @var YITH_Vendor $vendor
 * @var WC_Product $product
 * @var WC_Order $order
 * @var array $item
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
		<tr>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php _e( 'Owner:', 'yith-woocommerce-product-vendors' ) ?></td>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo $owner->user_firstname . ' ' . $owner->user_lastname ?></td>
		</tr>

		<tr>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php _e( 'Store Name:', 'yith-woocommerce-product-vendors' ) ?></td>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo $vendor->name ?></td>
		</tr>

		<tr>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php _e( 'Location', 'yith-woocommerce-product-vendors' ) ?></td>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo $vendor->location ?></td>
		</tr>

		<tr>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php _e( 'Store Email', 'yith-woocommerce-product-vendors' ) ?></td>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo $vendor->store_email ?></td>
		</tr>

		<tr>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php _e( 'Telephone', 'yith-woocommerce-product-vendors' ) ?></td>
			<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php echo $vendor->telephone ?></td>
		</tr>