<?php
/**
 * @class 		AW_Settings_Tab_Mailchimp
 */

class AW_Settings_Tab_Mailchimp extends AW_Admin_Settings_Tab_Abstract {

	function __construct() {
		$this->id = 'mailchimp';
		$this->name = __( 'MailChimp', 'automatewoo' );
	}

	/**
	 * @return array
	 */
	function get_settings() {
		return [
			array(
				'type' 	=> 'title',
				'id' 	=> 'automatewoo_mailchimp_integration'
			),

			array(
				'title'         => __( 'Enable', 'woocommerce' ),
				'id'            => 'automatewoo_mailchimp_integration_enabled',
				'desc' 	=> __( 'Enable MailChimp Integration', 'automatewoo' ),
				'default'       => 'no',
				'autoload' => true,
				'type'          => 'checkbox',
			),

			array(
				'title' => __( 'API Key', 'automatewoo' ),
				'id' => 'automatewoo_mailchimp_api_key',
				'tooltip' => __( 'You can get your API key when logged in to MailChimp under Account > Extras > API Keys.', 'automatewoo' ),
				'type' => 'text',
				'autoload' => false,
			),

			array(
				'type' 	=> 'sectionend',
				'id' 	=> 'automatewoo_mailchimp_integration'
			)

		];
	}
}

return new AW_Settings_Tab_Mailchimp();
