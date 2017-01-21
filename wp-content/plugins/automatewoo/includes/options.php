<?php
/**
 * WP option wrapper
 *
 * @class AW_Options
 * @version 2.0.2
 *
 * @property string $version
 *
 * @property bool $abandoned_cart_enabled
 * @property string $guest_email_capture_scope (checkout,all,none)
 *
 * @property bool $twilio_integration_enabled
 * @property string $twilio_from
 * @property string $twilio_auth_id
 * @property string $twilio_auth_token
 *
 * @property bool $mailchimp_integration_enabled
 * @property bool $mailchimp_api_key
 *
 * @property bool $active_campaign_integration_enabled
 * @property string $active_campaign_api_url
 * @property string $active_campaign_api_key
 *
 * @property string $url_shortener_enabled
 * @property string $google_api_key
 *
 *
 * @property int $queue_batch_size
 * @property int $conversion_window
 *
 * @property bool $enable_background_system_check
 */

class AW_Options extends AW_Options_API {

	/** @var string */
	public $prefix = 'automatewoo_';


	/**
	 * @var array
	 */
	public $defaults = [

		'abandoned_cart_enabled' => 'yes',
		'guest_email_capture_scope' => 'checkout',

		'twilio_integration_enabled' => 'no',
		'active_campaign_integration_enabled' => false,
		'mailchimp_integration_enabled' => false,
		'url_shortener_enabled' => false,
		'queue_batch_size' => 15,
		'conversion_window' => 14,
		'enable_background_system_check' => true,
	];
}

