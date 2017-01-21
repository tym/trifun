<?php

/**
 * Class AW_Action_Add_To_Mad_Mimi_List
 */
class AW_Action_Add_To_Mad_Mimi_List extends AW_Action {

	public $name = 'add_to_mad_mimi_list';

	public $required_data_items = [ 'user' ];


	function init() {
		$this->title = __( 'Add User to List', 'automatewoo' );
		$this->group = __( 'Mad Mimi', 'automatewoo' );

		// Registers the actions
		parent::init();
	}


	function check_requirements() {
		if ( ! function_exists('curl_init') ) {
			$this->warning( __('Server is missing CURL extension required to use the MadMimi API.', 'automatewoo' ) );
		}
	}



	function load_fields() {

		$email = new AW_Field_Text_Input();
		$email->set_name('username');
		$email->set_title( __( 'Username (Email)', 'automatewoo' ) );
		$email->set_required(true);

		$api_key = new AW_Field_Text_Input();
		$api_key->set_name('api_key');
		$api_key->set_title( __( 'API Key', 'automatewoo' ) );
		$api_key->set_required(true);
		$api_key->set_description( __( 'You can get your API key from the account section of Mad Mimi.', 'automatewoo' ) );

		$list = new AW_Field_Text_Input();
		$list->set_name('list');
		$list->set_title( __( 'List Name', 'automatewoo' ) );
		$list->set_required(true);

		$this->add_field($email);
		$this->add_field($api_key);
		$this->add_field($list);
	}


	/**
	 * @return void
	 */
	function run() {

		if ( ! $user = $this->workflow->get_data_item('user') )
			return;

		$username = $this->get_option('username');
		$api_key = $this->get_option('api_key');
		$list = $this->get_option('list');

		if ( ! $username || ! $api_key || ! $list )
			return;

		$mad_mimi = new AW_Integration_Mad_Mimi( $username, $api_key );

		$data = [
			'email' => $user->user_email,
			'firstname' => $user->first_name,
			'lastname' => $user->last_name,
			'add_list' => $list
		];

		$csv = $mad_mimi->build_csv( $data );

		$mad_mimi->request( 'POST', '/audience_members', [ 'csv_file' => $csv ] );
	}

}
