<?php
/**
 * @class 		AW_Variable_Product_Featured_Image
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Product_Featured_Image extends AW_Variable
{
	protected $name = 'product.featured_image';


	/**
	 * Init
	 */
	function init()
	{
		$this->description = __( "Displays the product's featured image.", 'automatewoo');
	}


	/**
	 * @param $product WC_Product
	 * @param $parameters
	 * @return string
	 */
	function get_value( $product, $parameters )
	{
		return $product->get_image('shop_catalog');
	}
}

return new AW_Variable_Product_Featured_Image();