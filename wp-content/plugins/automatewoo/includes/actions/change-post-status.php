<?php
/**
 * Change Post Status Action
 *
 * @class       AW_Action_Change_Post_Status
 * @package     AutomateWoo/Actions
 * @since       2.0.0
 */

class AW_Action_Change_Post_Status extends AW_Action {

	public $name = 'change_post_status';

	public $required_data_items = [ 'post' ];


	function init() {
		$this->title = __( 'Change Post Status', 'automatewoo' );
		$this->group = __( 'Other', 'automatewoo' );

		// Registers the actions
		parent::init();
	}


	function load_fields() {
		$post_status = new AW_Field_Select( false );
		$post_status->set_name('post_status');
		$post_status->set_title(__('Post Status', 'automatewoo') );
		$post_status->set_options( get_post_statuses() );
		$post_status->set_required();

		$this->add_field($post_status);
	}


	/**
	 * @return void
	 */
	function run() {
		$post = $this->workflow->get_data_item('post');
		$status = $this->get_option('post_status');

		if ( ! $status || ! $post )
			return;

		wp_update_post([
			'ID' => $post->ID,
			'post_status' => $status
		]);
	}

}
