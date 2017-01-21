<?php
/**
 * @class 		AW_Integration_Mailchimp
 * @package		AutomateWoo/Integrations
 * @since		2.3
 */

class AW_Integration_Mailchimp extends AW_Integration
{
	/** @var string */
	public $integration_id = 'mailchimp';

	/** @var string */
	private $api_key;

	/** @var string  */
	private $api_root = 'https://<dc>.api.mailchimp.com/3.0';


	/**
	 * AW_Integration_Mailchimp constructor.
	 * @param $api_key
	 */
	function __construct( $api_key )
	{
		$this->api_key = $api_key;
		list(, $data_center ) = explode( '-', $this->api_key );
		$this->api_root = str_replace( '<dc>', $data_center, $this->api_root );
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
				'Authorization' => 'Basic ' . base64_encode( 'anystring:' . $this->api_key )
			],
			'timeout' => 15,
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



	/**
	 * @return array
	 */
	function get_lists()
	{
		$cache = AW()->cache()->get( 'mailchimp_lists' );

		if ( $cache ) return $cache;

		$request = $this->request( 'GET', '/lists', array(
			'count' => 100,
		));

		$clean_lists = array();

		if ( $request->is_successful() )
		{
			$body = $request->get_body();

			if ( is_array( $body['lists'] ) )
			{
				foreach( $body['lists'] as $list )
				{
					$clean_lists[ $list['id'] ] = $list['name'];
				}
			}
		}

		set_transient( 'mailchimp_lists', $clean_lists, 5 * MINUTE_IN_SECONDS );

		return $clean_lists;
	}

}
