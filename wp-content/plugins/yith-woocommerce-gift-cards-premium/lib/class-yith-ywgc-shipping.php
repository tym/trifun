<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


if ( ! class_exists( 'YITH_YWGC_Shipping' ) ) {

	/**
	 *
	 * @class   YITH_YWGC_Shipping
	 * @package Yithemes
	 * @since   1.0.0
	 * @author  Your Inspiration Themes
	 */
	class YITH_YWGC_Shipping {
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

		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since  1.0
		 * @author Lorenzo Giuffrida
		 */
		protected function __construct() {

			/**
			 * Show gift card used amount, inclusive of shipping discount
			 */
			add_filter( 'woocommerce_coupon_discount_amount_html', array(
				$this,
				'coupon_discount_amount_html'
			), 10, 2 );

			/**
			 * Update shipping cost on cart and checkout page
			 */
			add_filter( 'woocommerce_shipping_packages', array(
				$this,
				'update_shipping_cost'
			) );

			/**
			 * Change the coupon label if it's a gift card
			 */
			add_filter( 'woocommerce_cart_totals_coupon_label', array(
				$this,
				'change_gift_card_label'
			), 10, 2 );

			/**
			 * Update coupon discount in cart, adding the shipping discount
			 */
			add_action( 'woocommerce_calculate_totals', array(
				$this,
				'update_coupon_discount'
			) );

			/**
			 * Show additional information about shipping method discount
			 */
			add_action( 'woocommerce_after_shipping_rate', array(
				$this,
				'highlight_shipping_method_discount'
			), 10, 2 );

			/**
			 * Save the shipping discount as order meta
			 */
			add_action( 'woocommerce_new_order', array(
				$this,
				'save_shipping_discount'
			), 10, 2 );

			/**
			 * Update order totals in my-account, showing shipping discount.
			 */
			add_filter( 'woocommerce_get_order_item_totals', array(
				$this,
				'show_gift_card_shipping_discount_on_order'
			), 10, 2 );

			/**
			 * Update the order total discount, adding shipping discount.
			 */
			add_filter( 'woocommerce_order_amount_total_discount', array(
				$this,
				'update_order_amount_total_discount'
			), 10, 2 );


			add_filter( 'woocommerce_order_amount_total_shipping', array(
				$this,
				'update_order_amount_total_shipping'
			), 10, 2 );

		}

		/**
		 * Show gift card used amount, inclusive of shipping discount
		 *
		 * @param string|WC_Coupon $coupon
		 * @param string           $discount_html
		 *
		 * @@return string
		 */
		public function coupon_discount_amount_html( $discount_html, $coupon ) {


			//  Check if the current coupon was used for shipping discount
			$coupon_code     = $coupon instanceof WC_Coupon ? $coupon->code : $coupon;
			$coupon_discount = isset( WC()->cart->gift_card_shipping_discount_amounts[ $coupon_code ] ) ?
				WC()->cart->gift_card_shipping_discount_amounts[ $coupon_code ] :
				0;

			if ( $coupon_discount <= 0 ) {
				return $discount_html;
			}

			$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
			if ( $chosen_methods ) {
				$chosen_method  = $chosen_methods[0];
				$single_package = WC()->shipping->get_packages()[0];
				$rates          = $single_package['rates'];

				$chosen_rate = $rates[ $chosen_method ];


				if ( isset( $chosen_rate->gift_card_discount_amounts[ $coupon_code ] ) ) {
					$discount_html .= '<div style="font-size: smaller">' . sprintf(
							_x( "(Include %s of shipping cost discount for the selected shipping method)",
								'In Cart/Checkout page: %s stands for the gift card amount. ',
								'yith-woocommerce-gift-cards' ),
							wc_price( $chosen_rate->gift_card_discount_amounts[ $coupon_code ] ) ) .
					                  '</div>';
				}
			}

			return $discount_html;
		}

		/**
		 * Update shipping cost on cart and checkout page
		 *
		 * @param array $packages
		 *
		 * @return array
		 */
		public function update_shipping_cost( $packages ) {

			if ( ! WC()->shipping()->enabled ) {
				return $packages;
			}

			if ( WC()->shipping()->shipping_total ) {
				return $packages;
			}

			/** if current cart total is not 0, then no gift cards is in use or the
			 * whole gift card balance was not enough to discount all products and we cannot
			 * discount the shipping fee.
			 *
			 */
			if ( WC()->cart->get_cart_total() > 0 ) {
				return $packages;
			}

			/** Stop now if  there aren't any gift cards in use */
			if ( ! WC()->cart->get_applied_coupons() ) {

				return $packages;
			}

			/* Search gift card in use */
			$cart_coupons              = WC()->cart->get_applied_coupons();
			$total_discount_amount     = 0;
			$total_discount_tax_amount = 0;
			/*
						foreach ( WC()->cart->get_applied_coupons() as $code ) {
							$gift = YITH_YWGC()->get_gift_card_by_code( $code );

							if ( $gift->exists() ) {
								//  save remaining balance after product discount
								$discount_amount     = WC()->cart->coupon_discount_amounts[ $code ];
								$discount_tax_amount = WC()->cart->coupon_discount_tax_amounts[ $code ];

								$total_discount_amount += $discount_amount;
								$total_discount_tax_amount += $discount_tax_amount;

								$cart_coupons[ $code ] = array(
									'discount_amount'     => $discount_amount,
									'discount_tax_amount' => $discount_tax_amount,
									'data'                => $gift,
								);
							}
						}
			*/

			/** if there aren't any gift cards in use, there is nothing to do */
			if ( ! $cart_coupons ) {
				return $packages;
			}

			/** Apply discount to shipping cost */

			foreach ( $packages as $package_id => $package_content ) {
				$rates = $package_content['rates'];

				foreach ( $rates as $rate_id => $rate_object ) {

					if ( $rate_object->cost <= 0 ) {
						continue;
					}

					$rate_object->original_cost                       = $rate_object->cost;
					$rate_object->original_taxes                      = $rate_object->taxes;
					$rate_object->applied_gift_cards                  = array();
					$rate_object->gift_card_discount_amounts          = array();
					$rate_object->gift_card_discount_tax_amounts      = array();
					$rate_object->gift_card_discount_tax_rate_amounts = array();
					$rate_object->gift_card_total_discount            = 0;
					$rate_object->gift_card_total_tax_discount        = 0;

					foreach ( $cart_coupons as $code ) {

						$gift = YITH_YWGC()->get_gift_card_by_code( $code );

						if ( ! $gift->exists() ) {
							continue;
						}

						$coupon_cart_discount_amount = WC()->cart->coupon_discount_amounts[ $code ];
						$gift_card_updated_balance   = max( 0, $gift->get_balance( false ) - $coupon_cart_discount_amount );

						$shipping_discount = min( $rate_object->cost, $gift_card_updated_balance );
						$new_shipping_cost = max( 0, $rate_object->cost - $shipping_discount );

						$discount_ratio = $new_shipping_cost / $rate_object->cost;//todo check it

						$rate_object->applied_gift_cards[] = $code;

						if ( ! isset( $rate_object->gift_card_discount_amounts[ $code ] ) ) {
							$rate_object->gift_card_discount_amounts[ $code ] = $shipping_discount;
						} else {
							$rate_object->gift_card_discount_amounts[ $code ] += $shipping_discount;
						}
						$rate_object->gift_card_total_discount += $shipping_discount;
						$rate_object->cost = $new_shipping_cost;

						$total_rate_taxes_discount = 0;
						foreach ( $rate_object->taxes as $taxes_id => $taxes_amount ) {

							$prev_rate_tax = $rate_object->taxes[ $taxes_id ];
							$new_rate_tax  = $discount_ratio * $rate_object->taxes[ $taxes_id ];

//							if ( ! isset( $rate_object->gift_card_tax_discount ) ) {
//								$rate_object->gift_card_tax_discount = $current_applied_gc['discount_tax'];
//							} else {
//								$rate_object->gift_card_tax_discount += $current_applied_gc['discount_tax'];
//							}

							$rate_tax_discount = $prev_rate_tax - $new_rate_tax;
							$total_rate_taxes_discount += $rate_tax_discount;

							$rate_object->gift_card_total_tax_discount += $rate_tax_discount;
							$rate_object->taxes[ $taxes_id ] = $new_rate_tax;

							$rate_object->gift_card_discount_tax_rate_amounts[ $code ][ $taxes_id ] = $rate_tax_discount;
						}

						$rate_object->gift_card_discount_tax_amounts[ $code ] = $total_rate_taxes_discount;
					}
				}
			}


			return $packages;
		}

		/**
		 * Change the coupon label if it's a gift card
		 *
		 * @param string    $label
		 * @param WC_Coupon $coupon
		 *
		 * @return string
		 */
		public function change_gift_card_label( $label, $coupon ) {

			/** @var YWGC_Gift_Card_Premium $gift */
			$gift = YITH_YWGC()->get_gift_card_by_code( $coupon->code );
			if ( $gift->exists() ) {
				$label = esc_html( __( 'Gift card:', 'yith-woocommerce-gift-cards' ) . ' ' . $coupon->code );
			}

			return $label;
		}

		/**
		 * Update coupon discount in cart, adding the shipping discount during the calculate_totals call
		 *
		 * @param WC_Cart $cart
		 */
		public function update_coupon_discount( $cart ) {

			// Get all chosen methods
			$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
			//$method_counts  = WC()->session->get( 'shipping_method_counts' );

			$shipping_packages = WC()->shipping->get_packages();

			if ( ! $shipping_packages ) {
				return;
			}

			$single_package  = $shipping_packages[0];
			$chosen_method   = $chosen_methods[0];
			$rates           = $single_package['rates'];
			$selected_method = $rates[ $chosen_method ];

			$shipping_discount     = 0;
			$shipping_tax_discount = 0;

			if ( isset( $selected_method->applied_gift_cards ) && $selected_method->applied_gift_cards ) {
				foreach ( $selected_method->applied_gift_cards as $code ) {

					$shipping_discount += $selected_method->gift_card_discount_amounts[ $code ];
					$cart->coupon_discount_amounts[ $code ] += $selected_method->gift_card_discount_amounts[ $code ];

					if ( $selected_method->gift_card_discount_tax_rate_amounts ) {
						foreach ( $selected_method->gift_card_discount_tax_rate_amounts[ $code ] as $rate_id => $tax_discount ) {
							$cart->coupon_discount_tax_amounts[ $code ] += $tax_discount;
							$shipping_tax_discount += $tax_discount;
						}
					}
				}
			}

			$cart->gift_card_shipping_discount_amounts     = isset( $selected_method->gift_card_discount_amounts ) ? $selected_method->gift_card_discount_amounts : array();
			$cart->gift_card_shipping_discount_tax_amounts = isset( $selected_method->gift_card_discount_tax_rate_amounts ) ? $selected_method->gift_card_discount_tax_rate_amounts : array();

			$cart->gift_card_shipping_discount     = $shipping_discount;
			$cart->gift_card_shipping_tax_discount = $shipping_tax_discount;
		}

		/**
		 * Show additional information about shipping method discount
		 *
		 * @param WC_Shipping_Rate $method
		 * @param int              $index
		 */
		function highlight_shipping_method_discount( $method, $index ) {

			if ( isset( $method->original_cost ) && ( $method->original_cost > $method->cost ) ) {
				if ( $method->cost == 0 ) {
					echo ': ' . wc_price( 0 );
				}

				echo '<div style="font-size: smaller">' . '(' . sprintf( _x( "In place of %s, thanks to gift card discount", "In cart/checkout: about a shipping method discounted with a gift card", 'yith-woocommerce-gift-cards' ), strip_tags( wc_price( $method->original_cost ) ) ) . ')</div>';
			}
		}

		/**
		 * Save the shipping discount as order meta
		 *
		 * @param int $order_id
		 */
		public function save_shipping_discount( $order_id ) {
			$cart = WC()->cart;

			update_post_meta( $order_id, '_gift_card_shipping_discount_amounts', $cart->gift_card_shipping_discount_amounts );
			update_post_meta( $order_id, '_gift_card_shipping_discount_tax_amounts', $cart->gift_card_shipping_discount_tax_amounts );
			update_post_meta( $order_id, '_gift_card_shipping_discount', $cart->gift_card_shipping_discount );
			update_post_meta( $order_id, '_gift_card_shipping_tax_discount', $cart->gift_card_shipping_tax_discount );
		}

		/**
		 * Update shipping totals for the order in my-account, showing shipping discount, if any.
		 *
		 * @param array    $total_rows
		 * @param WC_Order $order
		 *
		 * @return array
		 */
		public function show_gift_card_shipping_discount_on_order( $total_rows, $order ) {

			$shipping_discount = get_post_meta( $order->id, '_gift_card_shipping_discount', true );

			if ( ! empty( $shipping_discount ) && $shipping_discount ) {

				$shipping_tax_discount = get_post_meta( $order->id, '_gift_card_shipping_tax_discount', true );

				$tax_display = $order->tax_display_cart;

				//  Alter discount row, adding shipping discount
				if ( isset( $total_rows['shipping'] ) ) {

					if ( $tax_display == 'excl' ) {

						// Show shipping excluding tax.
						$shipping = wc_price( $order->order_shipping, array( 'currency' => $order->get_order_currency() ) );

						if ( $shipping_tax_discount != 0 && $order->prices_include_tax ) {
							$shipping .= apply_filters( 'woocommerce_order_shipping_to_display_tax_label',
								'&nbsp;<small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>',
								$order, $tax_display );
						}

						$shipping .= apply_filters( 'yith_ywgc_show_original_shipping_cost_when_discounted',
							'<div style="font-size: smaller">' . '(' .
							sprintf( _x( "In place of %s, thanks to gift card discount", "In cart/checkout: about a shipping method discounted with a gift card", 'yith-woocommerce-gift-cards' ),
								strip_tags( wc_price( $order->order_shipping + $shipping_discount ) ) ) . ')</div>',
							$order, $tax_display );


					} else {

						// Show shipping including tax.
						$shipping = wc_price( $order->order_shipping + $order->order_shipping_tax,
							array( 'currency' => $order->get_order_currency() ) );

						if ( $shipping_tax_discount != 0 && ! $order->prices_include_tax ) {
							$shipping .= apply_filters( 'woocommerce_order_shipping_to_display_tax_label',
								'&nbsp;<small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>',
								$order, $tax_display );
						}

						$shipping .= apply_filters( 'yith_ywgc_show_original_shipping_cost_when_discounted',
							'<div style="font-size: smaller">' . '(' .
							sprintf( _x( "In place of %s, thanks to gift card discount", "In cart/checkout: about a shipping method discounted with a gift card", 'yith-woocommerce-gift-cards' ),
								strip_tags( wc_price( $order->order_shipping + $shipping_discount +
								                      $order->order_shipping_tax + $shipping_tax_discount ) ) ) . ')</div>',
							$order, $tax_display );
					}

					$shipping .= apply_filters( 'woocommerce_order_shipping_to_display_shipped_via',
						'&nbsp;<small class="shipped_via">' . sprintf( __( 'via %s', 'woocommerce' ),
							$order->get_shipping_method() ) . '</small>',
						$order );

					$total_rows['shipping']['value'] = $shipping;
				}

				if ( isset( $total_rows['discount'] ) ) {

					$total_rows['discount']['value'] .= '<div style="font-size: smaller">' . sprintf(
							_x( "(Include %s of shipping cost discount through a gift card)",
								'In Cart/Checkout page state that a gift card is used for discounting shipping cost',
								'yith-woocommerce-gift-cards' ),
							wc_price( $shipping_discount ) ) .
					                                    '</div>';
				}
			}

			return $total_rows;
		}

		public function update_order_amount_total_discount( $total, $order ) {

			$shipping_discount = get_post_meta( $order->id, '_gift_card_shipping_discount', true );

			if ( ! empty( $shipping_discount ) && $shipping_discount ) {

				$shipping_tax_discount = get_post_meta( $order->id, '_gift_card_shipping_tax_discount', true );

				$tax_display = $order->tax_display_cart;
				$ex_tax      = $tax_display === 'excl';


				if ( ! $order->order_version || version_compare( $order->order_version, '2.3.7', '<' ) ) {
					// Backwards compatible total calculation - totals were not stored consistently in old versions.
					if ( $ex_tax ) {
						if ( $order->prices_include_tax ) {
							$total = (double) $order->cart_discount - (double) $order->cart_discount_tax
							         + (double) $shipping_discount - (double) $shipping_tax_discount;
						} else {
							$total = (double) $order->cart_discount + (double) $shipping_discount;
						}
					} else {
						if ( $order->prices_include_tax ) {
							$total = (double) $order->cart_discount + (double) $shipping_discount;
						} else {
							$total = (double) $order->cart_discount + (double) $order->cart_discount_tax
							         + (double) $shipping_discount + (double) $shipping_tax_discount;
						}
					}
					// New logic - totals are always stored exclusive of tax, tax total is stored in cart_discount_tax
				} else {
					if ( $ex_tax ) {
						$total = (double) $order->cart_discount + (double) $shipping_discount;
					} else {
						$total = (double) $order->cart_discount + (double) $order->cart_discount_tax
						         + (double) $shipping_discount + (double) $shipping_tax_discount;
					}
				}
			}

			return $total;
		}

		public function update_order_amount_total_shipping( $total, $order ) {
			$shipping_discount = get_post_meta( $order->id, '_gift_card_shipping_discount', true );
			$tax_display       = $order->tax_display_cart;

			if ( ! empty( $shipping_discount ) && $shipping_discount ) {
				$shipping_tax_discount = get_post_meta( $order->id, '_gift_card_shipping_tax_discount', true );

				if ( $tax_display == 'excl' ) {

					$total += $shipping_discount;

				} else {

					$total += $shipping_discount + $shipping_tax_discount;

				}
			}

			return $total;
		}
	}
}

YITH_YWGC_Shipping::get_instance();