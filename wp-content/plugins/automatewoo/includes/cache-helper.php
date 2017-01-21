<?php
/**
 * AutomateWoo Cache Helper
 *
 * Disables for admins and store owners.
 *
 * @class       AW_Cache_Helper
 * @package     AutomateWoo
 * @since       2.1.0
 */

class AW_Cache_Helper {

	/** @var bool */
	public $enabled = true;

	/** @var int (hours) */
	public $default_expiration;


	/**
	 *
	 */
	function __construct() {
		$this->default_expiration = apply_filters( 'automatewoo_cache_default_expiration', 6 );
	}


	/**
	 * @param $key
	 * @param $value
	 * @param bool|int $expiration - In hours. Optional.
	 * @return bool
	 */
	function set( $key, $value, $expiration = false ) {

		if ( ! $this->enabled )
			return false;

		if ( ! $expiration ) $expiration = $this->default_expiration;

		return set_transient( 'aw_cache_' . $key, $value, $expiration * HOUR_IN_SECONDS );
	}


	/**
	 * @param $key
	 * @return bool|mixed
	 */
	function get( $key ) {

		if ( ! $this->enabled )
			return false;

		return get_transient( 'aw_cache_' . $key );
	}


	/**
	 * @param $key
	 */
	function delete( $key ) {
		delete_transient( 'aw_cache_' . $key );
	}
}
