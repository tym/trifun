<?php
/**
 * @class 		AW_Admin_Controller_Tools
 * @package		AutomateWoo/Admin
 * @since		2.4.5
 */

class AW_Admin_Controller_Tools extends AW_Admin_Controller_Abstract {

	/**
	 * Output
	 */
	static function output() {
		$tool_id = aw_clean( aw_request( 'tool_id' ) );

		switch ( self::get_current_route() ) {

			case 'view':
				self::output_view_form( $tool_id );
				break;

			case 'validate':
				if ( self::validate_process( $tool_id ) ) {
					self::output_view_confirm( $tool_id );
				}
				else {
					self::output_view_form( $tool_id );
				}

				break;

			case 'confirm':
				self::confirm_process( $tool_id );
				self::output_view_listing();
				break;

			default:
				self::output_view_listing();
		}

		wp_enqueue_script( 'automatewoo-tools' );
	}


	/**
	 *
	 */
	private static function output_view_listing() {
		AW()->admin->get_view( 'page-tools-list', [
			'tools' => AW()->tools->get_tools()
		]);
	}


	/**
	 * @param $tool_id
	 */
	private static function output_view_form( $tool_id ) {
		$tool = AW()->tools->get_tool( $tool_id );

		AW()->admin->get_view( 'page-tools-form', [ 'tool' => $tool ] );
	}


	/**
	 * @param $tool_id
	 */
	private static function output_view_confirm( $tool_id ) {

		$tool = AW()->tools->get_tool( $tool_id );

		AW()->admin->get_view( 'page-tools-form-confirm', [
			'tool' => $tool,
			'args' => aw_request( 'args' )
		]);
	}


	/**
	 * Return true if init was successful
	 *
	 * @param $tool_id string
	 * @return bool
	 */
	private static function validate_process( $tool_id ) {

		$tool = AW()->tools->get_tool( $tool_id );
		$args = aw_request('args');

		if ( ! $tool )
		{
			wp_die( __( 'Invalid tool.', 'automatewoo' ) );
		}

		$valid = $tool->validate_process( $args );

		if ( $valid === false )
		{
			self::$errors[] = __( 'Failed to init process.', 'automatewoo' );
			return false;
		}
		elseif ( is_wp_error( $valid ) )
		{
			self::$errors[] = $valid->get_error_message();
			return false;
		}
		elseif ( $valid === true )
		{
			return true;
		}
	}


	/**
	 * @param $tool_id
	 */
	private static function confirm_process( $tool_id ) {

		$nonce = aw_clean( aw_request('_wpnonce') );

		if ( ! wp_verify_nonce( $nonce, $tool_id ) ) {
			wp_die( __( 'Security check failed.', 'automatewoo' ) );
		}

		// Process should be valid at this point but just in case
		if ( ! self::validate_process( $tool_id ) ) {
			wp_die( __( 'Process could not be validated.', 'automatewoo' ) );
		}

		$tool = AW()->tools->get_tool( $tool_id );
		$args = aw_request( 'args' );

		$processed = $tool->process( $args );

		if ( $processed === false )
		{
			self::$errors[] = __( 'Process failed.', 'automatewoo' );
		}
		elseif ( is_wp_error( $processed ) )
		{
			self::$errors[] = $processed->get_error_message();
		}
		elseif ( $processed === true )
		{
			self::$messages[] = __( 'Success - Items may be still be processing in the background.', 'automatewoo' );
		}
	}


	/**
	 * @param string|bool $route
	 * @param AW_Tool|bool $tool
	 * @return string
	 */
	static function get_route_url( $route = false, $tool = false ) {

		$base_url = admin_url( 'admin.php?page=automatewoo-tools' );

		if ( ! $route ) {
			return $base_url;
		}

		return add_query_arg([
			'action' => $route,
			'tool_id' => $tool->id
		], $base_url );
	}

}