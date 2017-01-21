<?php
/**
 * @class 		AW_Settings_Tab_License
 */

class AW_Settings_Tab_License extends AW_Admin_Settings_Tab_Abstract {

	function __construct() {
		$this->id = 'license';
		$this->name = __( 'License', 'automatewoo' );
	}


	function output() {
		AW()->licenses->check_for_domain_mismatch();
		AW()->licenses->check_statuses();

		AW()->admin->get_view('settings-licenses');
	}


	/**
	 * Handle deactivation and activation of licenses
	 */
	function save() {

		if ( ! empty( $_POST[ 'deactivate' ] ) ) {

			$product_id = aw_clean( $_POST[ 'deactivate' ] );

			if ( AW()->licenses->is_active( $product_id ) ) {
				AW()->licenses->remote_deactivate( $product_id );
				$this->add_message( __( 'Your license was successfully deactivated.', 'automatewoo' ) );
			}
		}
		elseif ( ! empty( $_REQUEST['submit'] ) ) {

			$license_keys = aw_clean( $_REQUEST['license_keys'] );

			foreach ( $license_keys as $product_id => $license_key ) {
				if ( empty( $license_key ) )
					continue;

				$activate = AW()->licenses->remote_activate( $product_id, $license_key );
				$notice_more = '';

				if ( AW()->licenses->is_primary( $product_id ) ) {
					$product_name = __( 'AutomateWoo', 'automatewoo' );
				}
				else {
					$addon = AW()->addons()->get( $product_id );
					$product_name = $addon->name;

					if ( $start_url = $addon->admin_start_url() ) {
						$notice_more .= ' <a href="' . esc_url( $start_url ) . '">'. __( 'Get started', 'automatewoo' ) .'</a>';
					}
				}

				if ( is_wp_error( $activate ) ) {
					$this->add_error( $product_name . ' - ' . $activate->get_error_message() );
				}
				else {
					$this->add_message( sprintf(__( '%s activated successfully.', 'automatewoo' ),$product_name), $notice_more );

					if ( isset( $addon ) ) {
						// activate the addon on the next request because addon is not initiated right now
						wp_schedule_single_event( time(), 'automatewoo/addons/activate', [ $addon->id ] );
					}
				}
			}
		}
	}
}

return new AW_Settings_Tab_License();
