<?php

/**
 * @class 		AW_Url_Shortener
 * @package		AutomateWoo
 * @since 		2.1.9
 */
class AW_Url_Shortener {

	/**
	 * @var string
	 */
	public static $api_base_url = 'https://www.googleapis.com/urlshortener/v1/url';

	/**
	 * @var string
	 */
	public static $api_url;


	/**
	 * Check if shortener is going to work
	 * @return bool
	 */
	public static function check() {
		if ( ! function_exists('curl_init') || ! AW()->options()->url_shortener_enabled || ! AW()->options()->google_api_key ) return false;
		return true;
	}


	/**
	 * @param $url
	 * @return bool
	 */
	public static function process( $url ) {
		if ( ! self::check() ) return false;

		$response = self::send($url);
		return isset( $response['id'] ) ? $response['id'] : false;
	}


	/**
	 * @param $url
	 * @return array|mixed|object
	 */
	private static function send( $url ) {

		self::$api_url = add_query_arg(array(
			'key' => AW()->options()->google_api_key
		), self::$api_base_url );

		self::$api_url = esc_url( self::$api_url );

		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, self::$api_url );
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode( array( 'longUrl' => $url ) ) );
		curl_setopt($ch,CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);
		curl_close($ch);
		return json_decode($result,true);
	}
}