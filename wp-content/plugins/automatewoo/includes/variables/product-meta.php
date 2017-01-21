<?php
/**
 * @class 		AW_Variable_Product_Meta
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Product_Meta extends AW_Variable_Abstract_Meta
{
	protected $name = 'product.meta';

	function init()
	{
		parent::init();

		$this->description = __( "Displays an product's meta field.", 'automatewoo');
	}


	/**
	 * @param $product WC_Product
	 * @param $parameters
	 * @return string
	 */
	function get_value( $product, $parameters )
	{
		if ( $parameters['key'] )
		{
			return get_post_meta( $product->id, $parameters['key'], true );
		}
	}
}

return new AW_Variable_Product_Meta();
