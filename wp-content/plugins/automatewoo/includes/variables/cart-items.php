<?php
/**
 * @class 		AW_Variable_Cart_Items
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Cart_Items extends AW_Variable_Abstract_Product_Display {

	protected $name = 'cart.items';

	/**
	 * Init
	 */
	function init() {

		parent::init();

		$this->description = __( "Display a product listing of the items in the cart.", 'automatewoo');
	}


	/**
	 * @param $cart AW_Model_Abandoned_Cart
	 * @param $parameters array
	 * @param $workflow AW_Model_Workflow
	 * @return string
	 */
	function get_value( $cart, $parameters, $workflow ) {

		$cart_items = $cart->get_items();
		$template = isset( $parameters['template'] ) ? $parameters['template'] : false;

		$products = [];
		$product_ids = [];

		if ( ! is_array( $cart_items ) )
			return;

		foreach ( $cart_items as $item ) {
			if ( ! empty( $item['variation_id'] ) ) {
				$product_ids[] = $item['variation_id'];
			}
			elseif ( ! empty( $item['product_id'] ) ) {
				$product_ids[] = $item['product_id'];
			}
		}

		$product_ids = array_unique( $product_ids );

		foreach ( $product_ids as $product_id ) {
			$products[] = wc_get_product( $product_id );
		}

		$args = array_merge( $this->get_default_product_template_args( $workflow ), [
			'products' => $products,
			'cart_items' => $cart_items,
			'cart' => $cart
		] );

		return $this->get_product_display_html( $template, $args );
	}
}

return new AW_Variable_Cart_Items();
