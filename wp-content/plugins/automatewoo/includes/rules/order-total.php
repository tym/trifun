<?php
/**
 * @class 		AW_Condition_Order_Includes_Product
 * @package		AutomateWoo/Rules
 */

class AW_Rule_Order_Total extends AW_Rule_Abstract_Number
{
	public $data_item = 'order';

	public $support_floats = true;

	/**
	 * Init
	 */
	function init()
	{
		$this->title = __( 'Order Total', 'automatewoo' );
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
		return $this->validate_number( $order->get_total(), $compare, $value );
	}

}

return new AW_Rule_Order_Total();
