<?php
/**
 * @class 		AW_Variable_Order_Items
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Order_Items extends AW_Variable_Abstract_Product_Display {

	/** @var string  */
	protected $name = 'order.items';

	/**
	 * Init
	 */
	function init() {
		parent::init();
		$this->description = __( "Displays a product listing of items in an order.", 'automatewoo');
	}


	/**
	 * @param $order WC_Order
	 * @param $parameters array
	 * @param $workflow
	 * @return string
	 */
	function get_value( $order, $parameters, $workflow ) {

		$template = isset( $parameters['template'] ) ? $parameters['template'] : false;
		$items = $order->get_items();
		$products = [];

		foreach ( $items as $item ) {
			$products[] = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
		}

		$args = array_merge( $this->get_default_product_template_args( $workflow ), [
			'products' => $products,
			'order' => $order
		]);

		return $this->get_product_display_html( $template, $args );
	}

}

return new AW_Variable_Order_Items();
