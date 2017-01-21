<?php
/**
 * @class 		AW_Admin_Controller_Settings
 * @package		AutomateWoo/Admin
 * @since		2.4.4
 */

class AW_Admin_Controller_Settings extends AW_Admin_Controller_Abstract {

	/** @var array */
	static $settings = [];


	static function output() {
		AW()->admin->get_view( 'page-settings', [
			'current_tab' => self::get_current_tab(),
			'tabs' => self::get_settings_tabs()
		]);
	}


	/**
	 *
	 */
	static function save() {

		// Save settings if data has been posted
		if ( empty( $_POST ) )
			return;

		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'automatewoo-settings' ) ) {
			die( __( 'Action failed. Please refresh the page and retry.', 'automatewoo' ) );
		}

		$current_tab = self::get_current_tab();
		$current_tab->save();
	}


	/**
	 * @return AW_Admin_Settings_Tab_Abstract|false
	 */
	static function get_current_tab() {

		$current_tab_id = empty( $_GET['tab'] ) ? 'general' : sanitize_title( $_GET['tab'] );

		$tabs = self::get_settings_tabs();

		return isset( $tabs[$current_tab_id] ) ? $tabs[$current_tab_id] : false;
	}



	/**
	 * @return array
	 */
	static function get_settings_tabs() {
		if ( empty( self::$settings ) ) {
			$path = AW()->path( '/admin/settings-tabs/' );

			$settings_includes = apply_filters( 'automatewoo/settings/tabs', array(
				$path . 'general.php',
				$path . 'mailchimp.php',
				$path . 'active-campaign.php',
				$path . 'twilio.php',
				$path . 'license.php',
				$path . 'system-check.php'
			));

			include_once $path . 'abstract.php';

			foreach ( $settings_includes as $settings_include )
			{
				$class = include_once $settings_include;
				self::$settings[$class->id] = $class;
			}
		}

		return self::$settings;
	}

}
