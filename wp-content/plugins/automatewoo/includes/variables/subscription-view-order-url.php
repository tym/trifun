<?php
/**
 * @class 		AW_Variable_Subscription_View_Order_Url
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Subscription_View_Order_Url extends AW_Variable
{
	protected $name = 'subscription.view_order_url';

	/**
	 * Init
	 */
	function init()
	{
		$this->description = __( "Displays a URL to the subscription page in the My Account area.", 'automatewoo');
	}

	/**
	 * @param $subscription WC_Subscription
	 * @param $parameters
	 * @return string
	 */
	function get_value( $subscription, $parameters )
	{
		return $subscription->get_view_order_url();
	}
}

return new AW_Variable_Subscription_View_Order_Url();
