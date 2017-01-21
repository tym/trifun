<?php
/**
 * @class 		AW_Rule_Order_Items
 * @package		AutomateWoo/Rules
 */

class AW_Rule_Order_Items extends AW_Rule_Abstract_Object
{
	public $data_item = 'order';

	public $is_multi = false;

	public $ajax_action = 'woocommerce_json_search_products_and_variations';

	public $class = 'wc-product-search';


	/**
	 * Init
	 */
	function init()
	{
		$this->title = __( 'Order Items', 'automatewoo' );
		$this->group = __( 'Order', 'automatewoo' );
		$this->placeholder = __( 'Search products...', 'automatewoo' );

		$this->compare_types = [
			'includes' => __( 'includes', 'automatewoo' ),
			'not_includes' => __( 'does not include', 'automatewoo' )
		];
	}


	/**
	 * @param $value
	 * @return string
	 */
	function get_object_display_value( $value )
	{
		if ( $product = wc_get_product( absint( $value ) ) )
			return $product->get_formatted_name();
	}


	/**
	 * @param $order WC_Order
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $order, $compare, $value )
	{
		if ( ! $product = wc_get_product( absint( $value ) ) )
			return false;

		$id_key = $product->get_type() == 'variation' ? 'variation_id' : 'product_id';
		$id_object_key = $product->get_type() == 'variation' ? 'variation_id' : 'id';
		$includes = false;

		foreach ( $order->get_items() as $item )
		{
			if ( isset($item[$id_key]) && $item[$id_key] == $product->$id_object_key )
			{
				$includes = true;
				break;
			}
		}

		switch ( $compare )
		{
			case 'includes':
				return $includes;
				break;

			case 'not_includes':
				return ! $includes;
				break;
		}

	}
}

return new AW_Rule_Order_Items();