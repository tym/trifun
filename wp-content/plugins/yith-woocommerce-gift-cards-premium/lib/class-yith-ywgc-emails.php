<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


if ( ! class_exists( 'YITH_YWGC_Emails' ) ) {

	/**
	 *
	 * @class   YITH_YWGC_Emails
	 * @package Yithemes
	 * @since   1.0.0
	 * @author  Your Inspiration Themes
	 */
	class YITH_YWGC_Emails {
		/**
		 * Single instance of the class
		 *
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since  1.0
		 * @author Lorenzo Giuffrida
		 */
		protected function __construct() {

			/**
			 * Add an email action for sending the digital gift card
			 */
			add_filter( 'woocommerce_email_actions', array( $this, 'add_gift_cards_trigger_action' ) );

			/**
			 * Locate the plugin email templates
			 */
			add_filter( 'woocommerce_locate_core_template', array( $this, 'locate_core_template' ), 10, 3 );

			/**
			 * Add the email used to send digital gift card to woocommerce email tab
			 */
			add_filter( 'woocommerce_email_classes', array( $this, 'add_woocommerce_email_classes' ) );
		}

		/**
		 * Add an email action for sending the digital gift card
		 *
		 * @param array $actions list of current actions
		 *
		 * @return array
		 */
		function add_gift_cards_trigger_action( $actions ) {
			//  Add trigger action for sending digital gift card
			$actions[] = 'ywgc-email-send-gift-card';
			$actions[] = 'ywgc-email-notify-customer';

			return $actions;
		}

		/**
		 * Locate the plugin email templates
		 *
		 * @param $core_file
		 * @param $template
		 * @param $template_base
		 *
		 * @return string
		 */
		public function locate_core_template( $core_file, $template, $template_base ) {
			$custom_template = array(
				'emails/send-gift-card.php',
				'emails/plain/send-gift-card.php',
				'emails/notify-customer.php',
				'emails/plain/notify-customer.php',
			);

			if ( in_array( $template, $custom_template ) ) {
				$core_file = YITH_YWGC_TEMPLATES_DIR . $template;
			}

			return $core_file;
		}


		/**
		 * Add the email used to send digital gift card to woocommerce email tab
		 *
		 * @param string $email_classes current email classes
		 *
		 * @return mixed
		 */
		public function add_woocommerce_email_classes( $email_classes ) {
			// add the email class to the list of email classes that WooCommerce loads
			$email_classes['ywgc-email-send-gift-card']  = include( 'emails/class-yith-ywgc-email-send-gift-card.php' );
			$email_classes['ywgc-email-notify-customer'] = include( 'emails/class-yith-ywgc-email-notify-customer.php' );

			return $email_classes;
		}


		/**
		 * Start the scheduling that let gift cards to be sent on expected date
		 */
		public static function start_gift_cards_scheduling() {
			wp_schedule_event( time(), 'daily', 'ywgc_start_gift_cards_sending' );
		}

		/**
		 * Stop the scheduling that let gift cards to be sent on expected date
		 */
		public static function end_gift_cards_scheduling() {
			wp_clear_scheduled_hook( 'ywgc_start_gift_cards_sending' );
		}
	}
}

YITH_YWGC_Emails::get_instance();