<?php
/**
 * MailChimp Subscribe Action
 *
 * @class       AW_Action_MailChimp_Subscribe
 * @package     AutomateWoo/Actions
 * @since       2.0.3
 */

class AW_Action_MailChimp_Subscribe extends AW_Action {

	public $name = 'mailchimp_subscribe';


	function init() {

		$this->title = __('Add List Member', 'automatewoo');
		$this->group = __('MailChimp', 'automatewoo');

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
			//->set_required()
			->set_variable_validation();

		$double_optin = ( new AW_Field_Checkbox() )
			->set_name('double_optin')
			->set_title( __( 'Double Optin', 'automatewoo' ) )
			->set_description( __( 'Users will receive an email asking them to confirm their subscription.', 'automatewoo' ) );

		$this->add_field( $email );
		$this->add_field( $list_select );
		$this->add_field( $double_optin );
	}


	/**
	 * @return void
	 */
	function run() {

		$list_id = $this->get_option('list');
		$email = aw_clean_email( $this->get_option( 'email', true ) );
		$user = $this->workflow->get_data_item('user'); // user object is not required but will be used if present

		if ( ! $list_id )
			return;

		if ( ! $email && $user ) {
			// fallback to user.email for backwards compatibility
			$email = strtolower( $user->user_email );
		}

		$args = [];
		$subscriber_hash = md5( $email );

		$args['email_address'] = $email;
		$args['status'] = $this->get_option('double_optin') ? 'pending' : 'subscribed';

		if ( $user ) {
			$args['merge_fields'] = [
				'FNAME' => $user->first_name,
				'LNAME' => $user->last_name
			];
		}

		AW()->integrations()->mailchimp()->request( 'PUT', "/lists/$list_id/members/$subscriber_hash", $args );
	}

}
