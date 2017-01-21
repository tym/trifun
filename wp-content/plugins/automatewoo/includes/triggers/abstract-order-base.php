<?php
/**
 * @class       AW_Trigger_Abstract_Order_Base
 * @package     AutomateWoo/Triggers
 */

abstract class AW_Trigger_Abstract_Order_Base extends AW_Trigger {

	public $supplied_data_items = [ 'user', 'order', 'shop' ];


	/**
	 * Construct
	 */
	function init() {
		$this->group = __( 'Order', 'automatewoo' );

		parent::init();
	}


	/**
	 * Add options to the trigger
	 */
	function load_fields(){}


	/**
	 * Route hooks through here
	 *
	 * @param $order_id
	 */
	function catch_hooks( $order_id ) {
		$order = wc_get_order( $order_id );
		$user = AW()->order_helper->prepare_user_data_item( $order );

		$this->maybe_run([
			'order' => $order,
			'user' => $user,
		]);
	}


	/**
	 * @param $workflow AW_Model_Workflow
	 *
	 * @return bool
	 */
	function validate_workflow( $workflow ) {
		/**
		 * @var WP_User $user
		 * @var WC_Order $order
		 */
		$user = $workflow->get_data_item('user');
		$order = $workflow->get_data_item('order');

		if ( ! $user || ! $order )
			return false;

		return true;
	}



}
