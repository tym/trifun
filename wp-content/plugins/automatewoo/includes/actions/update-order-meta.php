<?php
/**
 * Update Order Meta Action
 *
 * @class       AW_Action_Update_Order_Meta
 * @package     AutomateWoo/Actions
 */

class AW_Action_Update_Order_Meta extends AW_Action {

	public $name = 'update_order_meta';

	public $required_data_items = [ 'order' ];


	function init() {
		$this->title = __( 'Add/Update Order Meta', 'automatewoo' );
		$this->group = __( 'Order', 'automatewoo' );

		// Registers the actions
		parent::init();
	}


	function load_fields() {
		$meta_key = ( new AW_Field_Text_Input() )
			->set_name('meta_key')
			->set_title(__('Meta Key', 'automatewoo'))
			->set_variable_validation()
			->set_required();

		$meta_value = ( new AW_Field_Text_Input() )
			->set_name( 'meta_value' )
			->set_title( __('Meta Value', 'automatewoo') )
			->set_variable_validation();

		$this->add_field($meta_key);
		$this->add_field($meta_value);
	}


	/**
	 * Requires a WC Order object
	 *
	 * @return mixed|void
	 */
	function run() {

		// Do we have an order object?
		if ( ! $order = $this->workflow->get_data_item('order') )
			return;

		$meta_key = $this->get_option('meta_key', true );
		$meta_value = $this->get_option('meta_value', true );

		// Make sure there is a meta key but a value is not required
		if ( $meta_key ) {
			update_post_meta( $order->id, $meta_key, $meta_value );
		}

	}

}
