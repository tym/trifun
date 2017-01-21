<?php
/**
 * @class 		AW_Variable_Subscription_Last_Payment_Date
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Subscription_Last_Payment_Date extends AW_Variable_Abstract_Datetime
{
	protected $name = 'subscription.last_payment_date';

	function init()
	{
		parent::init();

		$this->description = __( "Displays the date of the most recent payment for the subscription.", 'automatewoo') . ' ' . $this->_desc_format_tip;
	}

	/**
	 * @param $subscription WC_Subscription
	 * @param $parameters
	 * @return string
	 */
	function get_value( $subscription, $parameters )
	{
		return $this->format_datetime( $subscription->get_date( 'last_payment', 'site' ), $parameters );
	}
}

return new AW_Variable_Subscription_Last_Payment_Date();
