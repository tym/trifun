<?php
/**
 * @class 		AW_Variable_Wishlist_Items
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Wishlist_Items extends AW_Variable_Abstract_Product_Display {

	protected $name = 'wishlist.items';


	function init() {
		parent::init();

		$this->description = __( "Display a product listing of the items in the wishlist.", 'automatewoo');
	}


	/**
	 * @param $wishlist
	 * @param $parameters
	 * @param $workflow
	 * @return string
	 */
	function get_value( $wishlist, $parameters, $workflow ) {

		$products = [];
		$template = isset( $parameters['template'] ) ? $parameters['template'] : false;

		foreach ( $wishlist->items as $product_id ) {
			$products[] = wc_get_product( $product_id );
		}

		$args = array_merge( $this->get_default_product_template_args( $workflow ), [
			'products' => $products
		]);

		return $this->get_product_display_html( $template, $args );
	}
}

return new AW_Variable_Wishlist_Items();
