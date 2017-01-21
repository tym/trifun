<?php
/**
 * @class 		AW_System_Check_Database_Tables_Exist
 * @package		AutomateWoo/System Checks
 * @since 		2.3
 */

class AW_System_Check_Database_Tables_Exist extends AW_System_Check {

	/**
	 * AW_System_Check_Cron_Running constructor.
	 */
	function __construct() {
		$this->title = __( 'Database Tables Installed', 'automatewoo' );
		$this->description = __( 'Checks the AutomateWoo custom database tables have been installed.', 'automatewoo' );
		$this->high_priority = true;
	}


	/**
	 * Perform the check
	 */
	function run() {

		global $wpdb;

		$tables = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}automatewoo_%'", ARRAY_N );

		// currently 6 tables in core
		if ( count( $tables ) >= 6 ) {
			return $this->success();
		}

		return $this->error( __( 'Tables could not be installed.', 'automatewoo' ) );
	}

}

return new AW_System_Check_Database_Tables_Exist();
