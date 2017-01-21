<?php
/**
 * @class 		AW_Variable_Product_Regular_Price
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Product_Regular_Price extends AW_Variable
{
	protected $name = 'product.regular_price';

	function init()
	{
		$this->description = __( "Displays the product's formatted regular price.", 'automatewoo');
	}

	/**
	 * @param $product WC_Product
	 * @param $parameters
	 * @return string
	 */
	function get_value( $product, $parameters )
	{
		return wc_price( $product->get_regular_price() );
	}
}

return new AW_Variable_Product_Regular_Price();