<?php
/**
 * @class 		AW_Rules_Loader
 * @package		AutomateWoo
 * @since		2.6
 */

class AW_Rules_Loader {

	/** @var array */
	private $includes;

	/** @var array  */
	private $rules = [];


	/**
	 * @return array
	 */
	function get_includes() {
		if ( ! isset( $this->includes ) ) {

			$path = AW()->path( '/includes/rules/' );

			$this->includes = [
				'order_item_count' => $path . 'order-item-count.php',
				'order_total' => $path . 'order-total.php',
				'order_items' => $path . 'order-items.php',
				'order_coupons' => $path . 'order-coupons.php',
				'order_payment_gateway' => $path . 'order-payment-gateway.php',
				'order_shipping_country' => $path . 'order-shipping-country.php',
				'order_billing_country' => $path . 'order-billing-country.php',
				'order_shipping_method' => $path . 'order-shipping-method.php',
				'order_shipping_method_string' => $path . 'order-shipping-method-string.php',
				'order_has_cross_sells' => $path . 'order-has-cross-sells.php',
				'order_is_customers_first' => $path . 'order-is-customers-first.php',
				'order_is_guest_order' => $path . 'order-is-guest-order.php',

				'cart_total' => $path . 'cart-total.php',
				'cart_count' => $path . 'cart-count.php',
				'cart_items' => $path . 'cart-items.php',
				'cart_coupons' => $path . 'cart-coupons.php',

				'user_role' => $path . 'user-role.php',
				'user_tags' => $path . 'user-tags.php',
				'user_total_spent' => $path . 'user-total-spent.php',
				'user_order_count' => $path . 'user-order-count.php',
				'user_email' => $path . 'user-email.php',
				'user_run_count' => $path . 'user-run-count.php',
				'user_purchased_products' => $path . 'user-purchased-products.php',
				'user_is_active_subscriber' => $path . 'user-is-active-subscriber.php',

				'guest_email' => $path . 'guest-email.php',
				'guest_run_count' => $path . 'guest-run-count.php',
			];


			if ( AW()->integrations()->subscriptions_enabled() ) {
				$this->includes[ 'subscription_payment_count' ] = $path . 'subscription-payment-count.php';
				$this->includes[ 'subscription_payment_method' ] = $path . 'subscription-payment-method.php';
			}


			if ( AW()->integrations()->is_woo_pos() ) {
				$this->includes[ 'order_is_pos' ] = $path . 'order-is-pos.php';
			}

			$this->includes = apply_filters( 'automatewoo/rules/includes', $this->includes );
		}

		return $this->includes;
	}


	/**
	 * Get all conditions
	 *
	 * @return array
	 */
	function get_rules() {
		foreach ( $this->get_includes() as $name => $path ) {
			$this->load( $name );
		}

		return $this->rules;
	}


	/**
	 * @param $rule_name
	 * @return AW_Rule_Abstract|false
	 */
	function get_rule( $rule_name ) {
		$this->load( $rule_name );

		return $this->rules[ $rule_name ];
	}


	/**
	 * @param $rule_name
	 * @return bool
	 */
	private function is_loaded( $rule_name ) {
		return isset( $this->rules[ $rule_name ] );
	}


	/**
	 * @param $rule_name
	 * @return void
	 */
	private function load( $rule_name ) {

		if ( $this->is_loaded( $rule_name ) )
			return;

		$rule = false;
		$includes = $this->get_includes();

		if ( ! empty( $includes[ $rule_name ] ) ) {
			if ( file_exists( $includes[ $rule_name ] ) ) {
				$rule = include_once $includes[ $rule_name ];
				$rule->name = $rule_name;
			}
		}

		$this->rules[ $rule_name ] = $rule;
	}

}
