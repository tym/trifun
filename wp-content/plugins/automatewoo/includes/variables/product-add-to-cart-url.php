<?php
/**
 * @class 		AW_Variable_Product_Add_To_Cart_Url
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Product_Add_To_Cart_Url extends AW_Variable
{
	protected $name = 'product.add_to_cart_url';

	/**
	 * Init
	 */
	function init()
	{
		$this->description = __( "Displays a link to the product that will also add the product to the users cart when clicked.", 'automatewoo');
	}


	/**
	 * @param $product WC_Product
	 * @param $parameters
	 * @return string
	 */
	function get_value( $product, $parameters )
	{
		return add_query_arg( 'add-to-cart', $product->id, $product->get_permalink() );
	}

}

return new AW_Variable_Product_Add_To_Cart_Url();