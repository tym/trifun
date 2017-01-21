<?php
/**
 * @class 		AW_Variable_Order_Related_Products
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Order_Related_Products extends AW_Variable_Abstract_Product_Display {

	protected $name = 'order.related_products';

	public $support_limit_field = true;

	/**
	 * Init
	 */
	function init() {

		parent::init();

		$this->description = __( "Displays a listing of products related to the items in an order.", 'automatewoo');
	}


	/**
	 * @param $order WC_Order
	 * @param $parameters array
	 * @param $workflow
	 * @return mixed
	 */
	function get_value( $order, $parameters, $workflow ) {

		$related = [];
		$in_order = [];
		$template = isset( $parameters['template'] ) ? $parameters['template'] : false;
		$limit = isset( $parameters['limit'] ) ? absint( $parameters['limit'] ) : 8;

		$items = $order->get_items();

		foreach ( $items as $item ) {
			$product = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
			$in_order[] = $product->id;
			$related = array_merge( $product->get_related(), $related );
		}

		$related = array_diff( $related, $in_order );

		if ( empty( $related ) )
			return false;

		$products = $this->prepare_products( $related, $limit );

		$args = array_merge( $this->get_default_product_template_args( $workflow ), [
			'products' => $products,
		]);

		return $this->get_product_display_html( $template, $args );
	}
}

return new AW_Variable_Order_Related_Products();