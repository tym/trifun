<?php
/**
 * @class 		AW_Variable_Subscription_Trial_End_Date
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Subscription_Trial_End_Date extends AW_Variable_Abstract_Datetime
{
	protected $name = 'subscription.trial_end_date';

	function init()
	{
		parent::init();

		$this->description = __( "Displays the subscription trial end date in your website's timezone.", 'automatewoo' ) . ' ' . $this->_desc_format_tip;
	}

	/**
	 * @param $subscription WC_Subscription
	 * @param $parameters
	 * @return string
	 */
	function get_value( $subscription, $parameters )
	{
		return $this->format_datetime( $subscription->get_date( 'trial_end', 'site' ), $parameters );
	}
}

return new AW_Variable_Subscription_Trial_End_Date();
