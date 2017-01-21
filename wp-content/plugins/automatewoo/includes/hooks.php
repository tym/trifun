<?php
/**
 * @class 		AW_Hooks
 * @package		AutomateWoo
 * @since		2.6.7
 */

class AW_Hooks {

	/**
	 * Add 'init' actions here means we can load less files at 'init'
	 */
	function __construct() {
		// general
		add_action( 'automatewoo/background_process', [ $this , 'background_process' ], 10, 3 );

		// addons
		add_action( 'automatewoo/addons/activate', [ $this , 'activate_addon' ] );

		// unsubscribes
		add_action( 'user_register', [ $this, 'schedule_unsubscribe_consolidate_user' ] );
		add_action( 'automatewoo/unsubscribe/consolidate_user', [ $this, 'unsubscribe_consolidate_user' ] );
	}


	/**
	 * @param $hook
	 * @param $batch
	 * @param $args
	 */
	function background_process( $hook, $batch, $args ) {
		AW_Background_Process_Handler::handle( $hook, $batch, $args );
	}


	/**
	 * @param $addon_id
	 */
	function activate_addon( $addon_id ) {
		if ( $addon = AW()->addons()->get( $addon_id ) ) {
			$addon->activate();
		}
	}


	/**
	 * @param $user_id
	 */
	function schedule_unsubscribe_consolidate_user( $user_id ) {
		wp_schedule_single_event( time() + 30, 'automatewoo/unsubscribe/consolidate_user', [ $user_id ] );
	}


	/**
	 * @param $user_id
	 */
	function unsubscribe_consolidate_user( $user_id ) {
		AW()->unsubscribes()->consolidate_user( absint( $user_id ) );
	}

}
