<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'YWGC_Dynamic_Pricing' ) ) {

	/**
	 *
	 * @class   YWGC_Dynamic_Pricing
	 * @package Yithemes
	 * @since   1.0.0
	 * @author  Your Inspiration Themes
	 */
	class YWGC_Dynamic_Pricing {

		/**
		 * Single instance of the class
		 *
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function __construct() {
			/**
			 * YITH WooCommerce Dynamic Pricing and Discount Premium compatibility.
			 * Single product template. Manage the price exclusion when used with the YITH WooCommerce Dynamic Pricing
			 */
			add_filter( 'ywdpd_get_price_exclusion', array(
				$this,
				'exclude_price_for_yith_dynamic_discount_product_page'
			), 10, 3 );

			/**
			 * YITH WooCommerce Dynamic Pricing and Discount Premium compatibility.
			 * Cart template. Manage the price exclusion
			 */
			add_filter( 'ywdpd_replace_cart_item_price', array(
				$this,
				'set_price_for_yith_dynamic_discount_cart_page'
			), 10, 4 );

			/**
			 * YITH WooCommerce Dynamic Pricing and Discount Premium compatibility.
			 * Show the table with pricing discount
			 */
			add_filter( 'ywdpd_show_price_on_table_pricing', array(
				$this,
				'show_price_on_table_pricing'
			), 10, 3 );

		}

		/**
		 * Show discounted price in the YITH WooCommerce Dynamic Pricing table
		 *
		 * @param string     $html    current value being shown
		 * @param array      $rule    rule to be applied
		 * @param WC_Product $product current product
		 *
		 * @return string
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function show_price_on_table_pricing( $html, $rule, $product ) {
			if ( YWGC_GIFT_CARD_PRODUCT_TYPE != $product->product_type ) {
				return $html;
			}
			/** @var WC_Product_Gift_Card $product */
			$prices = $product->amounts;
			if ( $prices ) {
				$min_price          = current( $prices );
				$discount_min_price = ywdpd_get_discounted_price_table( $min_price, $rule );
				$max_price          = end( $prices );
				$discount_max_price = ywdpd_get_discounted_price_table( $max_price, $rule );

				$html = $discount_min_price !== $discount_max_price ? sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), wc_price( $discount_min_price ), wc_price( $discount_max_price ) ) : wc_price( $discount_min_price );

			}

			return $html;
		}

		/**
		 * Single product template. Manage the price exclusion when used with the YITH WooCommerce Dynamic Pricing
		 *
		 * @param bool       $status  current visibility status
		 * @param float      $price   the price to be shown
		 * @param WC_Product $product the product in use
		 *
		 * @return bool
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function exclude_price_for_yith_dynamic_discount_product_page( $status, $price, $product ) {

			if ( YWGC_GIFT_CARD_PRODUCT_TYPE == $product->product_type ) {
				return true;
			}

			return $status;
		}

		/**
		 * Cart template. Manage the price exclusion when used with the YITH WooCommerce Dynamic Pricing
		 *
		 * @param float $price     the formatted price that will be shown in place of the real price
		 * @param float $old_price the real price
		 * @param array $cart_item
		 * @param array $cart_item_key
		 *
		 * @return mixed
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function set_price_for_yith_dynamic_discount_cart_page( $price, $old_price, $cart_item, $cart_item_key ) {

			$_product = $cart_item['data'];

			if ( isset( $_product ) && ( $_product instanceof WC_Product_Gift_Card ) ) {
				if ( isset( $cart_item['amount'] ) ) {

					$original_price = $cart_item['amount'];
					$price          = '<del>' . wc_price( $original_price ) . '</del> ' . WC()->cart->get_product_price( $_product );
				}
			}

			return $price;
		}

	}
}

YWGC_Dynamic_Pricing::get_instance();