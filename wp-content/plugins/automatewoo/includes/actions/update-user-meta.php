<?php
/**
 * Update User Meta Action
 *
 * @class       AW_Action_Update_User_Meta
 * @package     AutomateWoo/Actions
 */

class AW_Action_Update_User_Meta extends AW_Action {

	public $name = 'update_user_meta';

	public $required_data_items = [ 'user' ];


	function init() {
		$this->title = __( 'Add/Update User Meta', 'automatewoo' );
		$this->group = __( 'User', 'automatewoo' );

		parent::init();
	}


	function load_fields() {

		$meta_key = ( new AW_Field_Text_Input() )
			->set_name( 'meta_key' )
			->set_title( __( 'Meta Key', 'automatewoo' ) )
			->set_required()
			->set_variable_validation();

		$meta_value = ( new AW_Field_Text_Input() )
			->set_name( 'meta_value' )
			->set_title( __( 'Meta Value', 'automatewoo' ) )
			->set_variable_validation();

		$this->add_field( $meta_key );
		$this->add_field( $meta_value );
	}


	function run() {

		if ( ! $user = $this->workflow->get_data_item('user') )
			return;

		$meta_key = $this->get_option('meta_key', true );
		$meta_value = $this->get_option('meta_value', true );

		// Make sure there is a meta key but a value can be blank
		if ( $meta_key ) {
			update_user_meta( $user->ID, $meta_key, $meta_value );
		}
	}

}
