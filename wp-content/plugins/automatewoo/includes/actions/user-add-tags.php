<?php
/**
 * Add User Tags Action
 *
 * @class       AW_Action_Add_User_Tag
 * @package     AutomateWoo/Actions
 * @since       2.0.0
 */

class AW_Action_User_Add_Tags extends AW_Action {

	public $name = 'user_add_tags';

	public $required_data_items = [ 'user' ];


	function init() {
		$this->title = __('Add Tags to User', 'automatewoo');
		$this->group = __('User', 'automatewoo');

		parent::init();
	}


	function load_fields() {

		$tags = new AW_Field_User_Tags();

		$this->add_field($tags);
	}


	/**
	 */
	function run() {

		if ( ! $user = $this->workflow->get_data_item('user') )
			return;

		$tags = $this->get_option( 'user_tags' );

		if ( ! $user->ID || empty($tags) )
			return;

		wp_add_object_terms( $user->ID, $tags, 'user_tag' );
	}

}
