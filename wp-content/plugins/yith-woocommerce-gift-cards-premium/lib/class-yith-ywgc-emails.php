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

			/**
			 * Add entry on resend order email list
			 */
			add_filter( 'woocommerce_resend_order_emails_available', array( $this, 'resend_gift_card_code' ) );

			/**
			 * Add the admin email as recipient in BCC for every gift card code sent
			 */
			add_filter( 'ywgc_gift_card_code_email_bcc', array(
				$this,
				'add_admin_to_email_bcc'
			) );

			/**
			 * Add information to the email footer
			 */
			add_action( 'woocommerce_email_footer', array(
				$this,
				'add_footer_information'
			) );

			/**
			 * Add CSS style to gift card emails header
			 */
			add_action( 'woocommerce_email_header', array(
				$this,
				'include_css_for_emails'
			), 10, 2 );

			/**
			 * Show an introductory text before the gift cards editor
			 */
			add_action( 'ywgc_gift_cards_email_before_preview', array(
				$this,
				'show_introductory_text'
			), 10, 2 );

			/**
			 * Add the customer product suggestion is there is one
			 */
			add_action( 'ywgc_gift_card_email_after_preview', array(
				$this,
				'show_email_additional_data'
			) );

			/**
			 * We'll add additional information and link near the product name, but we need to show it
			 * only on view order page, while the same action (woocommerce_order_item_meta_start) is used for emails where we do not want
			 * to show it.
			 * Hack it enabling and disabling the feature using the action/filter available at the moment
			 *
			 * @since WooCommerce 2.5.5
			 *
			 * Remove some action/filter that cause unwanted data to be shown on emails
			 */
			add_filter( 'woocommerce_order_item_quantity_html', array( $this, 'enable_edit_hooks_for_emails' ) );

			/**
			 * Add the previously removed action/filter that cause unwanted data to be shown on emails
			 */
			add_action( 'woocommerce_order_item_meta_end', array( $this, 'disable_edit_hooks_for_emails' ) );
		}

		/**
		 * Add the customer product suggestion is there is one
		 *
		 * @param  YWGC_Gift_Card_Premium $gift_card
		 */
		public function show_email_additional_data( $gift_card ) {
			$this->show_link_for_cart_discount( $gift_card );

			$this->show_product_suggestion( $gift_card );
		}

		/**
		 * Show a link that let the customer to go to the website, adding the discount to the cart
		 *
		 * @param YWGC_Gift_Card_Premium $gift_card
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function show_link_for_cart_discount( $gift_card ) {

			if ( ! YITH_YWGC()->automatic_discount ) {
				return;
			}
			$shop_page_url = get_permalink( wc_get_page_id( 'shop' ) );

			$args = array(
				YWGC_ACTION_ADD_DISCOUNT_TO_CART => $gift_card->gift_card_number,
				YWGC_ACTION_VERIFY_CODE          => YITH_YWGC()->hash_gift_card( $gift_card ),
			);

			$apply_discount_url = esc_url( add_query_arg( $args, $shop_page_url ) );
			?>
			<div class="ywgc-add-cart-discount">
                <span
	                class="ywgc-discount-message"><?php _e( "In order to use this gift card you can enter the gift card code in the appropriate field of the cart page or you can click the following link to obtain the discount automatically.", 'yith-woocommerce-gift-cards' ); ?></span>

				<div class="ywgc-discount-link-section">
					<a class="ywgc-discount-link"
					   href="<?php echo $apply_discount_url; ?>"><?php _e( 'Click here for the discount', 'yith-woocommerce-gift-cards' ); ?></a>
				</div>
			</div>
			<?php
		}

		/**
		 * Remove some action/filter that cause unwanted data to be shown on emails
		 */
		public function disable_edit_hooks_for_emails() {
			remove_action( 'woocommerce_order_item_meta_start', array(
				YITH_YWGC()->frontend,
				'edit_gift_card',
			), 10 );
		}

		/**
		 * Add the previously removed action/filter that cause unwanted data to be shown on emails
		 *
		 * @param string $title the text being shown
		 *
		 * @return string
		 */
		public function enable_edit_hooks_for_emails( $title ) {
			add_action( 'woocommerce_order_item_meta_start', array(
				YITH_YWGC()->frontend,
				'edit_gift_card',
			), 10, 3 );

			return $title;
		}

		/**
		 * Show the product suggestion associated to the gift card
		 *
		 * @param YWGC_Gift_Card_Premium $gift_card
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function show_product_suggestion( $gift_card ) {

			if ( ! $gift_card->product_as_present ) {
				return;
			}

			//  The customer has suggested a product when he bought the gift card
			if ( $gift_card->present_variation_id ) {
				$product = wc_get_product( $gift_card->present_variation_id );
			} else {
				$product = wc_get_product( $gift_card->present_product_id );
			}

			wc_get_template( 'emails/product-suggestion.php',
				array(
					'gift_card' => $gift_card,
					'product'   => $product,
				),
				'',
				YITH_YWGC_TEMPLATES_DIR );
		}

		/**
		 * Show the introductory message on the email being sent
		 *
		 * @param string              $text
		 * @param YITH_YWGC_Gift_Card $gift_card
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function show_introductory_text( $text, $gift_card ) {
			?>
			<p class="center-email"><?php echo apply_filters( 'ywgc_gift_cards_email_before_preview_text', $text, $gift_card ); ?></p>
			<?php
		}


		/**
		 * Add CSS style to gift card emails header
		 */
		public function include_css_for_emails( $email_heading, $email = null ) {
			if ( $email == null ) {
				return;
			}

			if ( ! isset( $email->object ) ) {
				return;
			}

			if ( ! $email->object instanceof YITH_YWGC_Gift_Card ) {
				return;
			}
			?>
			<style type="text/css">
				/* Put your CSS here */
				<?php include(YITH_YWGC_ASSETS_DIR . "/css/ywgc-frontend.css"); ?>

				h2,
				.center-email,
				.ywgc-footer {
					margin: 0 auto;
					margin-bottom: 15px;
					text-align: center;
				}

				.ywgc-product-image {
					float: left;
					display: inline-block;
					max-width: 30%;
					margin-right: 20px;
				}

				.ywgc-suggested-text {
					padding: 0 20px;
					display: inline-block;
					font-weight: 700;
					font-size: 13px;
					color: #484848;
					text-align: center;
					margin-bottom: 20px;
					opacity: 0.8;
				}

				.ywgc-product-description {
					float: left;
					width: 60%;
				}

				.ywgc-product-title {
					display: block;
					font-weight: 700;
					text-transform: uppercase;
					color: #484848;
					margin-bottom: 10px;
					opacity: 0.8;
				}

				.ywgc-product-excerpt {
					margin-bottom: 15px;
				}

				.ywgc-product-link {
					display: inline-block;
					padding: 8px 15px;
					background-color: #afafaf;
					border-radius: 5px;
					color: white;
					text-decoration: none;
					text-transform: uppercase;
					font-size: 13px;
					font-weight: 700;
				}

				div.ywgc-gift-card-content input,
				div.ywgc-gift-card-content textarea,
				div.ywgc-gift-card-content button {
					display: none;
				}

				div.ywgc-gift-card-content fieldset {
					border: none;
				}

				img.ywgc-logo-shop-image {
					max-width: 50px;
				}

				.ywgc-logo-style2 {
					text-align: center;
				}

				.ywgc-add-cart-discount {
					text-align: justify;
					clear: both;
					max-width: 500px;
					margin: 0 auto;
					margin-bottom: 15px;
				}

				.ywgc-add-cart-discount a {
					text-decoration: none;
				}

				.ywgc-discount-link {
					color: white;
					font-weight: 700;
					text-decoration: none;
					background-color: #557da1;
					display: inline-block;
					padding: 8px 15px;
					border-radius: 5px;
					text-transform: uppercase;
					font-size: 13px;
				}

				.ywgc-discount-message {
					display: block;
					margin-bottom: 25px;
					text-align: justify;
				}

				.ywgc-product-suggested {
					background-color: #f0f0f0;
					padding: 30px 0;
					margin-bottom: 30px;
				}

				.ywgc-discount-link-section {
					text-align: center;
					margin-bottom: 30px;
				}

				.ywgc-generated-code {
					font-weight: bold;
					display: block;
					font-size: 18px;
				}

			</style>
			<?php
		}

		/**
		 * Add gift card email to the available email on resend order email feature
		 *
		 * @param array $emails current emails
		 *
		 * @return array
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function resend_gift_card_code( $emails ) {
			$emails[] = 'ywgc-email-send-gift-card';

			return $emails;
		}

		/**
		 * Add the BCC header to the email that send gift card code to the users
		 *
		 * @param string $headers current email headers
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 *
		 * @return string
		 */
		public function add_admin_to_email_bcc( $headers ) {
			//  Check if the option is set
			if ( ! YITH_YWGC()->blind_carbon_copy ) {
				return $headers;
			}

			$recipients = apply_filters( 'yith_ywgc_bcc_additional_recipients', array( get_option( 'admin_email' ) ) );

			return $headers . "\nBcc: " . implode( ',', $recipients ) . "\r\n";
		}

		/**
		 * Append CSS for the email being sent to the customer
		 *
		 * @param WC_Email $email the email content
		 */
		public function add_footer_information( $email = null ) {
			if ( $email == null ) {
				return;
			}

			if ( ! isset( $email->object ) ) {
				return;
			}

			if ( ! $email->object instanceof YITH_YWGC_Gift_Card ) {
				return;
			}

			wc_get_template( 'emails/gift-card-footer.php',
				array(
					'email'     => $email,
					'shop_name' => YITH_YWGC()->shop_name,
				),
				'',
				YITH_YWGC_TEMPLATES_DIR );
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