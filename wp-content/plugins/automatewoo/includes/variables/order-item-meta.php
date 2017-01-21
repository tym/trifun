<?php
/**
 * @class 		AW_Variable_Order_Item_Meta
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Order_Item_Meta extends AW_Variable
{
	protected $name = 'order_item.meta';

	function init()
	{
		$this->description = __( "Can be used to display the value of an order item meta field.", 'automatewoo');

		$this->add_parameter_text_field( 'key', __( "The key of the order item meta field.", 'automatewoo'), true );
	}


	/**
	 * @param $order_item
	 * @param $parameters
	 * @return string
	 */
	function get_value( $order_item, $parameters )
	{
		if ( empty( $parameters['key'] ) )
			return false;

		return wc_get_order_item_meta( $order_item['id'], $parameters['key'], true );
	}
}

return new AW_Variable_Order_Item_Meta();
