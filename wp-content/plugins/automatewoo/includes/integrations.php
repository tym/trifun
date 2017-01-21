<?php
/**
 * @class AW_Integrations
 */

class AW_Integrations {

	/** @var AW_Integration_Mailchimp */
	private $mailchimp;

	/** @var AW_Integration_ActiveCampaign */
	private $activecampaign;

	/** @var AW_Integration_Campaign_Monitor */
	private $campaign_monitor;


	/**
	 * @return bool
	 */
	function is_wpml() {
		return class_exists('SitePress');
	}


	/**
	 * @return bool
	 */
	function is_woo_pos() {
		return class_exists('WC_POS');
	}


	/**
	 * @return bool
	 */
	function subscriptions_enabled() {
		if ( ! class_exists( 'WC_Subscriptions' ) ) return false;
		if ( WC_Subscriptions::$version < '2.0.0' ) return false;
		return true;
	}


	/**
	 * @return bool
	 */
	function is_memberships_enabled() {
		if ( ! function_exists( 'wc_memberships' ) ) return false;
		return true;
	}



	function load_twilio() {
		if ( ! function_exists( 'Services_Twilio_autoload' ) ) {
			require_once AW()->lib_path( '/twilio-php/Services/Twilio.php' );
		}
	}


	/**
	 * @return AW_Integration_Mailchimp
	 */
	function mailchimp() {
		if ( ! isset( $this->mailchimp ) ) {
			$this->mailchimp = new AW_Integration_Mailchimp( AW()->options()->mailchimp_api_key );
		}

		return $this->mailchimp;
	}


	/**
	 * @return AW_Integration_ActiveCampaign
	 */
	function activecampaign() {
		if ( ! isset( $this->activecampaign ) ) {
			$api_url = AW()->options()->active_campaign_api_url;
			$api_key = AW()->options()->active_campaign_api_key;

			$this->activecampaign = new AW_Integration_ActiveCampaign( $api_url, $api_key );
		}

		return $this->activecampaign;
	}


	/**
	 * @return AW_Integration_Campaign_Monitor
	 */
//	function campaign_monitor()
//	{
//		if ( ! isset( $this->campaign_monitor ) )
//		{
//			$this->campaign_monitor = new AW_Integration_Campaign_Monitor( '...apikey' );
//		}
//
//		return $this->campaign_monitor;
//	}


}


