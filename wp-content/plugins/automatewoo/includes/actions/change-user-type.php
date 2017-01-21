<?php
/**
 * Change User Type Action
 *
 * @class       AW_Action_Change_User_Type
 * @package     AutomateWoo/Actions
 */

class AW_Action_Change_User_Type extends AW_Action {

	public $name = 'change_user_type';

	public $required_data_items = [ 'user' ];


	function init() {

		$this->title = __( 'Change User Type', 'automatewoo' );
		$this->group = __( 'User', 'automatewoo' );

		// Registers the actions
		parent::init();
	}


	function load_fields() {
		$user_type = new AW_Field_User_Type( false );
		$user_type->set_description( __( 'Users will be changed to this', 'automatewoo'  ) );
		$user_type->set_required();

		$this->add_field($user_type);
	}


	/**
	 * @return void
	 */
	function run() {
		if ( $user = $this->workflow->get_data_item('user') ) {
			$user->set_role( $this->get_option('user_type') );
		}
	}

}
