<?php
/**
 * @class       AW_Action_Add_To_Campaign_Monitor
 * @package     AutomateWoo/Actions
 */

class AW_Action_Add_To_Campaign_Monitor extends AW_Action {

	public $name = 'add_to_campaign_monitor';

	public $required_data_items = [ 'user' ];


	function init() {

		$this->title = __( 'Add User to List', 'automatewoo' );
		$this->group = __( 'Campaign Monitor', 'automatewoo' );

		parent::init();
	}


	function load_fields() {

		$api_key = new AW_Field_Text_Input();
		$api_key->set_name( 'api_key' );
		$api_key->set_title( __( 'API Key', 'automatewoo' ) );
		$api_key->set_required( true );
		$api_key->set_description( __( 'You can get your API key from the Account Settings page when logged into your Campaign Monitor account.', 'automatewoo' ) );

		$list_id = new AW_Field_Text_Input();
		$list_id->set_name( 'list_id' );
		$list_id->set_title( __( 'List ID', 'automatewoo' ) );
		$list_id->set_required(true);
		$list_id->set_description( __( 'You find the List ID of a list by heading into any list in your account and clicking the \'change name/type\' link below your list name.', 'automatewoo' ) );

		$resubscribe = new AW_Field_Checkbox();
		$resubscribe->set_name( 'resubscribe' );
		$resubscribe->set_title( __( 'Resubscribe', 'automatewoo' ) );
		$resubscribe->set_description( __( 'If checked the user will be subscribed even if they have already unsubscribed from one of your lists. Use with caution where appropriate.', 'automatewoo' ) );

		$this->add_field($api_key);
		$this->add_field($list_id);
		$this->add_field($resubscribe);
	}


	/**
	 * @return void
	 */
	function run() {

		if ( ! $user = $this->workflow->get_data_item('user') )
			return;

		$api_key = $this->get_option('api_key');
		$list_id = $this->get_option('list_id');

		if ( ! $api_key || ! $list_id )
			return;

		$campaign_monitor = new AW_Integration_Campaign_Monitor( $this->get_option('api_key') );

		$data = [
			'EmailAddress' => $user->user_email,
			'Name' => $user->first_name . ' ' . $user->last_name,
			'Resubscribe' => $this->get_option('resubscribe') ? true : false,
//			'CustomFields' => [
//				[
//					'Key' => 'Customer ID',
//					'Value' => $user->ID
//				]
//			]
		];

		$request = $campaign_monitor->request( 'POST', "/subscribers/$list_id.json", $data );

	}

}
