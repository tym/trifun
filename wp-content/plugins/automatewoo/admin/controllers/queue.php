<?php
/**
 * @class AW_Admin_Controller_Queue
 * @since 2.7.7
 */

class AW_Admin_Controller_Queue extends AW_Admin_Controller_Abstract {

	/** @var string  */
	protected static $nonce_action = 'queue-action';


	static function output() {

		$action = self::get_current_action();

		switch ( $action ) {

			case 'run_now':
				self::action_run_now();
				self::output_list_table();
				break;

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
		include_once AW()->admin_path( '/reports/queue.php' );

		$table = new AW_Report_Queue();
		$table->prepare_items();
		$table->nonce_action = self::$nonce_action;

		$sidebar_content = '<p>' . sprintf(
			__( 'Workflows that are not set to run immediately will be added to this queue. The queue processes %s items every 5 minutes so run times will vary slightly. The batch size can be changed in settings. <%s>Read more&hellip;<%s>', 'automatewoo' ),
			AW()->options()->queue_batch_size,
			'a href="' . AW()->website_url . 'docs/queue/" target="_blank"',
			'/a'
		) . '</p>';

		AW()->admin->get_view( 'page-table-with-sidebar', [
			'page' => 'queue',
			'table' => $table,
			'heading' => __( 'Queue', 'automatewoo' ),
			'sidebar_content' => $sidebar_content,
			'messages' => self::get_messages()
		]);
	}


	/**
	 * Run a single queued event
	 */
	private static function action_run_now() {

		self::verify_nonce_action();

		$queued_event = AW()->get_queued_event( absint( aw_request( 'queued_event_id' ) ) );

		if ( ! $queued_event )
			return;

		if ( $queued_event->run() ) {
			self::$messages[] = __( 'Queued Event Run', 'automatewoo' );
		}
		else {
			self::$errors[] = __( 'Queued event could not be run.', 'automatewoo');
		}
	}


	/**
	 * @param $action
	 */
	private static function action_bulk_edit( $action ) {

		self::verify_nonce_action();

		$ids = aw_clean( aw_request( 'queued_event_ids' ) );

		if ( empty( $ids ) ) {
			self::$errors[] = __( 'Please select some queued events to bulk edit.', 'automatewoo');
			return;
		}

		foreach ( $ids as $id ) {

			$queued_event = AW()->get_queued_event( $id );

			if ( ! $queued_event )
				continue;

			switch ( $action ) {
				case 'delete':
					$queued_event->delete();
					break;
			}
		}

		self::$messages[] = __( 'Bulk edit completed.', 'automatewoo' );
	}
}