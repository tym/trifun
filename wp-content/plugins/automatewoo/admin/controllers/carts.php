<?php
/**
 * @class AW_Admin_Controller_Carts
 * @since 2.7.7
 */

class AW_Admin_Controller_Carts extends AW_Admin_Controller_Abstract {

	/** @var string  */
	protected static $nonce_action = 'carts-action';


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
		include_once AW()->admin_path( '/reports/carts.php' );

		$table = new AW_Report_Carts();
		$table->prepare_items();
		$table->nonce_action = self::$nonce_action;

		$sidebar_content = '<p>' .
			__( 'Currently active carts are shown here which includes any cart that has not been cleared at purchase or emptied by its owner. Carts are automatically deleted 45 days after their last update.', 'automatewoo' )
			. '</p>';

		AW()->admin->get_view( 'page-table-with-sidebar', [
			'page' => 'carts',
			'table' => $table,
			'heading' => __( 'Carts', 'automatewoo' ),
			'sidebar_content' => $sidebar_content,
			'messages' => self::get_messages()
		]);
	}



	/**
	 * @param $action
	 */
	private static function action_bulk_edit( $action ) {

		self::verify_nonce_action();

		$ids = aw_clean( aw_request( 'cart_ids' ) );

		if ( empty( $ids ) ) {
			self::$errors[] = __( 'Please select some carts to bulk edit.', 'automatewoo');
			return;
		}

		foreach ( $ids as $id ) {

			$cart = AW()->get_cart( $id );

			if ( ! $cart )
				continue;

			switch ( $action ) {
				case 'delete':
					$cart->delete();
					break;
			}
		}

		self::$messages[] = __( 'Bulk edit completed.', 'automatewoo' );
	}
}