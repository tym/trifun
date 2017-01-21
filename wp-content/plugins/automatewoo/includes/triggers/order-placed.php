<?php
/**
 * @class       AW_Trigger_Order_Placed
 * @package     AutomateWoo/Triggers
 * @since		2.1.9
 */

class AW_Trigger_Order_Placed extends AW_Trigger_Abstract_Order_Base {

	public $name = 'order_placed';

	/**
	 * Construct
	 */
	function init() {
		$this->title = __('Order Placed', 'automatewoo');
		$this->description = __('Fires as soon as an order is created in the database regardless of its status and happens before payment is confirmed.', 'automatewoo');

		parent::init();
	}


	/**
	 * When could this trigger run?
	 */
	function register_hooks() {
		add_action( 'woocommerce_api_create_order', [ $this, 'catch_hooks' ], 1000 );
		add_action( 'woocommerce_checkout_order_processed', [ $this, 'catch_hooks' ], 1000 );
	}


	/**
	 * @param $order_id
	 */
	function catch_hooks( $order_id ) {
		// Ensure only triggers once per order
		if ( get_post_meta( $order_id, '_aw_checkout_order_processed', true ) ) return;

		add_post_meta( $order_id, '_aw_checkout_order_processed', true );

		parent::catch_hooks( $order_id );
	}

}
