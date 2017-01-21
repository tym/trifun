<?php
/**
 * @class 		AW_Admin_Controller_Reports
 * @package		AutomateWoo/Admin
 * @since		2.4.4
 */

class AW_Admin_Controller_Reports extends AW_Admin_Controller_Abstract {

	/** @var array */
	private static $reports = [];


	static function output() {
		self::handle_actions();
		self::output_list_table();
	}


	static function output_list_table() {
		AW()->admin->get_view( 'page-reports', [
			'current_tab' => self::get_current_tab(),
			'tabs' => self::get_reports_tabs()
		]);
	}


	static function handle_actions() {
		$current_tab = self::get_current_tab();
		$current_tab->handle_actions( self::get_current_action() );
	}



	static function verify_nonce() {

		$current_tab = self::get_current_tab();

		$nonce = aw_clean( aw_request( '_wpnonce' ) );

		if ( ! wp_verify_nonce( $nonce, $current_tab->id . '-action' ) )
			wp_die( 'Security check failed.' );
	}



	/**
	 * @return AW_Admin_Reports_Tab_Abstract|false
	 */
	static function get_current_tab() {

		$tabs = self::get_reports_tabs();

		$current_tab_id = empty( $_GET['tab'] ) ? current($tabs)->id : sanitize_title( $_GET['tab'] );

		return isset( $tabs[$current_tab_id] ) ? $tabs[$current_tab_id] : false;
	}


	/**
	 * @return array
	 */
	static function get_reports_tabs() {

		if ( empty( self::$reports ) ) {
			$path = AW()->path( '/admin/reports-tabs/' );

			$report_includes = [];

			$report_includes[] = $path . 'runs-by-date.php';
			$report_includes[] = $path . 'email-tracking.php';
			$report_includes[] = $path . 'conversions.php';
			$report_includes[] = $path . 'conversions-list.php';

			$report_includes = apply_filters( 'automatewoo/reports/tabs', $report_includes );

			include_once $path . 'abstract.php';

			foreach ( $report_includes as $report_include ) {
				$class = include_once $report_include;
				self::$reports[$class->id] = $class;
			}
		}

		return self::$reports;
	}

}
