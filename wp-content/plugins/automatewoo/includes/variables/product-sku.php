<?php
/**
 * @class 		AW_Variable_Product_Sku
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Product_Sku extends AW_Variable
{
	protected $name = 'product.sku';

	function init()
	{
		$this->description = __( "Displays the product's SKU.", 'automatewoo');
	}

	/**
	 * @param $product WC_Product
	 * @param $parameters
	 * @return string
	 */
	function get_value( $product, $parameters )
	{
		return $product->get_sku();
	}
}

return new AW_Variable_Product_Sku();
