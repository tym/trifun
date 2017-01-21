<?php
/**
 * @class 		AW_System_Check_Cron_Running
 * @package		AutomateWoo/System Checks
 * @since 		2.3
 */

class AW_System_Check_Cron_Running extends AW_System_Check {

	/**
	 * AW_System_Check_Cron_Running constructor.
	 */
	function __construct() {
		$this->title = __( 'WP Cron', 'automatewoo' );
		$this->description = __( 'Checks the dates of scheduled events to see if they are processing.', 'automatewoo' );
		$this->high_priority = true;
	}


	/**
	 * Perform the check
	 */
	function run() {

		$failed = 0;
		$cron = _get_cron_array();
		$thirty_minutes_ago = current_time( 'timestamp', true ) - 1800;

		foreach ( $cron as $time => $item ) {
			if ( $thirty_minutes_ago > $time ) {
				$failed++;
			}
		}

		if ( $failed > 2 ) {
			return $this->error( __( "WP Cron does not appear to be running. This function is heavily relied upon by AutomateWoo. Please contact your hosting provider to resolve the issue.", 'automatewoo' ) );
		}

		return $this->success();
	}

}

return new AW_System_Check_Cron_Running();