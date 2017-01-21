<?php
/**
 * @class AW_Admin_Controller_Logs
 * @since 2.5
 */

class AW_Admin_Controller_Logs extends AW_Admin_Controller_Abstract {

	/** @var string  */
	protected static $nonce_action = 'logs-action';


	static function output() {

		$action = self::get_current_action();

		switch ( $action ) {
			case 'bulk_delete':
				self::action_bulk_edit( str_replace( 'bulk_', '', $action ) );
				self::output_list_table();
				break;

			default:
				self::output_list_table();
				break;
		}
	}


	private static function output_list_table() {

		include_once AW()->admin_path( '/report-list-table.php' );
		include_once AW()->admin_path( '/reports/logs.php' );

		$table = new AW_Report_Logs();
		$table->prepare_items();
		$table->nonce_action = self::$nonce_action;

		$sidebar_content = '<p>' . __( 'Every time a workflow runs a log entry is created. Logs are used by some triggers to determine when they should and should not fire. For this reason deleting logs should generally be avoided.', 'automatewoo' ) . '</p>';

		AW()->admin->get_view( 'page-table-with-sidebar', [
			'page' => 'logs',
			'table' => $table,
			'heading' => __( 'Logs', 'automatewoo' ),
			'sidebar_content' => $sidebar_content,
			'messages' => self::get_messages()
		]);
	}


	/**
	 * @param $action
	 */
	private static function action_bulk_edit( $action ) {

		self::verify_nonce_action();

		$ids = aw_clean( aw_request( 'log_ids' ) );

		if ( empty( $ids ) ) {
			self::$errors[] = __('Please select some logs to bulk edit.', 'automatewoo');
			return;
		}

		foreach ( $ids as $id ) {

			$log = AW()->get_log( $id );

			if ( ! $log )
				continue;

			switch ( $action ) {
				case 'delete':
					$log->delete();
					break;
			}
		}

		self::$messages[] = __('Bulk edit completed.', 'automatewoo');
	}
}