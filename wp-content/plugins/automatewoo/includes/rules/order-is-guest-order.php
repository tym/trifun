<?php
/**
 * @class AW_Rule_Is_Guest_Order
 * @package		AutomateWoo/Rules
 */

class AW_Rule_Is_Guest_Order extends AW_Rule_Abstract_Bool
{
	public $data_item = 'order';

	/**
	 * Init
	 */
	function init()
	{
		$this->title = __( "Order Placed By Guest", 'automatewoo' );
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
		$is_guest = $order->get_user_id() === 0;

		switch ( $value )
		{
			case 'yes':
				return $is_guest;
				break;

			case 'no':
				return ! $is_guest;
				break;
		}
	}
}

return new AW_Rule_Is_Guest_Order();
