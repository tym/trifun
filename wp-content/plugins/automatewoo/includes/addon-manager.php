<?php
/**
 * @class 		AW_Addon_Manager
 * @package		AutomateWoo
 */

class AW_Addon_Manager {

	/** @var array */
	private $registered_addons = [];


	/**
	 * @param $addon AW_Abstract_Addon
	 */
	function register( $addon ) {
		$this->registered_addons[$addon->id] = $addon;
	}


	/**
	 * @return AW_Abstract_Addon[]
	 */
	function get_all() {
		return $this->registered_addons;
	}


	/**
	 * @param $id string
	 * @return AW_Abstract_Addon|false
	 */
	function get( $id ) {
		if ( ! isset( $this->registered_addons[$id] ) )
			return false;

		return $this->registered_addons[$id];
	}


	/**
	 * @return bool
	 */
	function has_addons() {
		return ! empty( $this->registered_addons );
	}

}
