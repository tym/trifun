<?php
/**
 * @class 		AW_Reports_Tab_Conversions_List
 */

class AW_Reports_Tab_Conversions_List extends AW_Admin_Reports_Tab_Abstract {

	function __construct() {
		$this->id = 'conversions-list';
		$this->name = __( 'Conversions List', 'automatewoo' );
	}


	/**
	 * @return object
	 */
	function get_report_class() {
		include_once AW()->admin_path( '/report-list-table.php' );
		include_once AW()->admin_path( '/reports/conversions-list.php' );

		return new AW_Report_Conversions_List();
	}


	/**
	 * @param $action
	 */
	function handle_actions( $action ) {

		switch ( $action ) {

			case 'bulk_unmark_conversion':

				AW_Admin_Controller_Reports::verify_nonce();

				$ids = aw_clean( aw_request( 'order_ids' ) );

				if ( empty( $ids ) ) {
					AW_Admin_Controller_Reports::$errors[] = __( 'Please select some queued events to bulk edit.', 'automatewoo');
					return;
				}

				foreach ( $ids as $id ) {
					delete_post_meta( $id, '_aw_conversion' );
					delete_post_meta( $id, '_aw_conversion_log' );
				}

				AW_Admin_Controller_Reports::$messages[] = __( 'Bulk edit completed.', 'automatewoo');

				break;
		}
	}

}

return new AW_Reports_Tab_Conversions_List();