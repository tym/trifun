<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'YITH_YWGC_Cart_Checkout' ) ) {

	/**
	 *
	 * @class   YITH_YWGC_Cart_Checkout
	 * @package Yithemes
	 * @since   1.0.0
	 * @author  Your Inspiration Themes
	 */
	class YITH_YWGC_Cart_Checkout {

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

			$this->includes();
			$this->init_hooks();
		}

		public function includes() {

		}

		public function init_hooks() {

			/**
			 * set the price when a gift card product is added to the cart
			 */
			add_filter( 'woocommerce_add_cart_item', array(
				$this,
				'set_price_in_cart'
			), 10, 2 );

			add_filter( 'woocommerce_get_cart_item_from_session', array(
				$this,
				'get_cart_item_from_session'
			), 10, 2 );

			/**
			 *  Let the user to edit che gift card content
			 */
			add_action( 'wp_ajax_edit_gift_card', array(
				$this,
				'edit_gift_card_callback'
			) );

			add_action( 'woocommerce_add_order_item_meta', array(
				$this,
				'append_gift_card_data_to_order_item'
			), 10, 3 );


			/**
			 * Custom add_to_cart handler for gift card product type
			 */
			add_action( 'woocommerce_add_to_cart_handler_gift-card', array(
				$this,
				'add_to_cart_handler'
			) );
		}

		/**
		 * Build cart item meta to pass to add_to_cart when adding a gift card to the cart
		 * @since 1.5.0
		 */
		public function build_cart_item_data() {

//			error_log( 'build_cart_item_data' );
//			error_log( print_r( $_REQUEST, true ) );
			$cart_item_data = array();

			$product_as_present = isset( $_POST["ywgc-as-present"] ) && ( 1 == $_POST["ywgc-as-present"] );

			/**
			 * Check if the current gift card has a manually entered amount set
			 */
			$ywgc_is_manual = isset( $_REQUEST['ywgc-manual-amount'] ) && ( floatval( $_REQUEST['ywgc-manual-amount'] ) > 0 ) &&
			                  ( ! isset( $_REQUEST['gift_amounts'] ) || ( "-1" == $_REQUEST['gift_amounts'] ) );

			/**
			 * Check if the current gift card has a prefixed amount set
			 */
			$ywgc_is_preset_amount = ! $ywgc_is_manual && isset( $_REQUEST['gift_amounts'] ) && ( floatval( $_REQUEST['gift_amounts'] ) > 0 );

			/**
			 * Neither manual or fixed? Something wrong happened!
			 */
			if ( ! $product_as_present && ! $ywgc_is_manual && ! $ywgc_is_preset_amount ) {
				wp_die( __( 'The gift card has invalid amount', 'yith-woocommerce-gift-cards' ) );
			}

			/**
			 * Check if it is a digital gift card
			 */
			$ywgc_is_digital = isset( $_REQUEST['ywgc-is-digital'] ) && $_REQUEST['ywgc-is-digital'];
			if ( $ywgc_is_digital ) {

				/**
				 * Retrieve gift card recipient
				 */
				$recipients = isset( $_REQUEST['ywgc-recipient-email'] ) ? $_REQUEST['ywgc-recipient-email'] : '';

				/**
				 * Retrieve sender name
				 */
				$sender_name = isset( $_REQUEST['ywgc-sender-name'] ) ? $_REQUEST['ywgc-sender-name'] : '';

				/**
				 * Recipient name
				 */
				$recipient_name = isset( $_REQUEST['ywgc-recipient-name'] ) ? $_REQUEST['ywgc-recipient-name'] : '';

				/**
				 * Retrieve the sender message
				 */
				$sender_message = isset( $_REQUEST['ywgc-edit-message'] ) ? $_REQUEST['ywgc-edit-message'] : '';

				/**
				 * Gift card should be delivered on a specific date?
				 */
				$postdated = isset( $_REQUEST['ywgc-postdated'] ) ? true : false;
				if ( $postdated ) {
					/**
					 * Retrieve the facultative delivery date
					 */
					//todo use the web site date format
					$delivery_date = isset( $_REQUEST['ywgc-delivery-date'] ) ? $_REQUEST['ywgc-delivery-date'] : '';
				}

				$gift_card_design = - 1;
				$design_type      = isset( $_POST['ywgc-design-type'] ) ? $_POST['ywgc-design-type'] : 'default';

				if ( 'custom' == $design_type ) {
					/**
					 * The user has uploaded a file
					 */
					if ( isset( $_FILES["ywgc-upload-picture"] ) ) {
						$custom_image = $_FILES["ywgc-upload-picture"];
						if ( isset( $custom_image["tmp_name"] ) && ( 0 == $custom_image["error"] ) ) {
							$gift_card_design = $this->save_uploaded_file( $custom_image );
						}
					}
				} else if ( 'template' == $design_type ) {
					if ( isset( $_POST['ywgc-template-design'] ) && is_numeric( $_POST['ywgc-template-design'] ) ) {
						$gift_card_design = intval( $_POST['ywgc-template-design'] );
					}

				}
			}

			if ( $product_as_present ) {
				$cart_item_data['ywgc_product_id'] = YITH_YWGC()->default_gift_card_id;

				$present_product_id   = $_POST["add-to-cart"];
				$present_variation_id = 0;

				if ( isset( $_POST["variation_id"] ) ) {
					$present_variation_id = $_POST["variation_id"];
				}

				$product = $present_variation_id ? new WC_Product( $present_variation_id ) : new WC_Product( $present_product_id );

				$ywgc_amount = $product->get_price();

				$cart_item_data['ywgc_product_as_present']   = $product_as_present;
				$cart_item_data['ywgc_present_product_id']   = $present_product_id;
				$cart_item_data['ywgc_present_variation_id'] = $present_variation_id;

			} else {
				$cart_item_data['ywgc_product_id'] = absint( $_POST['add-to-cart'] );

				/**
				 * Set the gift card amount
				 */
				$ywgc_amount = $ywgc_is_manual ?
					number_format( (float) $_REQUEST['ywgc-manual-amount'], wc_get_price_decimals(), '.', '' )  :
					$_REQUEST['gift_amounts'];
			}


			$cart_item_data['ywgc_amount']           = apply_filters( 'yith_ywgc_submitting_manual_amount', $ywgc_amount);
			$cart_item_data['ywgc_is_manual_amount'] = $ywgc_is_manual;
			$cart_item_data['ywgc_is_digital']       = $ywgc_is_digital;

			/**
			 * Retrieve the gift card recipient, if digital
			 */
			if ( $ywgc_is_digital ) {
				$cart_item_data['ywgc_recipients']     = $recipients;
				$cart_item_data['ywgc_sender_name']    = $sender_name;
				$cart_item_data['ywgc_recipient_name'] = $recipient_name;
				$cart_item_data['ywgc_message']        = $sender_message;
				$cart_item_data['ywgc_postdated']      = $postdated;

				if ( $postdated ) {
					$cart_item_data['ywgc_delivery_date'] = $delivery_date;
				}


				$cart_item_data['ywgc_design_type']       = $design_type;
				$cart_item_data['ywgc_has_custom_design'] = $gift_card_design != false;
				if ( $gift_card_design ) {
					$cart_item_data['ywgc_design'] = $gift_card_design;
				}

			}

			return $cart_item_data;
		}

		/**
		 * Custom add_to_cart handler for gift card product type
		 */
		public function add_to_cart_handler() {

			$quantity = isset( $_REQUEST['quantity'] ) ? intval( $_REQUEST['quantity'] ) : 1;

			$item_data  = $this->build_cart_item_data();
			$product_id = $item_data['ywgc_product_id'];

			if ( ! $product_id ) {
				wc_add_notice( __( 'An error occurred while adding the product to the cart.', 'yith-woocommerce-gift-cards' ), 'error' );

				return false;
			}

			if ( $item_data['ywgc_is_digital'] ) {
				/**
				 * Check if all mandatory fields are filled or throw an error
				 */
				if ( YITH_YWGC()->mandatory_recipient ) {
					if ( ! count( $item_data['ywgc_recipients'] ) ) {
						wc_add_notice( __( 'Add a valid email for the recipient', 'yith-woocommerce-gift-cards' ), 'error' );

						return false;
					}
				}

				/** The user can purchase 1 gift card with multiple recipient emails or [quantity] gift card for the same user.
				 * It's not possible to mix both, purchasing multiple instance of gift card with multiple recipients
				 * */
				$recipient_count = count( $item_data['ywgc_recipients'] );
				$quantity        = ( $recipient_count > 1 ) ? $recipient_count : ( isset( $_REQUEST['quantity'] ) ? intval( $_REQUEST['quantity'] ) : 1 );
			}

			if ( WC()->cart->add_to_cart( $product_id, 1, 0, array(), $item_data ) ) {
				$this->show_cart_message_on_added_product( $product_id, $quantity );
			}

			// If we added the product to the cart we can now optionally do a redirect.
			if ( wc_notice_count( 'error' ) === 0 ) {

				$url = '';
				// If has custom URL redirect there
				if ( $url = apply_filters( 'woocommerce_add_to_cart_redirect', $url ) ) {
					wp_safe_redirect( $url );
					exit;
				} elseif ( get_option( 'woocommerce_cart_redirect_after_add' ) === 'yes' ) {
					if ( function_exists( 'wc_get_cart_url' ) ) {
						wp_safe_redirect( wc_get_cart_url() );
					} else {
						wp_safe_redirect( WC()->cart->get_cart_url() );
					}
					exit;
				}
			}
		}

		public function show_cart_message_on_added_product( $product_id, $quantity = 1 ) {
			//  From WC 2.6.0 the parameter format in wc_add_to_cart_message changed
			$gt_255 = version_compare( WC()->version, '2.5.5', '>' );
			$param  = $gt_255 ? array( $product_id => $quantity ) : $product_id;
			wc_add_to_cart_message( $param, true );
		}

		/**
		 * Set the real amount for the gift card product
		 *
		 * @param array $cart_item
		 *
		 * @since 1.5.0
		 * @return mixed
		 */
		public function set_price_in_cart( $cart_item ) {

			if ( isset( $cart_item['data'] ) ) {
				if ( $cart_item['data'] instanceof WC_Product_Gift_Card ) {
					$cart_item['data']->price = $cart_item['ywgc_amount'];
				}
			}

			return $cart_item;
		}

		/**
		 * Update cart item when retrieving cart from session
		 *
		 * @param $session_data mixed Session data to add to cart
		 * @param $values       mixed Values stored in session
		 *
		 * @return mixed Session data
		 * @since 1.5.0
		 */
		public function get_cart_item_from_session( $session_data, $values ) {

			if ( isset( $values['ywgc_product_id'] ) && $values['ywgc_product_id'] ) {

				$session_data['ywgc_product_id']       = isset( $values['ywgc_product_id'] ) ? $values['ywgc_product_id'] : '';
				$session_data['ywgc_amount']           = isset( $values['ywgc_amount'] ) ? $values['ywgc_amount'] : '';
				$session_data['ywgc_is_manual_amount'] = isset( $values['ywgc_is_manual_amount'] ) ? $values['ywgc_is_manual_amount'] : false;
				$session_data['ywgc_is_digital']       = isset( $values['ywgc_is_digital'] ) ? $values['ywgc_is_digital'] : false;

				if ( $session_data['ywgc_is_digital'] ) {
					$session_data['ywgc_recipients']     = isset( $values['ywgc_recipients'] ) ? $values['ywgc_recipients'] : '';
					$session_data['ywgc_sender_name']    = isset( $values['ywgc_sender_name'] ) ? $values['ywgc_sender_name'] : '';
					$session_data['ywgc_recipient_name'] = isset( $values['ywgc_recipient_name'] ) ? $values['ywgc_recipient_name'] : '';
					$session_data['ywgc_message']        = isset( $values['ywgc_message'] ) ? $values['ywgc_message'] : '';

					$session_data['ywgc_has_custom_design'] = isset( $values['ywgc_has_custom_design'] ) ? $values['ywgc_has_custom_design'] : false;
					$session_data['ywgc_design_type']       = isset( $values['ywgc_design_type'] ) ? $values['ywgc_design_type'] : '';
					if ( $session_data['ywgc_has_custom_design'] ) {
						$session_data['ywgc_design'] = isset( $values['ywgc_design'] ) ? $values['ywgc_design'] : '';
					}

					$session_data['ywgc_postdated'] = isset( $values['ywgc_postdated'] ) ? $values['ywgc_postdated'] : false;
					if ( $session_data['ywgc_postdated'] ) {
						$session_data['ywgc_delivery_date'] = isset( $values['ywgc_delivery_date'] ) ? $values['ywgc_delivery_date'] : false;
					}
				}

				if ( isset( $values['ywgc_amount'] ) ) {
					$session_data['data']->price = apply_filters( 'yith_ywgc_set_cart_item_price', $values['ywgc_amount'], $values );
				}
			}

			return $session_data;
		}

		/**
		 * move an uploaded file into a persistent folder with a unique name
		 *
		 * @param string $image uploaded image
		 *
		 * @return string   real path of the uploaded image
		 */
		public function save_uploaded_file( $image ) {
			// Create folders for storing documents
			$date     = getdate();
			$folder   = sprintf( "%s/%s", $date["year"], $date["mon"] );
			$filename = $image["name"];

			while ( true ) {

				$relative_path = sprintf( "%s/%s", $folder, $filename );
				$dir_path      = sprintf( "%s/%s", YITH_YWGC_SAVE_DIR, $folder );
				$full_path     = sprintf( "%s/%s", YITH_YWGC_SAVE_DIR, $relative_path );

				if ( ! file_exists( $full_path ) ) {
					if ( ! file_exists( $dir_path ) ) {
						wp_mkdir_p( $dir_path );
					}

					move_uploaded_file( $image["tmp_name"], $full_path );

					return $relative_path;
				} else {

					$unique_id = rand();

					$name_without_ext = pathinfo( $filename, PATHINFO_FILENAME );
					$ext              = pathinfo( $filename, PATHINFO_EXTENSION );

					$filename = $name_without_ext . $unique_id . '.' . $ext;
				}
			}
		}

		/**
		 * Let the user to edit che gift card content
		 */
		public function edit_gift_card_callback() {
			//todo check it
			if ( ! YITH_YWGC()->allow_modification ) {
				return;
			}

			$order_item_id = intval( sanitize_text_field( $_POST['item_id'] ) );
			$gift_card_id  = intval( sanitize_text_field( $_POST['gift_card_id'] ) );
			$sender        = sanitize_text_field( $_POST['sender'] );
			$recipient     = sanitize_email( $_POST['recipient'] );
			$message       = implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST['message'] ) ) );

			/** Retrieve the gift card content.
			 *  If a valid gift card was generated, the content to be edited is a post meta of the gift card.
			 *  In the opposite case all the data are order item meta
			 */
			$item_gift_card_ids = ywgc_get_order_item_giftcards( $order_item_id );

			if ( in_array( $gift_card_id, $item_gift_card_ids ) ) {
				//  The gift card exists, edit it as custom post type
				$curr_card = new YWGC_Gift_Card_Premium( array( 'ID' => $gift_card_id ) );
				if ( $curr_card->exists() ) {

					//  Update current gift card content without saving, this card will be dismissed leaving a new gift card build as a clone from it
					$clone_it               = $recipient != $curr_card->recipient;
					$curr_card->sender_name = $sender;
					$curr_card->recipient   = $recipient;
					$curr_card->message     = $message;

					//  check if the recipient changes, if so, set_dismissed_status the current gift card and
					//  create a new one
					if ( $clone_it ) {

						//  The gift cards being changed will be closed and a new one will be created
						$new_gift = $this->clone_gift_card( $curr_card );
						$new_gift->save();

						$curr_card->set_dismissed_status();

						//  assign the new gift card to the order item
						$item_gift_card_ids[] = $new_gift->ID;
						ywgc_set_order_item_giftcards( $order_item_id, $item_gift_card_ids );

						wp_send_json( array(
							"code"   => 2,
							"values" => array(
								"new_id" => $new_gift->ID,
							),
						) );
					} else {

						//  update the current gift card
						$curr_card->save();

						wp_send_json( array(
							"code" => 1,
						) );
					}
				}
			} else {
				//  a gift card custom post type object doesn't exists, edit order item meta values
				$meta = wc_get_order_item_meta( $order_item_id, YWGC_ORDER_ITEM_DATA );

				//edit order item meta
				$meta["sender"]    = $sender;
				$meta["recipient"] = $recipient;
				$meta["message"]   = $message;

				wc_update_order_item_meta( $order_item_id, YWGC_ORDER_ITEM_DATA, $meta );

				wp_send_json( array(
					"code" => 1,
				) );
			}

			wp_send_json( array(
				"code" => - 1,
			) );
		}

		/**
		 * Append data to order item
		 *
		 * @param $item_id
		 * @param $values
		 * @param $cart_item_key
		 *
		 * @return mixed
		 * @author Lorenzo Giuffrida
		 * @since  1.5.0
		 */
		public function append_gift_card_data_to_order_item( $item_id, $values, $cart_item_key ) {

			if ( ! isset( $values['ywgc_product_id'] ) ) {
				return;
			}

			/**
			 * Store all fields related to Gift Cards
			 */
			foreach ( $values as $key => $value ) {
				if ( strpos( $key, 'ywgc_' ) === 0 ) {
					$meta_key = '_' . $key;
					wc_update_order_item_meta( $item_id, $meta_key, $value );
				}
			}

			/**
			 * Store subtotal and subtotal taxes applied to the gift card
			 */
			wc_update_order_item_meta( $item_id, '_ywgc_subtotal', $values['line_subtotal'] );
			wc_update_order_item_meta( $item_id, '_ywgc_subtotal_tax', $values['line_subtotal_tax'] );

			/**
			 * Store the plugin version for future use
			 */
			wc_update_order_item_meta( $item_id, '_ywgc_version', YITH_YWGC_VERSION );

		}
	}
}

YITH_YWGC_Cart_Checkout::get_instance();