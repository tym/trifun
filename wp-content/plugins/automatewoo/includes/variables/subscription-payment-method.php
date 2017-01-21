<?php
/**
 * @class 		AW_Variable_Subscription_Payment_Method
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Subscription_Payment_Method extends AW_Variable
{
	protected $name = 'subscription.payment_method';

	function init()
	{
		$this->description = __( "Displays the payment method of the subscription.", 'automatewoo');
	}

	/**
	 * @param $subscription WC_Subscription
	 * @param $parameters
	 * @return string
	 */
	function get_value( $subscription, $parameters )
	{
		return $subscription->get_payment_method_to_display();
	}
}

return new AW_Variable_Subscription_Payment_Method();

