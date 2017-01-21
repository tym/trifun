<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'YWGC_Gift_Card_Premium' ) ) {
	/**
	 *
	 * @class   YWGC_Gift_Card_Premium
	 * @package Yithemes
	 * @since   1.0.0
	 * @author  Your Inspiration Themes
	 */
	class YWGC_Gift_Card_Premium extends YITH_YWGC_Gift_Card {

		/**
		 * @var bool the gift card has a postdated delivery date
		 */
		public $postdated_delivery = false;

		/**
		 * @var string the expected delivery date
		 */
		public $delivery_date = '';

		/**
		 * @var string the real delivery date
		 */
		public $delivery_send_date = '';

		/**
		 * @var string the recipient for digital gift cards
		 */
		public $recipient = '';

		/**
		 * @var string the sender for digital gift cards
		 */
		public $sender_name = '';

		/**
		 * @var string the sender for digital gift cards
		 */
		public $recipient_name = '';

		/**
		 * @var string the message for digital gift cards
		 */
		public $message = '';

		/**
		 * @var bool the digital gift cards use the default image
		 */
		public $has_custom_design = true;

		/**
		 * @var string the type of design chosen by the user. Could be :
		 *             'default' for standard image
		 *             'custom' for image uploaded by the user
		 *             'template' for template chosen from the desing list
		 */
		public $design_type = 'default';

		/**
		 * @var string the custom image for digital gift cards
		 */
		public $design = null;

		/**
		 * @var bool the product is set as a present
		 */
		public $product_as_present = false;

		/**
		 * @var int the product variation id when the product is used as a present
		 */
		public $present_variation_id = 0;

		/**
		 * @var int the product id used as a present
		 */
		public $present_product_id = 0;

		/**
		 * @var string the currency used when the gift card is created
		 */
		public $currency = '';

		/**
		 * Plugin version that created the gift card
		 */
		public $version = '';

		/**
		 * @var bool the gift card is digital
		 */
		public $is_digital = false;

		/**
		 * @var bool the gift card amount was entered manually
		 */
		public $is_manual_amount = false;


		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @param $args int|array|WP_Post
		 *
		 * @since  1.0
		 * @author Lorenzo Giuffrida
		 */
		public function __construct( $args = array() ) {

			parent::__construct( $args );

			//  If $args is related to an existent gift card, load their data

			if ( $this->ID ) {
				$this->sender_name          = get_post_meta( $this->ID, '_ywgc_sender_name', true );
				$this->recipient_name       = get_post_meta( $this->ID, '_ywgc_recipient_name', true );
				$this->recipient            = get_post_meta( $this->ID, '_ywgc_recipient', true );
				$this->message              = get_post_meta( $this->ID, '_ywgc_message', true );
				$this->currency             = get_post_meta( $this->ID, '_ywgc_currency', true );
				$this->version              = get_post_meta( $this->ID, '_ywgc_version', true );
				$this->postdated_delivery   = get_post_meta( $this->ID, '_ywgc_postdated', true );
				$this->delivery_date        = get_post_meta( $this->ID, '_ywgc_delivery_date', true );
				$this->delivery_send_date   = get_post_meta( $this->ID, '_ywgc_delivery_send_date', true );
				$this->product_as_present   = get_post_meta( $this->ID, '_ywgc_product_as_present', true );
				$this->present_variation_id = get_post_meta( $this->ID, '_ywgc_present_variation_id', true );
				$this->present_product_id   = get_post_meta( $this->ID, '_ywgc_present_product_id', true );
				$this->is_manual_amount     = get_post_meta( $this->ID, '_ywgc_is_manual_amount', true );
				$this->is_digital           = get_post_meta( $this->ID, '_ywgc_is_digital', true );
				$this->has_custom_design    = get_post_meta( $this->ID, '_ywgc_has_custom_design', true );
				$this->design_type          = get_post_meta( $this->ID, '_ywgc_design_type', true );
				$this->design               = get_post_meta( $this->ID, '_ywgc_design', true );
			}
		}

		/**
		 * The gift card product is virtual
		 */
		public function is_virtual() {

			$is_virtual = false;

			$product = wc_get_product( $this->product_id );
			if ( $product ) {
				$is_virtual = $product->is_virtual();
			}

			return $is_virtual;
		}

		/**
		 * Check if the gift card has been sent
		 */
		public function has_been_sent() {
			return $this->delivery_send_date;
		}

		/**
		 * Set the gift card as sent
		 */
		public function set_as_sent() {
			$this->delivery_send_date = current_time( 'Y-m-d', 0 );
			update_post_meta( $this->ID, '_ywgc_delivery_send_date', $this->delivery_send_date );
		}

		/**
		 * Set the gift card as pre-printed i.e. the code is manually entered instead of being auto generated
		 */
		public function set_as_pre_printed() {
			$this->set_status( GIFT_CARD_STATUS_PRE_PRINTED );
			$this->gift_card_number = YWGC_PHYSICAL_PLACEHOLDER;
		}

		/**
		 * Check if the gift card is pre-printed
		 */
		public function is_pre_printed() {

			return GIFT_CARD_STATUS_PRE_PRINTED == $this->status;
		}

		/**
		 * Retrieve if a gift card is enabled
		 *
		 * @return bool
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function is_enabled() {

			return GIFT_CARD_STATUS_ENABLED == $this->status;
		}

		/**
		 * Retrieve if a gift card is disabled
		 *
		 * @return bool
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function is_disabled() {

			return GIFT_CARD_STATUS_DISABLED == $this->status;
		}


		/**
		 * Set the gift card enabled status
		 *
		 * @param bool|false $enabled
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function set_enabled_status( $enabled = false ) {

			$current_status = $this->is_enabled();

			if ( $current_status == $enabled ) {
				return;
			}

			//  If the gift card is dismissed, stop now
			if ( $this->is_dismissed() ) {
				return;
			}

			$this->set_status( $enabled ? 'publish' : GIFT_CARD_STATUS_DISABLED );
		}

		/**
		 * Set the gift card status
		 *
		 * @param string $status
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function set_status( $status ) {

			$this->status = $status;

			if ( $this->ID ) {
				$args = array(
					'ID'          => $this->ID,
					'post_status' => $status,
				);

				wp_update_post( $args );
			}
		}

		/**
		 * Save the current object
		 */
		public function save_data() {
			parent::save_data();

			/**
			 * Save additional data related to the gift card
			 * valid.
			 */
			if ( $this->ID ) {
				update_post_meta( $this->ID, '_ywgc_sender_name', $this->sender_name );
				update_post_meta( $this->ID, '_ywgc_recipient_name', $this->recipient_name );
				update_post_meta( $this->ID, '_ywgc_recipient', $this->recipient );
				update_post_meta( $this->ID, '_ywgc_message', $this->message );
				update_post_meta( $this->ID, '_ywgc_currency', $this->currency );
				update_post_meta( $this->ID, '_ywgc_version', $this->version );

				update_post_meta( $this->ID, '_ywgc_postdated', $this->postdated_delivery );
				if ( $this->postdated_delivery ) {
					update_post_meta( $this->ID, '_ywgc_delivery_date', $this->delivery_date );
					update_post_meta( $this->ID, '_ywgc_delivery_send_date', $this->delivery_send_date );
				}

				update_post_meta( $this->ID, '_ywgc_has_custom_design', $this->has_custom_design );
				update_post_meta( $this->ID, '_ywgc_design_type', $this->design_type );
				update_post_meta( $this->ID, '_ywgc_design', $this->design );

				update_post_meta( $this->ID, '_ywgc_product_as_present', $this->product_as_present );
				if ( $this->product_as_present ) {
					update_post_meta( $this->ID, '_ywgc_present_product_id', $this->present_product_id );
					update_post_meta( $this->ID, '_ywgc_present_variation_id', $this->present_variation_id );
				}

				update_post_meta( $this->ID, '_ywgc_is_manual_amount', $this->is_manual_amount );
				update_post_meta( $this->ID, '_ywgc_is_digital', $this->is_digital );
				update_post_meta( $this->ID, '_ywgc_status', $this->status );
			}
		}

		/**
		 * The gift card is nulled and no more usable
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function set_dismissed_status() {
			$this->set_status( GIFT_CARD_STATUS_DISMISSED );
		}

		/**
		 * Check if the gift card is dismissed
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function is_dismissed() {

			return GIFT_CARD_STATUS_DISMISSED == $this->status;
		}

		/**
		 * Clone the current gift card using the remaining balance as new amount
		 *
		 * @param string $new_code the code to be used for the new gift card
		 *
		 * @return YWGC_Gift_Card_Premium
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function clone_gift_card( $new_code = '' ) {

			$new_gift = new YWGC_Gift_Card_Premium();

			$new_gift->product_id           = $this->product_id;
			$new_gift->order_id             = $this->order_id;
			$new_gift->sender_name          = $this->sender_name;
			$new_gift->recipient_name       = $this->recipient_name;
			$new_gift->recipient            = $this->recipient;
			$new_gift->message              = $this->message;
			$new_gift->postdated_delivery   = $this->postdated_delivery;
			$new_gift->delivery_date        = $this->delivery_date;
			$new_gift->delivery_send_date   = $this->delivery_send_date;
			$new_gift->has_custom_design    = $this->has_custom_design;
			$new_gift->design_type          = $this->design_type;
			$new_gift->design               = $this->design;
			$new_gift->product_as_present   = $this->product_as_present;
			$new_gift->present_variation_id = $this->present_variation_id;
			$new_gift->present_product_id   = $this->present_product_id;
			$new_gift->currency             = $this->currency;
			$new_gift->status               = $this->status;

			$new_gift->gift_card_number = $new_code;

			//  Set the amount of the cloned gift card equal to the balance of the old one
			$new_gift->set_amount( $this->balance, $this->balance_tax );

			return $new_gift;
		}
	}
}