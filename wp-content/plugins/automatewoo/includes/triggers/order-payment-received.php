<?php
/**
 * @class      AW_Trigger_Order_Placed
 * @package    AutomateWoo/Triggers
 * @since		2.7.6
 */

class AW_Trigger_Order_Payment_Received extends AW_Trigger_Abstract_Order_Base {

	public $name = 'order_payment_received';


	/**
	 * Construct
	 */
	function init() {

		$this->title = __( 'Order Payment Received', 'automatewoo' );
		$this->description = __( 'Fires once at the end of the payment process after the order status has been changed and stock has been reduced.', 'automatewoo' );

		parent::init();
	}


	/**
	 * When could this trigger run?
	 */
	function register_hooks() {
		add_action( 'woocommerce_payment_complete', [ $this, 'catch_hooks' ], 100 );
	}


}
