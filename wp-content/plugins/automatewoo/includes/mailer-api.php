<?php
/**
 * Static mailer API.
 *
 * Used to display dynamic AW content in email template files.
 *
 * @class 		AW_Mailer_API
 * @since		2.2
 * @package		AutomateWoo
 */

class AW_Mailer_API {

	/**
	 * @var AW_Mailer
	 */
	static $mailer;

	/**
	 * @return bool|string
	 */
	static function email() {
		if ( ! self::$mailer ) return false;
		return self::$mailer->email;
	}


	/**
	 * @return bool|string
	 */
	static function subject() {
		if ( ! self::$mailer ) return false;
		return self::$mailer->subject;
	}


	/**
	 * @return bool|string
	 */
	static function unsubscribe_url() {
		if ( ! self::$mailer ) return false;
		return self::$mailer->get_unsubscribe_url();
	}


	/**
	 * @param WC_Product $product
	 * @param string $size
	 * @return array|false|string
	 */
	static function get_product_image( $product, $size = 'shop_catalog' ) {

		if ( $image_id = $product->get_image_id() ) {
			$image_url = wp_get_attachment_image_url( $image_id, $size );

			$image = '<img src="' . esc_url( $image_url ) . '" class="aw-product-image" alt="'. esc_attr( $product->get_title() ) .'">';
		}
		else {
			$image = wc_placeholder_img( $size );
		}

		return $image;
	}

}


