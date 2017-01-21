<?php
/**
 * @class 		AW_Variable_Product_Short_Description
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Product_Short_Description extends AW_Variable
{
	protected $name = 'product.short_description';

	function init()
	{
		$this->description = __( "Displays the product's short description.", 'automatewoo');
	}

	/**
	 * @param $product WC_Product
	 * @param $parameters
	 * @return string
	 */
	function get_value( $product, $parameters )
	{
		return $product->post->post_excerpt;
	}
}

return new AW_Variable_Product_Short_Description();
