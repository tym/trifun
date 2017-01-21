<?php
/**
 * @class 		AW_Integration_ActiveCampaign
 * @package		AutomateWoo/Integrations
 * @since		2.6.1
 */

class AW_Integration_ActiveCampaign extends AW_Integration
{
	/** @var string */
	public $integration_id = 'activecampaign';

	/** @var string */
	private $api_key;

	/** @var string */
	private $api_url;

	/** @var ActiveCampaign */
	private $sdk;

	/** @var int */
	public $request_count = 1;


	/**
	 * @param $api_url
	 * @param $api_key
	 */
	function __construct( $api_url, $api_key )
	{
		$this->api_url = $api_url;
		$this->api_key = $api_key;
	}



	/**
	 * @return array
	 */
	function get_lists()
	{
		$cache = get_transient( 'automatewoo_active_campaign_lists' );

		if ( $cache ) return $cache;

		if ( ! $sdk = $this->get_sdk() )
			return [];

		$lists = $sdk->api( 'list/list', [ 'ids' => 'all' ] );
		$clean_lists = [];

		foreach ( $lists as $list )
		{
			if ( is_object($list) )
			{
				$clean_lists[$list->id] = $list->name;
			}
		}

		set_transient('automatewoo_active_campaign_lists', $clean_lists, 10 * MINUTE_IN_SECONDS );

		return $clean_lists;
	}


	/**
	 * @param $email
	 * @return bool
	 */
	function is_contact( $email )
	{
		$cache_key = 'aw_ac_is_contact_' . md5( $email );

		if ( $cache = get_transient( $cache_key ) )
		{
			return $cache === 'yes';
		}

		$contact = $this->request( 'contact/view/email', [
			'email' => $email
		]);

		$is_contact = $contact->success;

		set_transient( $cache_key, $is_contact ? 'yes': 'no', DAY_IN_SECONDS * 30 );

		return $is_contact;
	}


	/**
	 * @param $email
	 */
	function clear_contact_transients( $email )
	{
		delete_transient( 'aw_ac_is_contact_' . md5( $email ) );
	}


	/**
	 * @param $path
	 * @param $data
	 * @return stdClass
	 */
	function request( $path, $data )
	{
		if ( ! $this->get_sdk() )
			return false;

		$this->request_count++;

		// avoid overloading the api
		if ( bcmod( $this->request_count, 4 ) == 0 )
		{
			sleep(2);
		}

		return $this->get_sdk()->api( $path, $data );
	}


	/**
	 * @return ActiveCampaign
	 */
	private function get_sdk()
	{
		if ( ! isset( $this->sdk ) )
		{
			if ( ! class_exists( 'ActiveCampaign' ) )
			{
				require_once AW()->lib_path( '/activecampaign-api-php/includes/ActiveCampaign.class.php' );
			}

			if ( $this->api_url && $this->api_key )
			{
				$this->sdk = new ActiveCampaign( $this->api_url, $this->api_key );
			}
			else
			{
				$this->sdk = false;
			}
		}

		return $this->sdk;
	}

}
