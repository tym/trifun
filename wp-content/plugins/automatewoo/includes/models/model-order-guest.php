<?php
/**
 * Imitates WP_User object but ID is always 0
 *
 * This object should be used as a data-type 'user' and can be queued with an order
 *
 * @class       AW_Model_Order_Guest
 * @package     AutomateWoo/Models
 * @since       2.1.0
 */

class AW_Model_Order_Guest
{
	/** @var int */
	public $ID = 0;

	/** @var string */
	public $user_email;

	/** @var string */
	public $first_name;

	/** @var string */
	public $last_name;

	/** @var string */
	public $billing_phone;

	/** @var WC_Order */
	public $order;

	/** @var array  */
	public $roles = [ 'guest' ];


	/**
	 * @param $order WC_Order|bool
	 */
	function __construct( $order = false )
	{
		if ( $order )
		{
			$this->order = $order;
			$this->user_email = $order->billing_email;
			$this->first_name = $order->billing_first_name;
			$this->last_name = $order->billing_last_name;
			$this->billing_phone = $order->billing_phone;
		}
	}
}