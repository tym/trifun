<?php
/**
 * @class 		AW_Rule_Order_Is_Customers_First
 * @package		AutomateWoo/Rules
 */

class AW_Rule_Order_Is_Customers_First extends AW_Rule_Abstract_Bool
{
	public $data_item = 'order';

	/**
	 * Init
	 */
	function init()
	{
		$this->title = __( "Order Is Customer's First", 'automatewoo' );
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
		$orders = wc_get_orders([
			'customer' => $order->get_user_id() > 0 ? $order->get_user_id() : $order->billing_email,
			'limit' => 1,
			'return' => 'ids',
			'exclude' => [ $order->id ]
		]);

		$is_first = empty( $orders );

		switch ( $value )
		{
			case 'yes':
				return $is_first;
				break;

			case 'no':
				return ! $is_first;
				break;
		}
	}

}

return new AW_Rule_Order_Is_Customers_First();
