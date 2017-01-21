<?php
/**
 * @class 		AW_Action_Trigger_Order_Action
 * @package		AutomateWoo/Action
 * @since		2.3
 */

class AW_Action_Trigger_Order_Action extends AW_Action {

	public $name = 'trigger_order_action';

	public $required_data_items = [ 'order' ];


	function init() {
		$this->title = __( 'Trigger Order Action', 'automatewoo' );
		$this->group = __( 'Order', 'automatewoo' );
		$this->description = __( 'Not to be confused with AutomateWoo actions this action can trigger a WooCommerce order action. They are also found in the in the top right of of the order edit view.', 'automatewoo');

		parent::init();
	}


	function load_fields() {

		$action = new AW_Field_Select(true);
		$action->set_name('order_action');
		$action->set_title( __('Order Action', 'automatewoo') );
		$action->set_required();
		$action->set_options( apply_filters( 'woocommerce_order_actions', [
			'regenerate_download_permissions' => __( 'Generate download permissions', 'woocommerce' )
		]));

		$this->add_field($action);
	}


	/**
	 * @return void
	 */
	function run() {

		$action = $this->get_option( 'order_action' );
		$order = $this->workflow->get_data_item('order');

		if ( ! $action || ! $order )
			return;

		$action = aw_clean( $action );

		if ( $action == 'regenerate_download_permissions' ) {
			delete_post_meta( $order->id, '_download_permissions_granted' );
			wc_downloadable_product_permissions( $order->id );
		}
		else {
			if ( ! did_action( 'woocommerce_order_action_' . sanitize_title( $action ) ) ) {
				do_action( 'woocommerce_order_action_' . sanitize_title( $action ), $order );
			}
		}
	}
}