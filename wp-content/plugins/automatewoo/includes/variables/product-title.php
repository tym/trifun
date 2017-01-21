<?php
/**
 * @class 		AW_Variable_Product_Title
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Product_Title extends AW_Variable
{
	protected $name = 'product.title';

	function init()
	{
		$this->description = __("Displays the product's title.", 'automatewoo');
	}


	/**
	 * @param $product WC_Product
	 * @param $parameters
	 * @return string
	 */
	function get_value( $product, $parameters )
	{
		return $product->get_title();
	}
}

return new AW_Variable_Product_Title();
