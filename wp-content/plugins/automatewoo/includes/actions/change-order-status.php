<?php
/**
 * Change Order Status Action
 *
 * @class       AW_Action_Change_Order_Status
 * @package     AutomateWoo/Actions
 * @since       1.1.4
 */

class AW_Action_Change_Order_Status extends AW_Action {

	public $name = 'change_order_status';

	public $required_data_items = [ 'order' ];


	function init() {
		$this->title = __( 'Change Order Status', 'automatewoo' );
		$this->group = __( 'Order', 'automatewoo' );

		// Registers the actions
		parent::init();
	}


	function load_fields() {
		$order_status = new AW_Field_Order_Status( false );
		$order_status->set_description( __( 'Order status will be changed to this', 'automatewoo' ) );
		$order_status->set_required(true);

		$this->add_field($order_status);
	}


	/**
	 * @return void
	 */
	function run() {
		$order = $this->workflow->get_data_item('order');
		$status = $this->get_option('order_status');

		if ( ! $status || ! $order )
			return;

		$note = sprintf( __('AutomateWoo Workflow: %s.', 'automatewoo'), $this->workflow->title );

		$order->update_status( $status, $note );
	}

}
