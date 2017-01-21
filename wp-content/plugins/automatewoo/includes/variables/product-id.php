<?php
/**
 * @class 		AW_Variable_Product_Id
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Product_Id extends AW_Variable
{
	protected $name = 'product.id';

	function init()
	{
		$this->description = __( "Displays the product's ID.", 'automatewoo');
	}

	/**
	 * @param $product WC_Product
	 * @param $parameters
	 * @return string
	 */
	function get_value( $product, $parameters )
	{
		return $product->id;
	}
}

return new AW_Variable_Product_Id();