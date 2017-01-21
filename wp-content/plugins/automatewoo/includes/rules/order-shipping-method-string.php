<?php
/**
 * @class 		AW_Rule_Order_Shipping_Method_String
 * @package		AutomateWoo/Rules
 */

class AW_Rule_Order_Shipping_Method_String extends AW_Rule_Abstract_String
{
	public $data_item = 'order';

	/**
	 * Init
	 */
	function init()
	{
		$this->title = __( 'Order Shipping Method (String Match)', 'automatewoo' );
		$this->group = __( 'Order', 'automatewoo' );
	}


	/**
	 * @param $order WC_Order
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $order, $compare, $value )
	{
		return $this->validate_string( $order->get_shipping_method(), $compare, $value );
	}

}

return new AW_Rule_Order_Shipping_Method_String();
