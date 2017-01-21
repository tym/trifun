<?php
/**
 * @class 		AW_Rule_Order_Billing_Country
 * @package		AutomateWoo/Rules
 */

class AW_Rule_Order_Billing_Country extends AW_Rule_Abstract_Select
{
	public $data_item = 'order';

	/**
	 * Init
	 */
	function init()
	{
		$this->title = __( 'Order Billing Country', 'automatewoo' );
		$this->group = __( 'Order', 'automatewoo' );
	}


	/**
	 * @return array
	 */
	function get_select_choices()
	{
		if ( ! isset( $this->select_choices ) )
		{
			$this->select_choices = WC()->countries->get_allowed_countries();
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
		if ( ! $order->billing_country )
			return false;

		return $this->validate_select( $order->billing_country, $compare, $value );
	}

}

return new AW_Rule_Order_Billing_Country();
