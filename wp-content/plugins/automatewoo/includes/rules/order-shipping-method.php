<?php
/**
 * @class 		AW_Rule_Order_Shipping_Method
 * @package		AutomateWoo/Rules
 */

class AW_Rule_Order_Shipping_Method extends AW_Rule_Abstract_Select
{
	public $data_item = 'order';

	public $is_multi = true;

	/**
	 * Init
	 */
	function init()
	{
		$this->title = __( 'Order Shipping Method', 'automatewoo' );
		$this->group = __( 'Order', 'automatewoo' );
	}


	/**
	 * @return array
	 */
	function get_select_choices()
	{
		if ( ! isset( $this->select_choices ) )
		{
			foreach ( WC()->shipping()->get_shipping_methods() as $method_id => $method )
			{
				// get_method_title() added in WC 2.6
				$this->select_choices[$method_id] = method_exists( $method, 'get_method_title' ) ? $method->get_method_title() : $method->get_title();
			}
		}

		return $this->select_choices;
	}


	/**
	 * @param $order WC_Order
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $order, $compare, $value )
	{
		$methods = [];

		foreach( $order->get_shipping_methods() as $method )
		{
			// extract method slug only, discard instance id
			if ( $split = strpos( $method['method_id'], ':') )
			{
				$methods[] = substr( $method['method_id'], 0, $split );
			}
			else
			{
				$methods[] = $method['method_id'];
			}
		}

		return $this->validate_select( $methods, $compare, $value );
	}

}

return new AW_Rule_Order_Shipping_Method();
