<?php
/**
 * @class 		AW_Variable_Order_Meta
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Order_Meta extends AW_Variable_Abstract_Meta
{
	protected $name = 'order.meta';

	function init()
	{
		parent::init();

		$this->description = __( "Displays an orders's meta field.", 'automatewoo');
	}

	/**
	 * @param $order WC_Order
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $order, $parameters )
	{
		if ( $parameters['key'] )
		{
			return get_post_meta( $order->id, $parameters['key'], true );
		}
	}
}

return new AW_Variable_Order_Meta();
