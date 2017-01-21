<?php
/**
 * @class 		AW_Variable_Subscription_Status
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Subscription_Status extends AW_Variable
{
	protected $name = 'subscription.status';

	function init()
	{
		$this->description = __( "Displays the formatted status of the subscription.", 'automatewoo');
	}

	/**
	 * @param $subscription WC_Subscription
	 * @param $parameters
	 * @return string
	 */
	function get_value( $subscription, $parameters )
	{
		return $subscription->get_status();
	}
}

return new AW_Variable_Subscription_Status();

