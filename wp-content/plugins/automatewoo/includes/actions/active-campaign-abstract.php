<?php
/**
 * @class 	AW_Action_Active_Campaign_Abstract
 */

abstract class AW_Action_Active_Campaign_Abstract extends AW_Action {

	function init() {
		$this->group = __( 'ActiveCampaign', 'automatewoo' );

		parent::init();
	}


	function check_requirements() {
		if ( ! function_exists('curl_init') ) {
			$this->warning( __( 'Server is missing CURL extension required to use the ActiveCampaign API.', 'automatewoo' ) );
		}
	}


	/**
	 * @return AW_Field_Text_Input
	 */
	function add_contact_email_field() {
		$email = ( new AW_Field_Text_Input() )
			->set_name( 'email' )
			->set_title( __( 'Contact Email', 'automatewoo' ) )
			->set_description( __( 'You can use variables such as {{ user.email }} or {{ guest.email }} here.', 'automatewoo' ) )
			->set_required()
			->set_variable_validation();

		$this->add_field( $email );

		return $email;
	}


	function add_contact_fields() {
		$first_name = ( new AW_Field_Text_Input() )
			->set_name( 'first_name' )
			->set_title( __( 'First Name', 'automatewoo' ) )
			->set_variable_validation();

		$last_name = ( new AW_Field_Text_Input() )
			->set_name( 'last_name' )
			->set_title( __( 'Last Name', 'automatewoo' ) )
			->set_variable_validation();

		$phone = ( new AW_Field_Text_Input() )
			->set_name( 'phone' )
			->set_title( __( 'Phone', 'automatewoo' ) )
			->set_variable_validation();

		$this->add_field( $first_name );
		$this->add_field( $last_name );
		$this->add_field( $phone );
	}


	/**
	 * @return AW_Field_Text_Input
	 */
	function add_tags_field() {
		$tag = ( new AW_Field_Text_Input() )
			->set_name( 'tag' )
			->set_title( __( 'Tags', 'automatewoo' ) )
			->set_description( __( 'Add multiple tags separated by commas. Please note that tags are case-sensitive.', 'automatewoo' ) )
			->set_variable_validation();

		$this->add_field( $tag );
		return $tag;
	}


	/**
	 * Convert tags string to array
	 * @param $tags
	 * @return array|string
	 */
	function parse_tags_field( $tags ) {
		return array_map( 'trim', explode( ',', $tags ) );
	}

}
