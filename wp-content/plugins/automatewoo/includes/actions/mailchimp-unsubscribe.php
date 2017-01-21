<?php
/**
 * MailChimp Unsubscribe Action
 *
 * @class       AW_Action_MailChimp_Unsubscribe
 * @package     AutomateWoo/Actions
 * @since       2.0.3
 */

class AW_Action_MailChimp_Unsubscribe extends AW_Action {

	public $name = 'mailchimp_unsubscribe';


	function init() {
		$this->title = __( 'Remove List Member', 'automatewoo' );
		$this->group = __( 'MailChimp', 'automatewoo' );

		// Registers the actions
		parent::init();
	}


	function load_fields() {

		$list_select = ( new AW_Field_Select() )
			->set_title( __( 'List', 'automatewoo' ) )
			->set_name('list')
			->set_options( AW()->integrations()->mailchimp()->get_lists() )
			->set_required();

		$email = ( new AW_Field_Text_Input() )
			->set_name( 'email' )
			->set_title( __( 'Member Email', 'automatewoo' ) )
			->set_description( __( 'You can use variables such as user.email or guest.email here. If blank user.email will be used.', 'automatewoo' ) )
			->set_variable_validation()
			->set_required();

		$unsubscribe_only = new AW_Field_Checkbox();
		$unsubscribe_only->set_name('unsubscribe_only');
		$unsubscribe_only->set_title( __( 'Unsubscribe Only', 'automatewoo' ) );
		$unsubscribe_only->set_description( __( 'If checked the user will be unsubscribed instead of deleted.', 'automatewoo' ) );

		$this->add_field( $list_select );
		$this->add_field( $email);
		$this->add_field( $unsubscribe_only );
	}


	/**
	 * @return void
	 */
	function run() {

		$list_id = $this->get_option('list');
		$email = aw_clean_email( $this->get_option( 'email', true ) );

		if ( ! $list_id )
			return;

		// fallback to user.email for backwards compatibility
		if ( ! $email && $user = $this->workflow->get_data_item('user') ) {
			$email = strtolower( $user->user_email );
		}

		$subscriber = md5( $email );

		if ( $this->get_option('unsubscribe_only') ) {
			AW()->integrations()->mailchimp()->request( 'PATCH', "/lists/$list_id/members/$subscriber", [
				'status' => 'unsubscribed',
			]);
		}
		else {
			AW()->integrations()->mailchimp()->request( 'DELETE', "/lists/$list_id/members/$subscriber" );
		}
	}

}
