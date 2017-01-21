<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


if ( ! class_exists( 'YITH_WooCommerce_Gift_Cards_Premium' ) ) {

	/**
	 *
	 * @class   YITH_WooCommerce_Gift_Cards_Premium
	 * @package Yithemes
	 * @since   1.0.0
	 * @author  Your Inspiration Themes
	 */
	class YITH_WooCommerce_Gift_Cards_Premium extends YITH_WooCommerce_Gift_Cards {
		/**
		 * @var int The default product of type gift card
		 */
		public $default_gift_card_id = - 1;

		/**
		 * @var bool Let the user to enter manually the amount of the gift card
		 */
		public $allow_manual_amount = false;

		/**
		 * @var bool allow the customer to choose a product from the shop to be used as a present for the gift card
		 */
		public $allow_product_as_present = false;

		/**
		 * @var bool allow the customer to edit the content of a gift card
		 */
		public $allow_modification = false;

		/**
		 * @var bool let your customer to buy a digital card and send it later
		 */
		public $allow_send_later = false;

		/**
		 * @var bool notify the customer when a gift card he bought is used
		 */
		public $notify_customer = false;

		/**
		 * @var string the shop name
		 */
		public $shop_name;

		/**
		 * @var int limit the maximum size of custom image uploaded by the customer
		 */
		public $custom_image_max_size;

		/**
		 * @var string  the logo to be used on the gift card
		 */
		public $shop_logo_url;

		/**
		 * @var bool set it the shop logo should be shown inside the gift card template
		 */
		public $shop_logo_on_template = false;

		/**
		 * @var string the image url used as gift card header
		 */
		public $default_header_image_url;

		/**
		 * @var string the style to be used for the email
		 *
		 */
		public $template_style = 'style1';
		/**
		 * @var bool set if the admin should receive the email containing the gift card code in BCC
		 */
		public $blind_carbon_copy;

		/**
		 * @var bool enable the automatic discount when the customer click on the link in the email received
		 */
		public $automatic_discount = false;

		/**
		 * @var bool restrict the usage of the gift card to the recipient
		 */
		public $restricted_usage = false;

		/**
		 * @var bool allow to use a user picture as custom gift card design
		 */
		public $allow_custom_design = true;

		/**
		 * @var bool let the user to choose from some gift cards templates
		 */
		public $allow_template_design = false;

		/**
		 * @var bool
		 */
		public $allow_multiple_recipients = true;

		/**
		 * @var string action to perform on order cancelled
		 */
		public $order_cancelled_action = '';

		/**
		 * @var string action to perform on order refunded
		 */
		public $order_refunded_action = '';

		/**
		 * @var bool choose if the pre-printed mode is enabled for physical gift cards
		 */
		public $enable_pre_printed = false;

		/**
		 * @var bool Ask for the recipient email when adding a digital gift card to the cart
		 */
		public $mandatory_recipient = true;

		/**
		 * @var bool apply gift cards for shipping cost discount
		 */
		public $shipping_discount = false;

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

			if ( ! class_exists( 'Emogrifier' ) ) {

				require_once( WC()->plugin_path() . '/includes/libraries/class-emogrifier.php' );
			}

			$this->register_custom_post_statuses();


			/*
			 * Customize a gift card with data entered by the customer on product page
			 */
			add_filter( 'yith_gift_cards_before_add_to_cart', array( $this, 'customize_card_before_add_to_cart' ) );

			/**
			 * Permit gift card to be purchasable even without price
			 */
			add_filter( 'woocommerce_is_purchasable', array( $this, 'gift_card_is_purchasable' ), 10, 2 );

			/**
			 * Add an option to let the admin set the gift card as a physical good or digital goods
			 */
			add_filter( 'product_type_options', array( $this, 'add_type_option' ) );

			/**
			 * When the default gift card image is changed from the plugin setting, update the product image
			 * of the default gift card
			 */
			add_action( 'yit_panel_wc_after_update', array( $this, 'update_default_gift_card' ) );

			/**
			 * Add plugin compatibility with YITH WooCommerce Multi Vendor
			 */
			add_filter( 'ywgc_can_create_gift_card', array( $this, 'user_can_create_gift_cards' ) );

			/**
			 * Append CSS for the email being sent to the customer
			 */
			add_action( 'yith_gift_cards_template_before_add_to_cart_form', array( $this, 'append_css_files' ) );

			/**
			 * Add taxonomy and assign it to gift card products
			 */
			add_action( 'init', array(
				$this,
				'create_gift_cards_category'
			) );

			/**
			 * Convert gift card amounts shown on product page according to current WPML currency
			 */
			add_filter( 'yith_ywgc_gift_card_amounts', array(
				$this,
				'get_wpml_multi_currency'
			), 10, 2 );
		}

		/**
		 * Convert gift card amounts shown on product page according to current WPML currency
		 *
		 * @param array                $amounts amounts to be shown
		 * @param WC_Product_Gift_Card $product the gift card product
		 *
		 * @return array
		 */
		public function get_wpml_multi_currency( $amounts, $product ) {

			if ( $amounts ) {
				$multi_currency_amounts = array();
				foreach ( $amounts as $amount ) {
					$multi_currency_amounts[] = apply_filters( 'wcml_raw_price_amount', $amount );
				}

				return $multi_currency_amounts;
			}

			return $amounts;
		}

		// register new taxonomy which applies to attachments
		public function create_gift_cards_category() {

			$labels = array(
				'name'              => 'Gift Cards Categories',
				'singular_name'     => 'Gift Card Category',
				'search_items'      => 'Search Gift Card Categories',
				'all_items'         => 'All Gift Card Categories',
				'parent_item'       => 'Parent Gift Card Category',
				'parent_item_colon' => 'Parent Gift Card Category:',
				'edit_item'         => 'Edit Gift Card Category',
				'update_item'       => 'Update Gift Card Category',
				'add_new_item'      => 'Add New Gift Card Category',
				'new_item_name'     => 'New Gift Card Category Name',
				'menu_name'         => 'Gift Card Category',
			);

			$args = array(
				'labels'            => $labels,
				'hierarchical'      => true,
				'query_var'         => true,
				'rewrite'           => true,
				'show_admin_column' => true,
				'show_ui'           => true,
				'public'            => true,
			);

			register_taxonomy( YWGC_CATEGORY_TAXONOMY, 'attachment', $args );
		}


		/**
		 * Register all the custom post statuses of gift cards
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function register_custom_post_statuses() {

			register_post_status( GIFT_CARD_STATUS_DISABLED, array(
					'label'                     => __( 'Disabled', 'yith-woocommerce-gift-cards' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( __( 'Disabled', 'yith-woocommerce-gift-cards' ) . '<span class="count"> (%s)</span>', __( 'Disabled', 'yith-woocommerce-gift-cards' ) . ' <span class="count"> (%s)</span>' ),
				)
			);

			register_post_status( GIFT_CARD_STATUS_DISMISSED, array(
					'label'                     => __( 'Dismissed', 'yith-woocommerce-gift-cards' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( __( 'Dismissed', 'yith-woocommerce-gift-cards' ) . '<span class="count"> (%s)</span>', __( 'Dismissed', 'yith-woocommerce-gift-cards' ) . ' <span class="count"> (%s)</span>' ),
				)
			);
		}


		/**
		 * Append CSS for the email being sent to the customer
		 */
		public function append_css_files() {
			YITH_YWGC()->frontend->enqueue_frontend_style();
		}


		/**
		 * Deny all vendors from creating gift cards
		 *
		 * @param $enable_user bool current enable status
		 *
		 * @return bool
		 */
		public function user_can_create_gift_cards( $enable_user ) {
			//  if YITH Multivendor is active, check if the user can
			if ( defined( 'YITH_WPV_PREMIUM' ) ) {
				$vendor = yith_get_vendor( 'current', 'user' );

				return $vendor->is_super_user();
			}

			return $enable_user;
		}

		/**
		 * When the default gift card image is changed from the plugin setting, update the product image
		 * of the default gift card
		 */
		public function update_default_gift_card() {
			if ( isset( $_POST["ywgc_gift_card_header_url-yith-attachment-id"] ) ) {
				update_post_meta( $this->default_gift_card_id, "_thumbnail_id", $_POST["ywgc_gift_card_header_url-yith-attachment-id"] );
			}
		}

		/**
		 * Hash the gift card code so it could be used for security checks
		 *
		 * @param YWGC_Gift_Card_Premium $gift_card
		 *
		 * @return string
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function hash_gift_card( $gift_card ) {

			return hash( 'md5', $gift_card->gift_card_number . $gift_card->ID );
		}


		/**
		 * Add an option to let the admin set the gift card as a physical good or digital goods.
		 *
		 * @param array $array
		 *
		 * @return mixed
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function add_type_option( $array ) {
			if ( isset( $array["virtual"] ) ) {
				$array["virtual"]["wrapper_class"] = add_cssclass( 'show_if_gift-card', $array["virtual"]["wrapper_class"] );
			}

			return $array;
		}

		/**
		 * Permit gift card to be purchasable even without price
		 *
		 * @param bool       $purchasable
		 * @param WC_Product $product
		 *
		 * @return bool
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function gift_card_is_purchasable( $purchasable, $product ) {

			if ( ! ( $product instanceof WC_Product_Gift_Card ) ) {
				return $purchasable;
			}

			return true;
		}

		/**
		 * Create a product of type gift card to be used as placeholder. Should not be visible on shop page.
		 */
		public function initialize_products() {
			//  Search for a product with meta YWGC_PRODUCT_PLACEHOLDER
			$this->default_gift_card_id = get_option( YWGC_PRODUCT_PLACEHOLDER, - 1 );

			if ( - 1 == $this->default_gift_card_id ) {

				//  Create a default gift card product
				$args = array(
					'post_title'   => __( 'Gift card', 'yith-woocommerce-gift-cards' ),
					'post_name'    => __( 'gift_card', 'yith-woocommerce-gift-cards' ),
					'post_content' => __( 'This product has been automatically created by the plugin YITH Gift Cards.You must not edit it, or the plugin could not work properly', 'yith-woocommerce-gift-cards' ),
					'post_status'  => 'publish',
					'post_date'    => date( 'Y-m-d H:i:s' ),
					'post_author'  => 0,
					'post_type'    => 'product',
				);

				$this->default_gift_card_id = wp_insert_post( $args );
				update_option( YWGC_PRODUCT_PLACEHOLDER, $this->default_gift_card_id );

				//  Create a taxonomy for products of type YWGC_GIFT_CARD_PRODUCT_TYPE and
				//  set the product created to the new taxonomy
				//  Create product type
				$term = wp_insert_term( YWGC_GIFT_CARD_PRODUCT_TYPE, 'product_type' );

				$term_id = - 1;
				if ( $term instanceof WP_Error ) {
					$error_code = $term->get_error_code();
					if ( "term_exists" == $error_code ) {
						$term_id = $term->get_error_data( $error_code );
					}
				} else {
					$term_id = $term["term_id"];
				}

				if ( $term_id != - 1 ) {
					wp_set_object_terms( $this->default_gift_card_id, $term_id, 'product_type' );
				} else {
					wp_die( __( "An error occurred, you cannot use the plugin", 'yith-woocommerce-gift-cards' ) );
				}
			}
			//  set this default gift card product as virtual
			update_post_meta( $this->default_gift_card_id, "_virtual", 'yes' );
		}

		public function init_plugin() {
			$this->allow_manual_amount       = "yes" == get_option( 'ywgc_permit_free_amount' );
			$this->allow_product_as_present  = "yes" == get_option( 'ywgc_permit_its_a_present' );
			$this->allow_modification        = "yes" == get_option( 'ywgc_permit_modification' );
			$this->allow_send_later          = "yes" == get_option( 'ywgc_enable_send_later' );
			$this->notify_customer           = "yes" == get_option( 'ywgc_notify_customer' );
			$this->blind_carbon_copy         = "yes" == get_option( "ywgc_blind_carbon_copy" );
			$this->automatic_discount        = "yes" == get_option( "ywgc_auto_discount" );
			$this->restricted_usage          = "yes" == get_option( "ywgc_restricted_usage" );
			$this->allow_custom_design       = "yes" == get_option( "ywgc_custom_design" );
			$this->allow_template_design     = "yes" == get_option( "ywgc_template_design" );
			$this->allow_multiple_recipients = "yes" == get_option( "ywgc_allow_multi_recipients" );

			$this->order_cancelled_action = get_option( "ywgc_order_cancelled_action", 'nothing' );
			$this->order_refunded_action  = get_option( "ywgc_order_refunded_action", 'nothing' );
			$this->enable_pre_printed     = "yes" == get_option( "ywgc_enable_pre_printed" );

			$this->shop_name             = get_option( 'ywgc_shop_name', '' );
			$this->custom_image_max_size = get_option( 'ywgc_custom_image_max_size', 1 );
			$this->shop_logo_url         = get_option( "ywgc_shop_logo_url", YITH_YWGC_ASSETS_IMAGES_URL . 'default-giftcard-main-image.png' );
			$this->shop_logo_on_template = "yes" == get_option( "ywgc_shop_logo_on_gift_card" );

			$this->default_header_image_url = get_option( "ywgc_gift_card_header_url", YITH_YWGC_ASSETS_IMAGES_URL . 'default-giftcard-main-image.png' );
			$this->template_style           = get_option( "ywgc_template_style", 'style1' );

			$this->mandatory_recipient = "yes" == get_option( 'ywgc_recipient_mandatory', 'no' );

			$this->shipping_discount = "yes" == get_option( 'ywgc_enable_shipping_discount', 'no' );

			$this->initialize_products();
		}

		/**
		 * Retrieve if the gift cards should be updated on order refunded
		 *
		 * @return bool
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function change_status_on_refund() {
			return $this->disable_on_refund() || $this->dismiss_on_refund();
		}

		/**
		 * Retrieve if the gift cards should be updated on order cancelled
		 *
		 * @return bool
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function change_status_on_cancelled() {
			return $this->disable_on_cancelled() || $this->dismiss_on_cancelled();
		}

		/**
		 * Retrieve if a gift card should be set as dismissed if an order change its status
		 * to refunded
		 *
		 * @return bool
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function dismiss_on_refund() {
			return 'dismiss' == $this->order_refunded_action;
		}

		/**
		 * Retrieve if a gift card should be set as disabled if an order change its status
		 * to refunded
		 *
		 * @return bool
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function disable_on_refund() {
			return 'disable' == $this->order_refunded_action;
		}

		/**
		 * Retrieve if a gift card should be set as dismissed if an order change its status
		 * to cancelled
		 *
		 * @return bool
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function dismiss_on_cancelled() {
			return 'dismiss' == $this->order_cancelled_action;
		}

		/**
		 * Retrieve if a gift card should be set as disabled if an order change its status
		 * to cancelled
		 *
		 * @return bool
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function disable_on_cancelled() {
			return 'disable' == $this->order_cancelled_action;
		}

		public function on_plugin_init() {
			parent::on_plugin_init();
			$this->init_metabox();
		}

		public function init_metabox() {
			$args1 = array(
				'label'    => __( 'Gift card detail', 'yith-woocommerce-gift-cards' ),
				'pages'    => YWGC_CUSTOM_POST_TYPE_NAME,   //or array( 'post-type1', 'post-type2')
				'context'  => 'normal', //('normal', 'advanced', or 'side')
				'priority' => 'high',
				'tabs'     => array(
					'General' => array( //tab
						'label'  => __( 'General', 'yith-woocommerce-gift-cards' ),
						'fields' => array(

							'_ywgc_amount'             => array(
								'label'   => __( 'Purchased amount', 'yith-woocommerce-gift-cards' ),
								'desc'    => __( 'The amount purchased by the customer.', 'yith-woocommerce-gift-cards' ),
								'type'    => 'text',
								'private' => false,
								'std'     => ''
							),
							'_ywgc_amount_tax'         => array(
								'label'   => __( 'Purchased amount tax', 'yith-woocommerce-gift-cards' ),
								'desc'    => __( 'The tax amount purchased by the customer.', 'yith-woocommerce-gift-cards' ),
								'type'    => 'text',
								'private' => false,
								'std'     => ''
							),
							'_ywgc_amount_balance'     => array(
								'label'   => __( 'Current balance', 'yith-woocommerce-gift-cards' ),
								'desc'    => __( 'The current amount available for the customer.', 'yith-woocommerce-gift-cards' ),
								'type'    => 'text',
								'private' => false,
								'std'     => ''
							),
							'_ywgc_amount_balance_tax' => array(
								'label'   => __( 'Current balance tax', 'yith-woocommerce-gift-cards' ),
								'desc'    => __( 'The current tax amount available for the customer.', 'yith-woocommerce-gift-cards' ),
								'type'    => 'text',
								'private' => false,
								'std'     => '',
//								'deps'    => array(
//									'ids'    => '_ywgc_is_digital',
//									'values' => 'yes',
//								),
							),
							'_ywgc_is_digital'         => array(
								'label'   => __( 'Digital', 'yith-woocommerce-gift-cards' ),
								'desc'    => __( 'Choose whether the gift card will be sent via email or like a physical product.', 'yith-woocommerce-gift-cards' ),
								'type'    => 'checkbox',
								'private' => false,
								'std'     => ''
							),
							'_ywgc_sender_name'        => array(
								'label'   => __( 'Sender name', 'yith-woocommerce-gift-cards' ),
								'desc'    => __( 'The sender name, if any, of the digital gift card.', 'yith-woocommerce-gift-cards' ),
								'type'    => 'text',
								'private' => false,
								'std'     => '',
								'css'     => 'width: 80px;',
								'deps'    => array(
									'ids'    => '_ywgc_is_digital',
									'values' => 'yes',
								),
							),
							'_ywgc_recipient'          => array(
								'label'   => __( 'Recipient email', 'yith-woocommerce-gift-cards' ),
								'desc'    => __( 'The recipient email address of the digital gift card.', 'yith-woocommerce-gift-cards' ),
								'type'    => 'text',
								'private' => false,
								'std'     => '',
								'deps'    => array(
									'ids'    => '_ywgc_is_digital',
									'values' => 'yes',
								),
							),
							'_ywgc_message'            => array(
								'label'   => __( 'Message', 'yith-woocommerce-gift-cards' ),
								'desc'    => __( 'The message attached to the gift card.', 'yith-woocommerce-gift-cards' ),
								'type'    => 'textarea',
								'private' => false,
								'std'     => '',
								'deps'    => array(
									'ids'    => '_ywgc_is_digital',
									'values' => 'yes',
								),
							),
							'_ywgc_delivery_date'      => array(
								'label'   => __( 'Delivery date', 'yith-woocommerce-gift-cards' ),
								'desc'    => __( 'The date when the digital gift card will be sent to the recipient.', 'yith-woocommerce-gift-cards' ),
								'type'    => 'datepicker',
								'private' => false,
								'std'     => '',
								'deps'    => array(
									'ids'    => '_ywgc_is_digital',
									'values' => 'yes',
								),
							),
						),
					),
				)
			);

			$metabox1 = YIT_Metabox( 'yit-metabox-id' );
			$metabox1->init( $args1 );

		}

		/**
		 * Register the custom post type
		 */
		public function init_post_type() {
			$args = array(
				'labels'        => array(
					'name'               => _x( 'Gift Cards', 'post type general name', 'yith-woocommerce-gift-cards' ),
					'singular_name'      => _x( 'Gift Card', 'post type singular name', 'yith-woocommerce-gift-cards' ),
					'menu_name'          => _x( 'Gift Cards', 'admin menu', 'yith-woocommerce-gift-cards' ),
					'name_admin_bar'     => _x( 'Gift Card', 'add new on admin bar', 'yith-woocommerce-gift-cards' ),
					'add_new'            => _x( 'Add New', 'book', 'yith-woocommerce-gift-cards' ),
					'add_new_item'       => __( 'Add New Gift Card', 'yith-woocommerce-gift-cards' ),
					'new_item'           => __( 'New Gift Card', 'yith-woocommerce-gift-cards' ),
					'edit_item'          => __( 'Edit Gift Card', 'yith-woocommerce-gift-cards' ),
					'view_item'          => __( 'View Gift Card', 'yith-woocommerce-gift-cards' ),
					'all_items'          => __( 'All gift cards', 'yith-woocommerce-gift-cards' ),
					'search_items'       => __( 'Search gift cards', 'yith-woocommerce-gift-cards' ),
					'parent_item_colon'  => __( 'Parent gift cards:', 'yith-woocommerce-gift-cards' ),
					'not_found'          => __( 'No gift cards found.', 'yith-woocommerce-gift-cards' ),
					'not_found_in_trash' => __( 'No gift cards found in Trash.', 'yith-woocommerce-gift-cards' )
				),
				'label'         => __( 'Gift Cards', 'yith-woocommerce-gift-cards' ),
				'description'   => __( 'Gift Cards', 'yith-woocommerce-gift-cards' ),
				// Features this CPT supports in Post Editor
				'supports'      => array( 'title' ),
				'hierarchical'  => false,
				'public'        => false,
				'show_ui'       => true,
//				'show_in_admin_bar'   => true,
//				'show_in_menu'        => true,
				'menu_position' => 9,
				'can_export'    => false,
				'has_archive'   => false,
				'menu_icon'     => 'dashicons-clipboard',
				'query_var'     => false,
			);

			// Registering your Custom Post Type
			register_post_type( YWGC_CUSTOM_POST_TYPE_NAME, $args );


		}

		/**
		 * Checks for YWGC_Gift_Card_Premium instance
		 *
		 * @param object $obj the object to check
		 *
		 * @return bool obj is an instance of YWGC_Gift_Card_Premium
		 */
		public function instanceof_giftcard( $obj ) {
			return $obj instanceof YWGC_Gift_Card_Premium;
		}

		/**
		 * Retrieve a gift card product instance from the gift card code
		 *
		 * @param $code string the card code to search for
		 *
		 * @return YWGC_Gift_Card_Premium
		 */
		public function get_gift_card_by_code( $code ) {
			/*if ( ! is_string ( $code ) ) {
				return null;
			}*/

			return new YWGC_Gift_Card_Premium( array( 'gift_card_number' => $code ) );
		}


		public function get_postdated_gift_cards( $send_date ) {
			$args = array(
				'meta_query' => array(
					array(
						'key'     => YWGC_META_GIFT_CARD_DELIVERY_DATE,
						'value'   => $send_date,
						'compare' => '<=',
					),
					array(
						'key'     => YWGC_META_GIFT_CARD_SENT,
						'value'   => '1',
						'compare' => 'NOT EXISTS',
					),
				),

				'post_type'      => YWGC_CUSTOM_POST_TYPE_NAME,
				'fields'         => 'ids',
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
			);

			$ids = get_posts( $args );

			return $ids;
		}

		/**
		 * Retrieve the real picture to be used on the gift card preview
		 *
		 * @param YWGC_Gift_Card_Premium $object
		 *
		 * @return string
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 *
		 */
		public function get_gift_card_header_url( $object ) {
			//  Choose a valid gift card image header
			if ( $object->has_custom_design ) {
				//  There is a custom header image or a template chosen by the customer?
				if ( is_numeric( $object->design ) ) {
					//  a template was chosen, retrieve the picture associated
					$header_image_url = wp_get_attachment_image_url( $object->design, 'full' );
				} else {
					$header_image_url = YITH_YWGC_SAVE_URL . $object->design;
				}
			} else {
				if ( ! empty( $this->gift_card_header_url ) ) {
					$header_image_url = $this->gift_card_header_url;
				} else {
					$header_image_url = YITH_YWGC_ASSETS_IMAGES_URL . 'default-giftcard-main-image.png';
				}
			}

			return $header_image_url;
		}

		/**
		 * Retrieve the image to be used as a main image for the gift card
		 *
		 * @param WC_product $product
		 *
		 * @return string
		 */
		public function get_header_image_for_product( $product ) {

			$header_image_url = '';

			if ( $product && ( $product instanceof WC_Product ) ) {
				$custom_header = YITH_YWGC()->admin->get_manual_header_image( $product->id );
				if ( $custom_header ) {
					$image = wp_get_attachment_image_src( $custom_header, 'full' );

					$header_image_url = $image[0];

				} elseif ( has_post_thumbnail( $product->id ) ) {
					$image            = wp_get_attachment_image_src( get_post_thumbnail_id( $product->id ), 'full' );
					$header_image_url = $image[0];
				}
			}

			return $header_image_url;
		}

		public function get_default_header_image() {
			return $this->default_header_image_url ? $this->default_header_image_url : YITH_YWGC_ASSETS_IMAGES_URL . 'default-giftcard-main-image.png';;
		}

		/**
		 * Retrieve the default image, configured from the plugin settings, to be used as gift card header image
		 *
		 * @param YWGC_Gift_Card_Premium|WC_Product $obj
		 *
		 * @return mixed|string|void
		 */
		public function get_header_image( $obj = null ) {

			$header_image_url = '';
			if ( $obj instanceof WC_Product ) {

				$header_image_url = $this->get_header_image_for_product( $obj );
			} elseif ( $obj instanceof YWGC_Gift_Card_Premium ) {

				if ( $obj->has_custom_design ) {
					//  There is a custom header image or a template chosen by the customer?
					if ( is_numeric( $obj->design ) ) {
						//  a template was chosen, retrieve the picture associated
						$header_image_url = wp_get_attachment_image_url( $obj->design, 'full' );

					} else {
						$header_image_url = YITH_YWGC_SAVE_URL . $obj->design;

					}
				} else {
					$product          = wc_get_product( $obj->product_id );
					$header_image_url = $this->get_header_image_for_product( $product );
				}
			}

			if ( ! $header_image_url ) {
				$header_image_url = $this->get_default_header_image();
			}

			return $header_image_url;
		}

		/**
		 * Output a gift cards template filled with real data or with sample data to start editing it
		 * on product page
		 *
		 * @param WC_Product|YWGC_Gift_Card_Premium $object
		 * @param string                            $context
		 */
		public function preview_digital_gift_cards( $object, $context = 'shop' ) {

			if ( $object instanceof WC_Product ) {

				if ( $this->allow_product_as_present && ( 'gift-card' != $object->product_type ) ) {
					$header_image_url = $this->get_default_header_image();
				} else {
					$header_image_url = $this->get_header_image( $object );
				}
				// check if the admin set a default image for gift card
				$amount          = ( $object instanceof WC_Product_Simple ) ? $object->get_display_price() : 0;
				$formatted_price = wc_price( $amount );
				$gift_card_code  = "xxxx-xxxx-xxxx-xxxx";
				$message         = __( "Your message...", 'yith-woocommerce-gift-cards' );
			} else if ( $object instanceof YWGC_Gift_Card_Premium ) {

				$header_image_url = $this->get_header_image( $object );

				$amount          = $object->get_amount( true );
				$formatted_price = apply_filters( 'yith_ywgc_gift_card_template_amount', wc_price( $amount ), $object );

				$gift_card_code = $object->gift_card_number;
				$message        = $object->message;
			}

			wc_get_template( 'yith-gift-cards/gift-card-template.php',
				array(
					'template_style'   => $this->template_style,
					'company_logo_url' => $this->shop_logo_on_template ? $this->shop_logo_url : '',
					'header_image_url' => $header_image_url,
					'formatted_price'  => $formatted_price,
					'gift_card_code'   => $gift_card_code,
					'message'          => $message,
				),
				'',
				trailingslashit( YITH_YWGC_TEMPLATES_DIR ) );
		}
	}
}