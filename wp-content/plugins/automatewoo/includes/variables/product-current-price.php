<?php
/**
 * @class 		AW_Variable_Product_Current_Price
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Product_Current_Price extends AW_Variable
{
	protected $name = 'product.current_price';


	/**
	 * Init
	 */
	function init()
	{
		$this->description = __( "Displays the product's formatted current price.", 'automatewoo');
	}

	/**
	 * @param $product WC_Product
	 * @param $parameters
	 * @return string
	 */
	function get_value( $product, $parameters )
	{
		return wc_price( $product->get_price() );
	}
}

return new AW_Variable_Product_Current_Price();