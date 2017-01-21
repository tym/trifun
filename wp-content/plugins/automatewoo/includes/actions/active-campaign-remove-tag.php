<?php
/**
 * @class       AW_Action_Active_Campaign_Remove_Tag
 * @package     AutomateWoo/Actions
 * @since       2.0.0
 */

class AW_Action_Active_Campaign_Remove_Tag extends AW_Action_Active_Campaign_Abstract {

	public $name = 'active_campaign_remove_tag';


	function init() {
		$this->title = __( 'Remove Tags From Contact', 'automatewoo' );
		parent::init();
	}


	function load_fields() {
		$this->add_contact_email_field();
		$this->add_tags_field()->set_required();
	}


	/**
	 * @return void
	 */
	function run() {

		$email = $this->get_option( 'email', true );
		$tags = $this->get_option( 'tag',  true );

		if ( empty( $tags ) ) return;

		$data = [
			'email' => $email,
			'tags' => $this->parse_tags_field( $tags )
		];

		AW()->integrations()->activecampaign()->request( 'contact/tag/remove', $data );
	}

}
