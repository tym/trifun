<?php
/**
 * @class 		AW_Variable_Subscription_ID
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Subscription_ID extends AW_Variable
{
	protected $name = 'subscription.id';

	/**
	 * Init
	 */
	function init()
	{
		$this->description = __( "Displays the ID of the subscription.", 'automatewoo');
	}

	/**
	 * @param $subscription WC_Subscription
	 * @param $parameters
	 * @return string
	 */
	function get_value( $subscription, $parameters )
	{
		return $subscription->id;
	}
}

return new AW_Variable_Subscription_ID();

