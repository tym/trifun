<?php
/**
 * @class AW_Admin_Controller_Unsubscribes
 * @since 2.7.7
 */

class AW_Admin_Controller_Unsubscribes extends AW_Admin_Controller_Abstract {

	/** @var string  */
	protected static $nonce_action = 'unsubscribes-action';


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
		include_once AW()->admin_path( '/reports/unsubscribes.php' );

		$table = new AW_Report_Unsubscribes();
		$table->prepare_items();
		$table->nonce_action = self::$nonce_action;

		$sidebar_content = '<p>' . sprintf(
			__( 'All emails sent from AutomateWoo workflows automatically include an unsubscribe link in the email footer. This link allows any recipient, both user or guest, to unsubscribe from that specific workflow. <%s>Read more&hellip;<%s>', 'automatewoo' ),
			'a href="' . AW()->website_url . 'docs/unsubscribes/" target="_blank"',
			'/a'
		) . '</p>';

		AW()->admin->get_view( 'page-table-with-sidebar', [
			'page' => 'unsubscribes',
			'table' => $table,
			'heading' => __( 'Unsubscribes', 'automatewoo' ),
			'sidebar_content' => $sidebar_content,
			'messages' => self::get_messages()
		]);
	}


	/**
	 * @param $action
	 */
	private static function action_bulk_edit( $action ) {

		self::verify_nonce_action();

		$ids = aw_clean( aw_request( 'unsubscribe_ids' ) );

		if ( empty( $ids ) ) {
			self::$errors[] = __( 'Please select some unsubscribes to bulk edit.', 'automatewoo');
			return;
		}

		foreach ( $ids as $id ) {

			$unsubscribe = AW()->get_unsubscribe( $id );

			if ( ! $unsubscribe )
				continue;

			switch ( $action ) {
				case 'delete':
					$unsubscribe->delete();
					break;
			}
		}

		self::$messages[] = __( 'Bulk edit completed.', 'automatewoo' );
	}
}