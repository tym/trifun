<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


if ( ! class_exists( 'YITH_YWGC_Frontend_Premium' ) ) {

	/**
	 *
	 * @class   YITH_YWGC_Frontend_Premium
	 * @package Yithemes
	 * @since   1.0.0
	 * @author  Your Inspiration Themes
	 */
	class YITH_YWGC_Frontend_Premium extends YITH_YITH_Frontend {

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

			/**
			 * Permit to enter free gift card amount
			 */
			add_action( 'yith_gift_cards_template_after_amounts', array( $this, 'show_free_amount_area' ) );

			/**
			 * Let the user to enter a free amount instead of choosing from the select
			 */
			add_action( 'yith_gift_cards_template_append_amount', array( $this, 'add_manual_amount_item' ) );

			/**
			 * Show a live preview of how the gift card will look like
			 */
			add_action( 'yith_gift_cards_template_after_gift_card_form', array(
				$this,
				'show_gift_card_generator'
			), 1 );

			/**
			 * Let the customer to use a product of type WC_Product_Simple  as source for a gift card
			 */
			add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'show_give_as_present_link_simple' ) );

			/**
			 * Let the customer to use a product of type WC_Product_Variable  as source for a gift card
			 */
			add_action( 'woocommerce_single_variation', array( $this, 'show_give_as_present_link_variable' ), 99 );


			/**
			 * Show the gift cards registered to the current user in my-account page
			 */
			add_action( 'woocommerce_after_my_account', array( $this, 'show_my_gift_cards_table' ) );

			/**
			 * Manage the WooCommerce 2.6.0 changes in the cart page and
			 * change the message sent when in cart page a gift card code is entered.
			 * @since 1.4.0
			 */
			add_filter( 'woocommerce_coupon_message', array( $this, 'change_messsage_in_cart' ), 10, 3 );
			add_filter( 'woocommerce_coupon_error', array( $this, 'change_messsage_in_cart' ), 10, 3 );

			add_action( 'yith_ywgc_gift_card_preview_end', array(
				$this,
				'append_design_presets'
			) );

			add_action( 'yith_ywgc_gift_card_preview_content', array(
				$this,
				'show_design_section'
			) );

			add_action( 'yith_ywgc_gift_card_preview_content', array(
				$this,
				'show_gift_card_details'
			), 15 );

			add_action( 'yith_ywgc_generator_buttons_before', array(
				$this,
				'show_cancel_button_on_gift_this_product'
			) );

			add_action( 'yith_ywgc_gift_card_preview', array(
				$this,
				'show_template_preview'
			) );
		}

		public function show_template_preview( $product ) {
			YITH_YWGC()->preview_digital_gift_cards( $product );
		}

		/**
		 * Append the design preset to the gift card preview
		 */
		public function append_design_presets( $product ) {

			if ( ! $this->can_show_template_design( $product->id ) ) {
				return;
			}

			$categories = get_terms( YWGC_CATEGORY_TAXONOMY, array( 'hide_empty' => 1 ) );

			$item_categories = array();
			foreach ( $categories as $item ) {
				$object_ids = get_objects_in_term( $item->term_id, YWGC_CATEGORY_TAXONOMY );
				foreach ( $object_ids as $object_id ) {
					$item_categories[ $object_id ] = isset( $item_categories[ $object_id ] ) ? $item_categories[ $object_id ] . ' ywgc-category-' . $item->term_id : 'ywgc-category-' . $item->term_id;
				}
			}

			wc_get_template( 'yith-gift-cards/gift-card-presets.php',
				array(
					'categories'      => $categories,
					'item_categories' => $item_categories
				),
				'',
				trailingslashit( YITH_YWGC_TEMPLATES_DIR ) );
		}

		public function show_design_section( $product ) {
			// Load the template
			wc_get_template( 'yith-gift-cards/gift-card-design.php',
				array(
					'allow_templates'       => $this->can_show_template_design( $product->id ),
					'allow_customer_images' => YITH_YWGC()->allow_custom_design,
				),
				'',
				trailingslashit( YITH_YWGC_TEMPLATES_DIR ) );
		}

		public function show_gift_card_details( $product ) {
			// Load the template
			wc_get_template( 'yith-gift-cards/gift-card-details.php',
				array(
					'allow_templates'           => $this->can_show_template_design( $product->id ),
					'allow_customer_images'     => YITH_YWGC()->allow_custom_design,
					'allow_multiple_recipients' => YITH_YWGC()->allow_multiple_recipients && ( $product instanceof WC_Product_Gift_Card ),
					'mandatory_recipient'       => YITH_YWGC()->mandatory_recipient,
					'gift_this_product'         => ! ( $product instanceof WC_Product_Gift_Card ),
					'allow_send_later'         => YITH_YWGC()->allow_send_later,
				),
				'',
				trailingslashit( YITH_YWGC_TEMPLATES_DIR ) );
		}

		public function show_cancel_button_on_gift_this_product( $product ) {
			if ( $product instanceof WC_Product_Gift_Card ) {
				return;
			}
			?>
			<button id="ywgc-cancel-gift-card"
			        class="button"><?php _e( "Cancel", 'yith-woocommerce-gift-cards' ); ?></button>
			<?php
		}

		/**
		 * Change the message sent when in cart page a gift card code is entered.
		 *
		 * @param string    $msg
		 * @param int       $msg_code
		 * @param WC_Coupon $coupon
		 *
		 * @return string
		 */
		public function change_messsage_in_cart( $msg, $msg_code, $coupon ) {
			if ( ! isset( $_REQUEST['is_gift_card'] ) ) {
				return $msg;
			}

			switch ( $msg_code ) {
				case WC_Coupon::E_WC_COUPON_ALREADY_APPLIED :
					$msg = __( 'The gift card code is already applied to the current cart.', 'yith-woocommerce-gift-cards' );
					break;

				case WC_Coupon::WC_COUPON_SUCCESS :
					$msg = __( 'Gift card code applied successfully.', 'yith-woocommerce-gift-cards' );
					break;

				case WC_Coupon::WC_COUPON_REMOVED :
					$msg = __( 'Gift card code removed successfully.', 'yith-woocommerce-gift-cards' );
					break;

				case WC_Coupon::E_WC_COUPON_NOT_EXIST :
					$msg = sprintf( __( 'Gift card code "%s" does not exist!', 'yith-woocommerce-gift-cards' ), $coupon->code );
					break;
			}

			return $msg;
		}

		/**
		 * Show my gift cards status on myaccount page
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function show_my_gift_cards_table() {
			wc_get_template( 'myaccount/my-giftcards.php',
				'',
				'',
				trailingslashit( YITH_YWGC_TEMPLATES_DIR ) );
		}


		/**
		 * Clone a gift card with updated balance
		 *
		 * @param YWGC_Gift_Card_Premium $gift_card the initial gift card
		 *
		 * @return YWGC_Gift_Card_Premium
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function clone_gift_card( $gift_card ) {

			$code = YITH_YWGC()->generate_gift_card_code();

			return $gift_card->clone_gift_card( $code );
		}


		/**
		 * Let the user to edit the gift card
		 *
		 * @param $order_item_id
		 * @param $item
		 * @param $order
		 */
		public function edit_gift_card( $order_item_id, $item, $order ) {

			if ( ! YITH_YWGC()->allow_modification ) {
				return;
			}

			//  Allow editing only on checkout or my orders pages
			if ( ! is_checkout() && ! is_account_page() ) {
				return;
			}

			$item_meta_array = $item["item_meta"];
			//  Check if current order item is a gift card
			if ( ! isset( $item_meta_array[ YWGC_ORDER_ITEM_DATA ] ) ) {

				return;
			}

			//  Retrieve the gift card content. If a valid gift card was generated, the content to be edited is a postmeta of the
			//  Gift card post type, else the content is still on the order_item_meta.
			$gift_cards = ywgc_get_order_item_giftcards( $order_item_id );

			if ( $gift_cards ) {
				$_gift_card_id = is_array( $gift_cards ) ? $gift_cards[0] : $gift_cards;

				//  edit values from a gift card object stored on the DB
				$gift_card = new YWGC_Gift_Card_Premium( array( 'ID' => $_gift_card_id ) );

			} else {
				//  edit the data stored as order item meta because the final gift card is not created yet
				$order_item_meta = $item_meta_array[ YWGC_ORDER_ITEM_DATA ];
				$order_item_meta = $order_item_meta[0];
				$order_item_meta = maybe_unserialize( $order_item_meta );

				$gift_card = new YWGC_Gift_Card_Premium( $order_item_meta );
			}

			//  Check if the gift card still exists
			//todo do not block the editing on gift card that are not generated yet
			if ( ! $gift_card->exists() ) {
				//return;
			}

			//  There is nothing to edit for physical gift card product, only virtual gift cards
			//  can be edited

			if ( ! $gift_card->is_virtual() ) {
				return;
			}

			?>

			<div id="current-gift-card-<?php echo $order_item_id; ?>" class="ywgc-gift-card-content">
				<a href="#"
				   class="edit-details"><?php _e( "See card details", 'yith-woocommerce-gift-cards' ); ?></a>

				<div class="ywgc-gift-card-details ywgc-hide">
					<h3><?php _e( "Gift card details", 'yith-woocommerce-gift-cards' ); ?></h3>
					<fieldset class="ywgc-sender-details" style="border: none">
						<label><?php _e( "Sender: ", 'yith-woocommerce-gift-cards' ); ?></label>
						<span class="ywgc-sender"><?php echo $gift_card->sender_name; ?></span>
					</fieldset>

					<fieldset class="ywgc-recipient-details" style="border: none">
						<label><?php _e( "Recipient: ", 'yith-woocommerce-gift-cards' ); ?></label>
						<span class="ywgc-recipient"><?php echo $gift_card->recipient; ?></span>
					</fieldset>

					<fieldset class="ywgc-message-details" style="border: none">
						<label><?php _e( "Message: ", 'yith-woocommerce-gift-cards' ); ?></label>
						<span class="ywgc-message"><?php echo $gift_card->message; ?></span>
					</fieldset>
					<button
						class="ywgc-do-edit btn btn-ghost"
						style="display: none;"><?php _e( "Edit", 'yith-woocommerce-gift-cards' ); ?></button>
				</div>

				<div class="ywgc-gift-card-edit-details ywgc-hide" style="display: none">
					<h3><?php _e( "Gift card details", 'yith-woocommerce-gift-cards' ); ?></h3>

					<form name="form-gift-card-<?php echo $gift_card->ID; ?>">
						<input type="hidden" name="ywgc-gift-card-id" value="<?php echo $gift_card->ID; ?>">
						<input type="hidden" name="ywgc-item-id" value="<?php echo $order_item_id; ?>">
						<fieldset>
							<label
								for="ywgc-edit-sender"><?php _e( "Sender: ", 'yith-woocommerce-gift-cards' ); ?></label>
							<input type="text" name="ywgc-edit-sender" id="ywgc-edit-sender"
							       value="<?php echo $gift_card->sender_name; ?>">
						</fieldset>

						<fieldset>
							<label
								for="ywgc-edit-recipient"><?php _e( "Recipient: ", 'yith-woocommerce-gift-cards' ); ?></label>
							<input type="email" name="ywgc-edit-recipient" id="ywgc-edit-recipient"
							       value="<?php echo $gift_card->recipient; ?>"">
						</fieldset>

						<fieldset>
							<label
								for="ywgc-edit-message"><?php _e( "Message: ", 'yith-woocommerce-gift-cards' ); ?></label>
							<textarea name="ywgc-edit-message" id="ywgc-edit-message"
							          rows="5"><?php echo $gift_card->message; ?></textarea>
						</fieldset>
					</form>

					<button
						class="ywgc-apply-edit btn apply"><?php _e( "Apply", 'yith-woocommerce-gift-cards' ); ?></button>
					<button
						class="ywgc-cancel-edit btn btn-ghost"><?php _e( "Cancel", 'yith-woocommerce-gift-cards' ); ?></button>
				</div>
			</div>
			<?php
		}

		/**
		 * Let the customer to use a product of type WC_Product_Simple  as source for a gift card
		 */
		public function show_give_as_present_link_simple() {
			if ( ! YITH_YWGC()->allow_product_as_present ) {
				return;
			}

			global $product;
			if ( $product instanceof WC_Product_Simple ) {
				// Load the template
				wc_get_template( 'single-product/add-to-cart/give-product-as-present.php',
					'',
					'',
					trailingslashit( YITH_YWGC_TEMPLATES_DIR ) );
			}
		}

		/**
		 * Let the customer to use a product of type WC_Product_Variable  as source for a gift card
		 */
		public function show_give_as_present_link_variable() {
			if ( ! YITH_YWGC()->allow_product_as_present ) {
				return;
			}

			global $product;
			if ( $product instanceof WC_Product_Variable ) {
				// Load the template
				wc_get_template( 'single-product/add-to-cart/give-product-as-present.php',
					'',
					'',
					trailingslashit( YITH_YWGC_TEMPLATES_DIR ) );
			}
		}

		/**
		 * Check if a gift card product avoid entering manual amount value
		 *
		 * @param WC_Product_Gift_Card $product
		 *
		 * @return bool
		 */
		public function is_manual_amount_allowed( $product ) {

			$manual_amount = $product->get_manual_amount_status();

			//  if the gift card have specific manual entered amount behaviour, return that
			if ( "global" != $manual_amount ) {
				return "accept" == $manual_amount;
			}

			return YITH_YWGC()->allow_manual_amount;
		}

		/**
		 * Show a live preview of how the gift card will look like
		 */
		public function show_gift_card_generator() {
			global $product;

			if ( ( $product instanceof WC_Product_Gift_Card ) && ! $product->is_virtual() ) {
				return;
			}

			// Load the template
			wc_get_template( 'yith-gift-cards/gift-card-generator.php',
				array(
					'product' => $product,
				),
				'',
				trailingslashit( YITH_YWGC_TEMPLATES_DIR ) );
		}

		/**
		 * Permit to enter free gift card amount
		 *
		 * @param WC_Product_Gift_Card $product
		 */
		public function show_free_amount_area( $product ) {
			if ( ! $this->is_manual_amount_allowed( $product ) ) {
				return;
			}

			$amounts  = $product->get_amounts_to_be_shown();
			$hide_css = count( $amounts ) ? 'ywgc-hidden' : '';

			?>
			<input id="ywgc-manual-amount" name="ywgc-manual-amount"
			       class="ywgc-manual-amount <?php echo $hide_css; ?>" type="text"
			       placeholder="<?php _e( "Enter amount(Only digits)", 'yith-woocommerce-gift-cards' ); ?>">
			<?php
		}

		/**
		 * Show a dropdown for selecting the amount
		 *
		 * @param WC_Product_Gift_Card $product
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function show_gift_cards_amount_dropdown( $product ) {
			$amounts = $product->get_amounts_to_be_shown();

			if ( $amounts ) :
				?>
				<select id="gift_amounts" name="gift_amounts">
					<!--					<option value="">-->
					<?php //_e( "Choose an amount", 'yith-woocommerce-gift-cards' );
					?><!--</option>-->

					<?php foreach ( $product->get_amounts_to_be_shown() as $value => $price ) : ?>

						<option
							value="<?php echo $value; ?>" <?php echo selected( $price, $value, false ); ?>><?php echo wc_price( $price ); ?></option>
					<?php endforeach; ?>

					<?php
					//  Check if the current product permit free entered amount...
					if ( $this->is_manual_amount_allowed( $product ) ): ?>
						<option value="-1"><?php _e( "Manual amount", 'yith-woocommerce-gift-cards' ); ?></option>
					<?php endif; ?>
				</select>
				<?php
			endif;

			do_action( 'yith_gift_cards_template_after_amounts', $product );
		}


		/**
		 * Retrieve if the templates design should be shown for the product
		 *
		 * @param int $product_id the product id
		 *
		 * @author Lorenzo Giuffrida
		 *
		 * @since  1.0.0
		 */
		public function can_show_template_design( $product_id ) {
			if ( ! $this->is_template_design_allowed( $product_id ) ) {
				return false;
			}

			//  If template design are allowed, show it (if there are at least one!)
			return $this->template_design_count();
		}


		/**
		 * Retrieve the number of templates available
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public
		function template_design_count() {
			$media_terms = get_terms( YWGC_CATEGORY_TAXONOMY, array( 'hide_empty' => 1 ) );
			$ids         = array();
			foreach ( $media_terms as $media_term ) {
				$ids[] = $media_term->term_id;
			}

			$template_ids = array_unique( get_objects_in_term( $ids, YWGC_CATEGORY_TAXONOMY ) );

			return count( $template_ids );
		}

		/**
		 * Check if a gift card product permit to choose from a custom template design
		 *
		 * @param $product_id int the product id to check
		 *
		 * @return bool
		 */
		public
		function is_template_design_allowed(
			$product_id
		) {
			$product        = new WC_Product_Gift_Card( $product_id );
			$show_templates = $product->get_design_status();

			//  If the product have a custom status related to the use of template design, return that settings
			if ( "enabled" == $show_templates ) {
				return true;
			}

			if ( "disabled" == $show_templates ) {
				return false;
			}

			//  If there isn't a custom status, retrieve the global settings

			return YITH_YWGC()->allow_template_design;
		}

	}
}