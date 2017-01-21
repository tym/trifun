<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 *
 *
 * @class      YITH_SMS_Notifications_Support
 * @package    Yithemes
 * @since      Version 1.9.14
 * @author     Your Inspiration Themes
 *
 */
if ( ! class_exists( 'YITH_SMS_Notifications_Support' ) ) {

	/**
	 * YITH_SMS_Notifications_Support Class
	 */
	class YITH_SMS_Notifications_Support {

		/**
		 * Main instance
		 */
		private static $_instance = null;

		/**
		 * Construct
		 */
		public function __construct() {
			//add_filter( 'ywsn_allow_sms_sending', array( $this, 'prevent_sending_sms_for_suborders' ), 10, 3 );
		}

		/**
		 * Disable GeoDirectory "Prevent admin access" for vendor
		 *
		 * @return boolean
		 *
		 * @since  1.9.14
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function prevent_sending_sms_for_suborders( $check, $order_id, $order ) {


			if ( wp_get_post_parent_id( $order_id ) != 0 ) {
				$vendor = yith_get_vendor( $order->post->post_author, 'user' );
				if ( $vendor->is_valid() ) {
					//prevent sms sending
					$check = false;
				}
			}

			return $check;
		}


		/**
		 * Main plugin Instance
		 *
		 * @static
		 * @return YITH_Vendor_Vacation Main instance
		 *
		 * @since  1.9.14
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}
	}
}

/**
 * Main instance of plugin
 *
 * @return /YITH_SMS_Notifications_Support
 * @since  1.9.14
 * @author Andrea Grillo <andrea.grillo@yithemes.com>
 */
if ( ! function_exists( 'YITH_SMS_Notifications_Support' ) ) {
	function YITH_SMS_Notifications_Support() {
		return YITH_SMS_Notifications_Support::instance();
	}
}

YITH_SMS_Notifications_Support();
