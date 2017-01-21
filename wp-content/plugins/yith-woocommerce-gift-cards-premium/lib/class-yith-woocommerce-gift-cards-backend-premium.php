<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


if ( ! class_exists( 'YITH_WooCommerce_Gift_Cards_Backend_Premium' ) ) {

	/**
	 *
	 * @class   YITH_WooCommerce_Gift_Cards_Backend_Premium
	 * @package Yithemes
	 * @since   1.0.0
	 * @author  Your Inspiration Themes
	 */
	class YITH_WooCommerce_Gift_Cards_Backend_Premium extends YITH_WooCommerce_Gift_Cards_Backend {
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

			parent::__construct();

			add_action( 'woocommerce_before_order_itemmeta', array(
				$this,
				'woocommerce_before_order_itemmeta'
			), 10, 3 );


			add_action( 'ywgc_start_gift_cards_sending', array( $this, 'send_delayed_gift_cards' ) );

			/**
			 * Set the class rate and class tax as visible for product of  type "gift cards"
			 */
			add_action( 'woocommerce_product_options_general_product_data', array(
				$this,
				'show_tax_class_for_gift_cards'
			) );

			/**
			 * manage CSS class for the gift cards table rows
			 */
			add_filter( 'post_class', array( $this, 'add_cpt_table_class' ), 10, 3 );

			add_action( 'init', array( $this, 'redirect_gift_cards_link' ) );
			add_action( 'wp_loaded', array( $this, 'manage_gift_card_email' ) );

			add_action( 'load-upload.php', array( $this, 'set_gift_card_category_to_media' ) );

			add_action( 'edited_term_taxonomy', array( $this, 'update_taxonomy_count' ), 10, 2 );

			/**
			 * Show icon that prompt the admin for a pre-printed gift cards buyed and whose code is not entered
			 */
			add_action( 'manage_shop_order_posts_custom_column', array(
				$this,
				'show_warning_for_pre_printed_gift_cards'
			) );

		}

		/**
		 * Show icon on backend page "orders" for order where there is file uploaded and waiting to be confirmed.
		 *
		 * @param string $column current column being shown
		 */
		public function show_warning_for_pre_printed_gift_cards( $column ) {
			//  If column is not of type order_status, skip it
			if ( 'order_status' !== $column ) {
				return;
			}

			global $the_order;
			$count = $this->pre_printed_cards_waiting_count( $the_order );
			if ( $count ) {
				$message = _n( "This order contains one pre-printed gift card that needs to be filled", sprintf( "This order contains %d pre-printed gift cards that needs to be filled", $count ), $count, 'yith-woocommerce-gift-cards' );
				echo '<img class="ywgc-pre-printed-waiting" src="' . YITH_YWGC_ASSETS_IMAGES_URL . 'waiting.png" title="' . $message . '" />';
			}
		}

		/**
		 * Retrieve the number of pre-printed gift cards that are not filled
		 *
		 * @param WC_Order $order
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 * @return int
		 */
		private function pre_printed_cards_waiting_count( $order ) {
			$order_items = $order->get_items( 'line_item' );
			$count       = 0;

			foreach ( $order_items as $order_item_id => $order_data ) {
				$gift_ids = ywgc_get_order_item_giftcards( $order_item_id );

				if ( empty( $gift_ids ) ) {
					return;
				}

				foreach ( $gift_ids as $gift_id ) {

					$args = array( 'ID' => $gift_id );
					$gc   = new YWGC_Gift_Card_Premium( $args );

					if ( $gc->is_pre_printed() ) {
						$count ++;
					}
				}
			}

			return $count;
		}

		/**
		 * Fix the taxonomy count of items
		 *
		 * @param $term_id
		 * @param $taxonomy_name
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function update_taxonomy_count( $term_id, $taxonomy_name ) {
			//  Update the count of terms for attachment taxonomy
			if ( YWGC_CATEGORY_TAXONOMY != $taxonomy_name ) {
				return;
			}

			//  update now
			global $wpdb;
			$count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships, $wpdb->posts p1 WHERE p1.ID = $wpdb->term_relationships.object_id AND ( post_status = 'publish' OR ( post_status = 'inherit' AND (post_parent = 0 OR (post_parent > 0 AND ( SELECT post_status FROM $wpdb->posts WHERE ID = p1.post_parent ) = 'publish' ) ) ) ) AND post_type = 'attachment' AND term_taxonomy_id = %d", $term_id ) );

			$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term_id ) );
		}


		public function set_gift_card_category_to_media() {

			//  Skip all request without an action
			if ( ! isset( $_REQUEST['action'] ) && ! isset( $_REQUEST['action2'] ) ) {
				return;
			}

			//  Skip all request without a valid action
			if ( ( '-1' == $_REQUEST['action'] ) && ( '-1' == $_REQUEST['action2'] ) ) {
				return;
			}

			$action = '-1' != $_REQUEST['action'] ? $_REQUEST['action'] : $_REQUEST['action2'];

			//  Skip all request that do not belong to gift card categories
			if ( ( 'ywgc-set-category' != $action ) && ( 'ywgc-unset-category' != $action ) ) {
				return;
			}

			//  Skip all request without a media list
			if ( ! isset( $_REQUEST['media'] ) ) {
				return;
			}

			$media_ids = $_REQUEST['media'];

			//  Check if the request if for set or unset the selected category to the selected media
			$action_set_category = ( 'ywgc-set-category' == $action ) ? true : false;

			//  Retrieve the category to be applied to the selected media
			$category_id = '-1' != $_REQUEST['action'] ? intval( $_REQUEST['categories1_id'] ) : intval( $_REQUEST['categories2_id'] );

			foreach ( $media_ids as $media_id ) {

				// Check whether this user can edit this post
				//if ( ! current_user_can ( 'edit_post', $media_id ) ) continue;

				if ( $action_set_category ) {
					$result = wp_set_object_terms( $media_id, $category_id, YWGC_CATEGORY_TAXONOMY, true );
				} else {
					$result = wp_remove_object_terms( $media_id, $category_id, YWGC_CATEGORY_TAXONOMY );
				}

				if ( is_wp_error( $result ) ) {
					return $result;
				}
			}
		}

		/**
		 * manage CSS class for the gift cards table rows
		 *
		 * @param array  $classes
		 * @param string $class
		 * @param int    $post_id
		 *
		 * @return array|mixed|void
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function add_cpt_table_class( $classes, $class, $post_id ) {

			if ( YWGC_CUSTOM_POST_TYPE_NAME != get_post_type( $post_id ) ) {
				return $classes;
			}

			$args      = array( 'ID' => $post_id );
			$gift_card = new YWGC_Gift_Card_Premium( $args );

			if ( ! $gift_card->exists() ) {
				return $class;
			}

			$classes[] = $gift_card->status;

			return apply_filters( 'yith_gift_cards_table_class', $classes, $post_id );
		}

		/**
		 * send the gift card code email
		 *
		 * @param int $gift_card_id the gift card id
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function send_gift_card_email( $gift_card_id ) {

			$args      = array( 'ID' => $gift_card_id );
			$gift_card = new YWGC_Gift_Card_Premium( $args );

			if ( ! $gift_card->exists() ) {
				//  it isn't a gift card
				return;
			}

			if ( ! $gift_card->is_virtual() || empty( $gift_card->recipient ) ) {
				// not a digital gift card or missing recipient
				return;
			}

			WC()->mailer();
			do_action( 'ywgc-email-send-gift-card_notification', $gift_card );
		}

		/**
		 * Manage the request from an email for a gift card code to be applied to the cart
		 *
		 */
		public function manage_gift_card_email() {

			if ( isset( $_GET[ YWGC_ACTION_ADD_DISCOUNT_TO_CART ] ) &&
			     isset( $_GET[ YWGC_ACTION_VERIFY_CODE ] )
			) {
				$gift_card = $this->main->get_gift_card_by_code( $_GET[ YWGC_ACTION_ADD_DISCOUNT_TO_CART ] );

				if ( $gift_card->exists() && $gift_card->is_enabled() ) {

					//  Check the hash value
					$hash_value = $this->main->hash_gift_card( $gift_card );

					if ( $hash_value == $_GET[ YWGC_ACTION_VERIFY_CODE ] ) {
						//  can add the discount to the cart
						if (class_exists('WC_Cart')) {
							WC()->cart->remove_coupon($gift_card->get_code() );
							WC()->cart->add_discount( $gift_card->get_code() );
						}
					}
				}
			}
		}

		/**
		 * Make some redirect based on the current action being performed
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function redirect_gift_cards_link() {

			/**
			 * Check if a gift card discount should be added to the cart
			 */
			if ( isset( $_GET[ YWGC_ACTION_ADD_DISCOUNT_TO_CART ] ) &&
			     isset( $_GET[ YWGC_ACTION_VERIFY_CODE ] )
			) {
				return;
				//  if not currently applied, add the discount to the cart
				if ( ! isset( $_GET['apply_discount_code'] ) ) {

					wp_redirect( add_query_arg(
						array(
							YWGC_ACTION_ADD_DISCOUNT_TO_CART => $_GET[ YWGC_ACTION_ADD_DISCOUNT_TO_CART ],
							YWGC_ACTION_VERIFY_CODE          => $_GET[ YWGC_ACTION_VERIFY_CODE ],
							'apply_discount_code'            => 1,
						),
						wc_get_cart_url() ) );
					exit;
				}
			}

			/**
			 * Check if the user ask for retrying sending the gift card email that are not shipped yet
			 */
			if ( isset( $_GET[ YWGC_ACTION_RETRY_SENDING ] ) ) {
				$post_id = $_GET['id'];
				$this->send_gift_card_email( $post_id );


				wp_redirect( remove_query_arg( array( YWGC_ACTION_RETRY_SENDING, 'id' ) ) );
				exit;
			}

			/**
			 * Check if the user ask for enabling/disabling a specific gift cards
			 */
			if ( isset( $_GET[ YWGC_ACTION_ENABLE_CARD ] ) || isset( $_GET[ YWGC_ACTION_DISABLE_CARD ] ) ) {
				$gift_card_id = $_GET['id'];
				$enabled      = isset( $_GET[ YWGC_ACTION_ENABLE_CARD ] );

				$args      = array( 'ID' => $gift_card_id );
				$gift_card = new YWGC_Gift_Card_Premium( $args );

				if ( ! $gift_card->is_dismissed() ) {

					$current_status = $gift_card->is_enabled();

					if ( $current_status != $enabled ) {

						$gift_card->set_enabled_status( $enabled );
						do_action( 'yith_gift_cards_status_changed', $gift_card, $enabled );
					}

					wp_redirect( remove_query_arg( array( YWGC_ACTION_ENABLE_CARD, YWGC_ACTION_DISABLE_CARD, 'id' ) ) );
					die();
				}
			}


			if ( ! isset( $_GET["post_type"] ) || ! isset( $_GET["s"] ) ) {
				return;
			}

			if ( 'shop_coupon' != ( $_GET["post_type"] ) ) {
				return;
			}

			if ( preg_match( "/(\w{4}-\w{4}-\w{4}-\w{4})(.*)/i", $_GET["s"], $matches ) ) {
				wp_redirect( admin_url( 'edit.php?s=' . $matches[1] . '&post_type=gift_card' ) );
				die();
			}
		}

		public function show_tax_class_for_gift_cards() {
			echo '<script>
                jQuery("select#_tax_status").closest("div.options_group").addClass("show_if_gift-card");
            </script>';
		}

		/**
		 * Show the gift card code under the order item, in the order admin page
		 *
		 * @param int        $item_id
		 * @param array      $item
		 * @param WC_product $_product
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function woocommerce_before_order_itemmeta( $item_id, $item, $_product ) {

			global $theorder;
			$gift_ids = ywgc_get_order_item_giftcards( $item_id );


			if ( empty( $gift_ids ) ) {
				return;
			}

			foreach ( $gift_ids as $gift_id ) {
				$args = array( 'ID' => $gift_id );
				$gc   = new YWGC_Gift_Card_Premium( $args );

				if ( ! $gc->is_pre_printed() ):
					?>
					<div>
					<span
						class="ywgc-gift-code-label"><?php _e( "Gift card code: ", 'yith-woocommerce-gift-cards' ); ?></span>

						<a href="<?php echo admin_url( 'edit.php?s=' . $gc->get_code() . '&post_type=gift_card&mode=list' ); ?>"
						   class="ywgc-card-code"><?php echo $gc->get_code(); ?></a>
					</div>
				<?php elseif ( apply_filters( 'yith_ywgc_enter_pre_printed_gift_card_code', true, $theorder, $_product ) ): ?>
					<div>
					<span
						class="ywgc-gift-code-label"><?php _e( "Enter the pre-printed code: ", 'yith-woocommerce-gift-cards' ); ?></span>
						<input type="text" name="ywgc-pre-printed-code[<?php echo $gc->ID; ?>]"
						       class="ywgc-pre-printed-code">
					</div>
				<?php endif;
			}
		}

		/**
		 * Send the digital gift cards that should be received on specific date.
		 *
		 * @param string $send_date
		 */
		public function send_delayed_gift_cards( $send_date = null ) {
			if ( ! class_exists( "YITH_YWGC_Email_Send_Gift_Card" ) ) {
				include( 'emails/class-yith-ywgc-email-send-gift-card.php' );
			}

			if ( null == $send_date ) {
				$send_date = current_time( 'Y-m-d', 0 );
			}

			// retrieve gift card to be sent for specific date
			$gift_cards_ids = $this->main->get_postdated_gift_cards( $send_date );

			foreach ( $gift_cards_ids as $gift_card_id ) {
				// send digital single gift card to recipient
				$args      = array( 'ID' => $gift_card_id );
				$gift_card = new YWGC_Gift_Card_Premium( $args );

				if ( ! $gift_card->exists() ) {
					continue;
				}

				if ( ! $gift_card->is_virtual() || empty( $gift_card->recipient ) ) {
					// not a digital gift card or missing recipient
					continue;
				}

				if ( $gift_card->has_been_sent() ) {
					//  avoid sending emails more than one time
					continue;
				}

				do_action( 'ywgc-email-send-gift-card_notification', $gift_card );
			}
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