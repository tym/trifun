<?php
/**
 * @class 		AW_Settings_Tab_Active_Campaign
 */

class AW_Settings_Tab_Active_Campaign extends AW_Admin_Settings_Tab_Abstract {

	function __construct() {
		$this->id = 'active-campaign';
		$this->name = __( 'ActiveCampaign', 'automatewoo' );
	}


	/**
	 * @return array
	 */
	function get_settings() {
		return [
			[
				'type' 	=> 'title',
				'id' 	=> 'automatewoo_active_campaign_integration'
			],
			[
				'title'         => __( 'Enable', 'woocommerce' ),
				'id'            => 'automatewoo_active_campaign_integration_enabled',
				'desc' 	=> __( 'Enable ActiveCampaign Integration', 'automatewoo' ),
				'default'       => 'no',
				'autoload' => true,
				'type'          => 'checkbox',
			],
			[
				'title'    => __( 'API URL', 'automatewoo' ),
				'id'       => 'automatewoo_active_campaign_api_url',
				'type'     => 'text',
				'autoload' => false,
			],
			[
				'title'    => __( 'API Key', 'automatewoo' ),
				'id'       => 'automatewoo_active_campaign_api_key',
				'type'     => 'text',
				'autoload' => false,
			],
			[
				'type' 	=> 'sectionend',
				'id' 	=> 'automatewoo_active_campaign_integration'
			],
		];
	}

}

return new AW_Settings_Tab_Active_Campaign();
