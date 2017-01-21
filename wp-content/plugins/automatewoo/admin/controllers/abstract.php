<?php
/**
 * @class 		AW_Admin_Controller_Abstract
 * @package		AutomateWoo/Admin/Controllers
 * @since		2.4.5
 */

abstract class AW_Admin_Controller_Abstract {

	/** @var array */
	static $messages = [];

	/** @var array  */
	static $errors = [];

	/** @var string */
	static $default_route = 'list';

	/** @var string  */
	protected static $nonce_action = 'automatewoo-action';


	/**
	 *
	 */
	static function output_messages() {

		if ( sizeof( self::$errors ) > 0 ) {
			foreach ( self::$errors as $error ) {
				echo '<div class="error"><p><strong>' . esc_html( $error ) . '</strong></p></div>';
			}
		}
		elseif ( sizeof( self::$messages ) > 0 ) {
			foreach ( self::$messages as $message ) {
				echo '<div class="updated"><p><strong>' . esc_html( $message ) . '</strong></p></div>';
			}
		}
	}


	/**
	 * @since 2.7.8
	 * @return string
	 */
	static function get_messages() {
		ob_start();
		self::output_messages();
		return ob_get_clean();
	}


	/**
	 *
	 */
	static function get_current_route() {

		if ( $action = aw_clean( aw_request( 'action' ) ) )
			return $action;

		return self::$default_route;
	}


	/**
	 * @return string
	 */
	static function get_current_action() {

		if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] )
			return aw_clean( $_REQUEST['action'] );

		if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] )
			return aw_clean( $_REQUEST['action2'] );

		return self::$default_route;
	}


	/**
	 * Verify nonce
	 */
	protected static function verify_nonce_action() {
		$nonce = aw_clean( aw_request( '_wpnonce' ) );
		if ( ! wp_verify_nonce( $nonce, static::$nonce_action ) ) {
			wp_die( 'Security check failed.' );
		}
	}

}
