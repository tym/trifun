<?php
/**
 * @class 		AW_Language_Helper
 * @since		2.3
 * @package		AutomateWoo
 */

class AW_Language_Helper {

	/**
	 * AW_Language_Helper constructor.
	 */
	function __construct() {
		if ( AW()->is_request('frontend') ) {
			add_action( 'wp_loaded', [ $this, 'make_language_persistent' ] );
		}
	}


	/**
	 * Make language choice for guests and users persist
	 */
	function make_language_persistent() {

		if ( ! AW()->integrations()->is_wpml() )
			return;

		$current_lang = wpml_get_current_language();

		if ( is_user_logged_in() ) {
			$user_lang = get_user_meta( get_current_user_id(), '_aw_persistent_language', true );

			if ( $user_lang != $current_lang ) {
				$this->set_user_language( get_current_user_id(), $current_lang );
			}
		}
		else {
			// Save language for guest if they have been stored
			$guest = AW()->session_tracker->get_current_guest();

			if ( $guest ) {
				if ( $guest->language != $current_lang ) {
					$this->set_guest_language( $guest, $current_lang );
				}
			}
		}
	}


	/**
	 * @param $user AW_Model_Order_Guest|WP_User
	 * @return string|false
	 */
	function get_user_language( $user ) {

		if ( ! AW()->integrations()->is_wpml() )
			return false;

		if ( $user instanceof WP_User ) {
			if ( $persisted = get_user_meta( $user->ID, '_aw_persistent_language', true ) ) {
				return $persisted;
			}
		}

		// guest orders
		if ( $user instanceof AW_Model_Order_Guest && $user->order  ) {
			if ( $order_lang = get_post_meta( $user->order->id, 'wpml_language', true ) ) {
				return $order_lang;
			}
		}

		return wpml_get_default_language();
	}


	/**
	 * @param $user_id
	 * @param $language
	 */
	function set_user_language( $user_id, $language ) {
		update_user_meta( $user_id, '_aw_persistent_language', $language );
	}


	/**
	 * @param $guest AW_Model_Guest
	 * @return string
	 */
	function get_guest_language( $guest ) {
		if ( ! AW()->integrations()->is_wpml() )
			return false;

		if ( $guest && $guest->language ) {
			return $guest->language;
		}
		return wpml_get_default_language();
	}


	/**
	 * @param AW_Model_Guest $guest
	 * @param $language
	 */
	function set_guest_language( $guest, $language ) {
		if ( $guest && $guest->exists ) {
			$guest->language = $language;
			$guest->save();
		}
	}

}
