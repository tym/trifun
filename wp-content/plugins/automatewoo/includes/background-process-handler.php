<?php
/**
 * @class 		AW_Background_Process_Handler
 * @package 	AutomateWoo
 * @since		2.6.1
 */

class AW_Background_Process_Handler {

	/**
	 * @param $hook
	 * @param $batch
	 * @param $args
	 */
	static function handle( $hook, $batch, $args ) {
		do_action( $hook, $batch, $args );
	}
}
