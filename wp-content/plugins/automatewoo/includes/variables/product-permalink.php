<?php
/**
 * @class 		AW_Variable_Product_Permalink
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Product_Permalink extends AW_Variable
{
	protected $name = 'product.permalink';

	function init()
	{
		$this->description = __( "Displays the permalink to the product.", 'automatewoo');
	}


	/**
	 * @param $product WC_Product
	 * @param $parameters
	 * @return string
	 */
	function get_value( $product, $parameters )
	{
		return $product->get_permalink();
	}
}

return new AW_Variable_Product_Permalink();

