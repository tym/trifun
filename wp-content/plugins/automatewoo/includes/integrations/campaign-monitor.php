<?php
/**
 * @class 		AW_Integration_Campaign_Monitor
 * @package		AutomateWoo/Integrations
 * @since		2.7
 */

class AW_Integration_Campaign_Monitor extends AW_Integration
{
	/** @var string */
	public $integration_id = 'campaign-monitor';

	/** @var string */
	private $api_key;

	/** @var string  */
	private $api_root = 'https://api.createsend.com/api/v3.1';


	/**
	 * @param $api_key
	 */
	function __construct( $api_key )
	{
		$this->api_key = $api_key;
	}


	/**
	 * Automatically logs errors
	 *
	 * @param $method
	 * @param $endpoint
	 * @param $args
	 *
	 * @return AW_Remote_Request
	 */
	function request( $method, $endpoint, $args = [] )
	{
		$request_args = [
			'headers' => [
				'Authorization' => 'Basic ' . base64_encode( $this->api_key . ':x' ),
				'Accept' => 'application/json'
			],
			'timeout' => 10,
			'method' => $method,
			'sslverify' => false
		];

		$url = $this->api_root . $endpoint;

		switch ( $method )
		{
			case 'GET':
				$url = add_query_arg( $args, $url );
				break;

			default:
				$request_args['body'] = json_encode( $args );
				break;
		}

		$request = new AW_Remote_Request( $url, $request_args );

		if ( $request->is_failed() )
		{
			$this->log( $request->get_error_message() );
		}
		elseif ( ! $request->is_http_success_code() )
		{
			$this->log(
				$request->get_response_code() . ' ' . $request->get_response_message()
				. '. Method: ' . $method
				. '. Endpoint: ' . $endpoint
				. '. Response body: ' . print_r( $request->get_body(), true ) );
		}

		return $request;
	}


}
