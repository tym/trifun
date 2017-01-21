<?php
/**
 * @class       AW_Trigger_Abstract_Order_Status_Base
 * @package     AutomateWoo/Triggers
 */

abstract class AW_Trigger_Abstract_Order_Status_Base extends AW_Trigger_Abstract_Order_Base {

	public $supplied_data_items = [ 'user', 'order', 'shop' ];

	/** @var string|false */
	public $_target_status = false;
	

	/**
	 * Add options to the trigger
	 */
	function load_fields() {

		$this->group = __( 'Order', 'automatewoo' );
		$this->add_field_validate_queued_order_status();

		parent::load_fields();
	}


	/**
	 * Don't use status specific hooks as they fire too early
	 */
	function register_hooks() {
		add_action( 'woocommerce_order_status_changed', [ $this, 'catch_hooks' ], 100, 3 );
	}


	/**
	 * @param $order_id
	 * @param bool $old_status
	 * @param bool $new_status
	 */
	function catch_hooks( $order_id, $old_status = false, $new_status = false ) {

		if ( $this->_target_status && $new_status ) {
			if ( $new_status !== $this->_target_status )
				return;
		}

		$order = wc_get_order( $order_id );
		$user = AW()->order_helper->prepare_user_data_item( $order );

		// bit of hack to pass in old status
		$order->_aw_old_status = $old_status;

		$this->maybe_run(array(
			'order' => $order,
			'user' => $user,
		));
	}

}
