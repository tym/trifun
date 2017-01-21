<?php
/**
 * @class       AW_Action_Active_Campaign_Create_Contact
 * @package     AutomateWoo/Actions
 * @since       2.0.0
 */

class AW_Action_Active_Campaign_Create_Contact extends AW_Action_Active_Campaign_Abstract {

	public $name = 'add_user_to_active_campaign_list';


	function init() {
		$this->title = __( 'Create / Update Contact', 'automatewoo' );
		$this->description = __( 'This trigger can be used to create or update contacts in ActiveCampaign. If an existing contact is found by email then an update will occur otherwise a new contact will be created. When updating a contact any fields left blank will not be updated e.g. if you only want to update the address just select an address and enter an email, all other fields can be left blank.', 'automatewoo' );

		parent::init();
	}


	function load_fields() {
		$list_select = ( new AW_Field_Select() )
			->set_title( __( 'Add to List', 'automatewoo' ) )
			->set_name( 'list' )
			->set_options( AW()->integrations()->activecampaign()->get_lists() )
			->set_description( __( 'Leave blank to add a contact without assigning them to any lists.', 'automatewoo' ) );

		$this->add_contact_email_field();
		$this->add_contact_fields();
		$this->add_field( $list_select );
		$this->add_tags_field()
			->set_title( __( 'Add Tags', 'automatewoo' ) );
	}


	/**
	 * @return void
	 */
	function run() {

		$email = $this->get_option( 'email', true );
		$first_name = $this->get_option( 'first_name', true );
		$last_name = $this->get_option( 'last_name', true );
		$phone = $this->get_option( 'phone', true );
		$list_id = $this->get_option( 'list' );
		$tags = $this->get_option( 'tag', true );

		$contact = [
			'email' => $email,
		];

		if ( $first_name ) $contact['first_name'] = $first_name;
		if ( $last_name ) $contact['last_name'] = $last_name;
		if ( $phone ) $contact['phone'] = $phone;
		if ( $tags ) $contact['tags'] = $tags;

		if ( $list_id ) {
			$contact[ "p[$list_id]" ] = $list_id;
			$contact[ "status[$list_id]" ] = 1;
		}

		$ac = AW()->integrations()->activecampaign();

		$ac->request( 'contact/sync', $contact );
		$ac->clear_contact_transients( $email );
	}

}
