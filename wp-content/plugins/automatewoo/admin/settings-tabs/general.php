<?php
/**
 * @class 		AW_Settings_Tab_General
 */

class AW_Settings_Tab_General extends AW_Admin_Settings_Tab_Abstract {

	/** @var string */
	public $prefix = 'automatewoo_';


	function __construct() {
		$this->id = 'general';
		$this->name = __( 'General', 'automatewoo' );
	}


	function load_settings() {

		if ( ! empty( $this->settings ) )
			return;

		$this->section_start( 'automatewoo_general' );

		$this->add_setting( 'abandoned_cart_enabled', [
			'type' => 'checkbox',
			'title' => __( 'Enable Abandoned Cart Tracking', 'automatewoo-referrals' ),
			'autoload' => true
		]);

		$this->add_setting( 'guest_email_capture_scope', [
			'type' => 'select',
			'title' => __( 'Where should guest email capture be enabled?', 'automatewoo' ),
			'tooltip' => __( "Determines which pages have javascript code inserted for email capture. If unsure leave set to Checkout Only.", 'automatewoo' ) . '<br><br>'
				. __("If set to All Pages you can add the CSS class 'automatewoo-capture-guest-email' to enable email capture on custom form fields.", 'automatewoo' ),
			'options' => array(
				'checkout' => __( 'Checkout Only', 'automatewoo' ),
				'all' => __( 'All Pages', 'automatewoo' ),
				'none' => __( 'No Pages', 'automatewoo' ),
			),
		]);

		$this->add_setting( 'queue_batch_size', [
			'type' => 'number',
			'title' => __( 'Queue Batch Size', 'automatewoo' ),
			'desc' => __( 'Sets the maximum number of queued events to process at once. The queue runs every 5 minutes. Default value is 15.', 'automatewoo' ),
		]);

		$this->add_setting( 'conversion_window', [
			'title' => __( 'Conversion Tracking Window', 'automatewoo' ),
			'desc' => __( 'Sets the number of days after a workflow runs that a new order can be considered a conversion. Default value is 14.', 'automatewoo' ),
			'type' => 'number',
		]);

		$this->section_end( 'automatewoo_general' );
	}


	/**
	 * @return array
	 */
	function get_settings() {
		$this->load_settings();
		return $this->settings;
	}

}

return new AW_Settings_Tab_General();
