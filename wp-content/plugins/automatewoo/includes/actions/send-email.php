<?php
/**
 * Send Email Action
 *
 * @class       AW_Action_Send_Email
 * @package     AutomateWoo/Actions
 */

class AW_Action_Send_Email extends AW_Action {

	public $name = 'send_email';

	public $can_be_previewed = true;


	function init() {

		$this->title = __( 'Send Email', 'automatewoo' );
		$this->group = __( 'Email', 'automatewoo' );

		// Registers the actions
		parent::init();
	}


	function load_fields() {

		$to = ( new AW_Field_Text_Input() )
			->set_name( 'to' )
			->set_title( __( 'To', 'automatewoo' ) )
			->set_description( __( 'Enter emails here or use email variables like {{ user.email }} or {{ guest.email }}. Multiple emails can be used, separated by commas.', 'automatewoo' ) )
			->set_placeholder( __( 'E.g. {{ user.email }}, email@example.org', 'automatewoo' ) )
			->set_variable_validation()
			->set_required();

		$subject = ( new AW_Field_Text_Input() )
			->set_name ('subject' )
			->set_title( __( 'Email Subject', 'automatewoo' ) )
			->set_variable_validation()
			->set_required();

		$heading = ( new AW_Field_Text_Input() )
			->set_name( 'email_heading' )
			->set_title( __('Email Heading', 'automatewoo' ) )
			->set_variable_validation()
			->set_description( __( 'The appearance will depend on your email template. Not all templates support this field.', 'automatewoo' ) );

		$template = ( new AW_Field_Select( false ) )
			->set_name('template')
			->set_title( __('Template', 'automatewoo' ) )
			->set_options( AW()->email->get_email_templates() );

		$email_content = ( new AW_Field_Email_Content() ); // no easy way to define data attributes

		$this->add_field( $to );
		$this->add_field( $subject );
		$this->add_field( $heading );
		$this->add_field( $template );
		$this->add_field( $email_content );
	}


	/**
	 * Generates the HTML content for the email
	 * @param array $send_to
	 * @return string|WP_Error|true
	 */
	function preview( $send_to = [] ) {

		$email_heading = $this->get_option('email_heading', true );
		$email_content = $this->get_option('email_content', true, true );
		$subject = $this->get_option('subject', true );
		$template = $this->get_option( 'template' );

		// use the current user
		$user = get_user_by( 'id', get_current_user_id() );

		// no user object should be present when sending emails
		wp_set_current_user( 0 );

		if ( ! empty( $send_to ) ) {
			foreach ( $send_to as $recipient ) {
				$mailer = new AW_Mailer( $subject, $recipient, $email_content, $template );
				$mailer->user = $user;
				$mailer->set_heading( $email_heading );
				$mailer->set_workflow( $this->workflow );
				$sent = $mailer->send();

				if ( is_wp_error( $sent ) ) {
					return $sent;
				}
			}

			return true;
		}
		else
		{
			$mailer = new AW_Mailer( $subject, $user->get('user_email'), $email_content, $template );
			$mailer->set_heading( $email_heading );
			$mailer->set_workflow( $this->workflow );

			return $mailer->get_html();
		}
	}



	/**
	 * @return void
	 */
	function run() {

		$email_heading = $this->get_option('email_heading', true );
		$email_content = $this->get_option('email_content', true, true );
		$subject = $this->get_option('subject', true );
		$template = $this->get_option( 'template' );

		$to = $this->get_option( 'to', true );
		$to = AW()->email->parse_multi_email_field( $to, false );

		foreach ( $to as $recipient ) {

			$mailer = new AW_Mailer( $subject, $recipient, $email_content, $template );
			$mailer->set_heading( $email_heading );
			$mailer->set_workflow( $this->workflow );
			$sent = $mailer->send();

			if ( is_wp_error( $sent ) ) {
				$this->workflow->add_action_log_note( $this, $sent->get_error_message() );
			}
			else {
				$this->workflow->add_action_log_note( $this, sprintf( __( 'Successfully sent to %s', 'automatewoo'), $recipient ) );
			}
		}
	}


}
