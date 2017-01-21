<?php
/**
 * @class AW_Admin_Controller_Guests
 * @since 2.7.7
 */

class AW_Admin_Controller_Guests extends AW_Admin_Controller_Abstract {

	/** @var string  */
	protected static $nonce_action = 'guests-action';


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
		include_once AW()->admin_path( '/reports/guests.php' );

		$table = new AW_Report_Guests();
		$table->prepare_items();
		$table->nonce_action = self::$nonce_action;

		$sidebar_content = '<p>' .
				__( 'Guests are individuals who have visited your store and their email has been captured. A guest does not necessarily have a currently active cart.', 'automatewoo' )
			 . '</p>';

		AW()->admin->get_view( 'page-table-with-sidebar', [
			'page' => 'guests',
			'table' => $table,
			'heading' => __( 'Guests', 'automatewoo' ),
			'sidebar_content' => $sidebar_content,
			'messages' => self::get_messages()
		]);
	}


	/**
	 * @param $action
	 */
	private static function action_bulk_edit( $action ) {

		self::verify_nonce_action();

		$ids = aw_clean( aw_request( 'guest_ids' ) );

		if ( empty( $ids ) ) {
			self::$errors[] = __( 'Please select some guests to bulk edit.', 'automatewoo' );
			return;
		}

		foreach ( $ids as $id ) {

			$guest = AW()->get_guest( $id );

			if ( ! $guest )
				continue;

			switch ( $action ) {
				case 'delete':
					$guest->delete();
					break;
			}
		}

		self::$messages[] = __( 'Bulk edit completed.', 'automatewoo' );
	}
}