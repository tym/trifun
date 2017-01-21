<?php
/**
 * @class 		AW_Rule_Subscription_Payment_Method
 * @package		AutomateWoo/Rules
 */

class AW_Rule_Subscription_Payment_Method extends AW_Rule_Abstract_Select
{
	/** @var array  */
	public $data_item = 'subscription';

	/**
	 * Init
	 */
	function init()
	{
		$this->title = __( 'Subscription Payment Method', 'automatewoo' );
		$this->group = __( 'Subscription', 'automatewoo' );
	}


	function get_select_choices()
	{
		if ( ! isset( $this->select_choices ) )
		{
			foreach( WC()->payment_gateways()->get_available_payment_gateways() as $gateway )
			{
				if ( $gateway->supports('subscriptions') )
				{
					$this->select_choices[$gateway->id] = $gateway->get_title();
				}
			}
		}

		return $this->select_choices;
	}


	/**
	 * @param $subscription WC_Subscription
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $subscription, $compare, $value )
	{
		return $this->validate_select( $subscription->payment_method, $compare, $value );
	}

}

return new AW_Rule_Subscription_Payment_Method();
