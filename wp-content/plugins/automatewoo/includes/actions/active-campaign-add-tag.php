<?php
/**
 * @class       AW_Action_Active_Campaign_Add_Tag
 * @package     AutomateWoo/Actions
 * @since       2.0.0
 */

class AW_Action_Active_Campaign_Add_Tag extends AW_Action_Active_Campaign_Abstract {

	public $name = 'active_campaign_add_tag';


	function init() {
		$this->title = __( 'Add Tags To Contact', 'automatewoo' );

		parent::init();
	}


	function load_fields() {

		$create_user = ( new AW_Field_Checkbox() )
			->set_name( 'create_missing_contact' )
			->set_title( __( "Create Contact If Missing", 'automatewoo' ) )
			->set_description( __( "The below fields will be used only if the contact needs to be created.", 'automatewoo' ) );

		$this->add_contact_email_field();
		$this->add_tags_field()->set_required();
		$this->add_field( $create_user );
		$this->add_contact_fields();
	}


	/**
	 * @return void
	 */
	function run() {

		$email = $this->get_option( 'email', true );
		$tags = $this->get_option( 'tag',  true );
		$create_missing_contact = $this->get_option( 'create_missing_contact' );

		if ( empty( $tags ) ) return;

		$api = AW()->integrations()->activecampaign();

		if ( ! $api->is_contact( $email ) ) {
			if ( $create_missing_contact ) {
				$first_name = $this->get_option( 'first_name', true );
				$last_name = $this->get_option( 'last_name', true );
				$phone = $this->get_option( 'phone', true );

				$contact = [
					'email' => $email,
					'tags' => $tags
				];

				if ( $first_name ) $contact['first_name'] = $first_name;
				if ( $last_name ) $contact['last_name'] = $last_name;
				if ( $phone ) $contact['phone'] = $phone;

				$api->request( 'contact/sync', $contact );

				$api->clear_contact_transients( $email );
			}
			return;
		}

		$data = [
			'email' => $email,
			'tags' => $this->parse_tags_field( $tags )
		];

		$api->request( 'contact/tag/add', $data);
	}
}
