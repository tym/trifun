<?php
/**
 * @class 		AW_Settings_Tab_System_Check
 */

class AW_Settings_Tab_System_Check extends AW_Admin_Settings_Tab_Abstract {

	function __construct() {
		$this->id = 'system-check';
		$this->name = __( 'System Check', 'automatewoo' );
	}


	function output() {
		AW()->admin->get_view('system-check');
		$this->output_settings_form();
	}


	/**
	 * @return array
	 */
	function get_settings() {
		return array(

			array(
				'type' 	=> 'title',
				'id' 	=> 'automatewoo_system_check_options'
			),

			array(
				'title'         => __( 'Enable Background Checks', 'woocommerce' ),
				'id'            => 'automatewoo_enable_background_system_check',
				'desc' 	=> __( 'Allow occasional background checks for major system issues. If an issue is detected an admin notice will appear.', 'automatewoo' ),
				'default'       => 'yes',
				'autoload' => true,
				'type'          => 'checkbox',
			),

			array(
				'type' 	=> 'sectionend',
				'id' 	=> 'automatewoo_system_check_options'
			),

		);
	}

}

return new AW_Settings_Tab_System_Check();
