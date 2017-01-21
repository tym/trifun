<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


if ( ! class_exists( 'YITH_WooCommerce_Gift_Cards' ) ) {

	/**
	 *
	 * @class   YITH_WooCommerce_Gift_Cards
	 * @package Yithemes
	 * @since   1.0.0
	 * @author  Your Inspiration Themes
	 */
	class YITH_WooCommerce_Gift_Cards {
		/**
		 * @var YITH_YWGC_Backend|YITH_YITH_Backend_Premium The instance for backend features and methods
		 */
		public $admin;

		/**
		 * @var YITH_YITH_Frontend|YITH_YWGC_Frontend_Premium instance for frontend features and methods
		 */
		public $frontend;

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

			/**
			 * Store if the plugin version is prior than 1.5.0 that is threshold of much plugin changes
			 */

		}

		public function prior_than_150() {
			return version_compare( YITH_YWGC_VERSION, '1.5.0', '<' );
		}

		public function includes() {
			require_once( YITH_YWGC_DIR . 'lib/class-yith-ywgc-product.php' );
			require_once( YITH_YWGC_DIR . 'lib/class-yith-ywgc-cart-checkout.php' );
			require_once( YITH_YWGC_DIR . 'lib/class-yith-ywgc-emails.php' );
			require_once( YITH_YWGC_DIR . 'lib/class-yith-ywgc-gift-cards-table.php' );

			if ( 'yes' === get_option( 'ywgc_enable_shipping_discount', 'no' ) ) {
				require_once( YITH_YWGC_DIR . 'lib/class-yith-ywgc-shipping.php' );
			}

			/**
			 * Include third-party integration classes
			 */

			//  YITH Dynamic Pricing
			defined( 'YITH_YWDPD_VERSION' ) && require_once( YITH_YWGC_DIR . 'lib/third-party/class-ywgc-dynamic-pricing.php' );

			//  YITH Points and Rewards
			defined( 'YITH_YWPAR_VERSION' ) && require_once( YITH_YWGC_DIR . 'lib/third-party/class-ywgc-points-and-rewards.php' );

			//  YITH Multi Vendor
			defined( 'YITH_WPV_PREMIUM' ) && require_once( YITH_YWGC_DIR . 'lib/third-party/class-ywgc-multi-vendor-module.php' );

			//  Aelia Currency Switcher
			class_exists( 'WC_Aelia_CurrencySwitcher' ) && require_once( YITH_YWGC_DIR . 'lib/third-party/class-ywgc-AeliaCS-module.php' );

			defined( 'YITH_WCQV_PREMIUM' ) && require_once( YITH_YWGC_DIR . 'lib/third-party/class-ywgc-general-integrations.php' );
		}

		public function init_hooks() {
			/**
			 * Do some stuff on plugin init
			 */
			add_action( 'init', array( $this, 'on_plugin_init' ) );

			/**
			 * Hide the temporary gift card product from being shown on shop page
			 */
			add_action( 'woocommerce_product_query', array( $this, 'hide_from_shop_page' ), 10, 1 );

			add_filter( 'yith_plugin_status_sections', array( $this, 'set_plugin_status' ) );
		}


		/**
		 * Hide the temporary gift card product from being shown on shop page
		 *
		 * @param WP_Query $query The current query
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function hide_from_shop_page( $query ) {
			$default_gift_card = get_option( YWGC_PRODUCT_PLACEHOLDER, - 1 );

			if ( $default_gift_card > 0 ) {
				$query->set( 'post__not_in', array( $default_gift_card ) );
			}
		}

		/**
		 * Execute update on data used by the plugin that has been changed passing
		 * from a DB version to another
		 */
		public function update_database() {

			/**
			 * Init DB version if not exists
			 */
			$db_version = get_option( YWGC_DB_VERSION_OPTION );

			if ( ! $db_version ) {
				//  Update from previous version where the DB option was not set
				global $wpdb;

				//  Update metakey from YITH Gift Cards 1.0.0
				$query = "Update {$wpdb->prefix}woocommerce_order_itemmeta
                        set meta_key = '" . YWGC_META_GIFT_CARD_POST_ID . "'
                        where meta_key = 'gift_card_post_id'";
				$wpdb->query( $query );

				$db_version = '1.0.0';
			}

			/**
			 * Start the database update step by step
			 */
			if ( version_compare( $db_version, '1.0.0', '<=' ) ) {

				//  Set gift card placeholder with catalog visibility equal to "hidden"
				$placeholder_id = get_option( YWGC_PRODUCT_PLACEHOLDER );

				update_post_meta( $placeholder_id, '_visibility', 'hidden' );

				$db_version = '1.0.1';
			}

			if ( version_compare( $db_version, '1.0.1', '<=' ) ) {

				//  extract the user_id from the order where a gift card is applied and register
				//  it so the gift card will be shown on my-account

				$args = array(
					'numberposts' => - 1,
					'meta_key'    => YWGC_META_GIFT_CARD_ORDERS,
					'post_type'   => YWGC_CUSTOM_POST_TYPE_NAME,
					'post_status' => 'any',
				);

				//  Retrieve the gift cards matching the criteria
				$posts = get_posts( $args );

				foreach ( $posts as $post ) {
					$gift_card = new YWGC_Gift_Card_Premium( array( 'ID' => $post->ID ) );

					if ( ! $gift_card->exists() ) {
						continue;
					}

					/** @var WC_Order $order */
					$orders = $gift_card->get_registered_orders();
					foreach ( $orders as $order_id ) {
						$order = wc_get_order( $order_id );
						if ( $order ) {
							$gift_card->register_user( $order->customer_user );
						}
					}
				}

				$db_version = '1.0.2';  //  Continue to next step...
			}

			//  Update the current DB version
			update_option( YWGC_DB_VERSION_OPTION, YITH_YWGC_DB_CURRENT_VERSION );
		}

		/**
		 *  Execute all the operation need when the plugin init
		 */
		public function on_plugin_init() {
			$this->init_post_type();

			$this->init_plugin();

			$this->update_database();
		}

		/**
		 * Initialize plugin data and shard instances
		 */
		public function init_plugin() {
			//nothing to do
		}

		/**
		 * Register the custom post type
		 */
		public function init_post_type() {
			$args = array(
				'label'               => __( 'Gift Cards', 'yith-woocommerce-gift-cards' ),
				'description'         => __( 'Gift Cards', 'yith-woocommerce-gift-cards' ),
				//'labels' => $labels,
				// Features this CPT supports in Post Editor
				'supports'            => array(
					//'title',
					'editor',
					//'author',
				),
				'hierarchical'        => false,
				'public'              => false,
				'show_ui'             => false,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => false,
				'menu_position'       => 9,
				'can_export'          => false,
				'has_archive'         => false,
				'exclude_from_search' => true,
				'menu_icon'           => 'dashicons-clipboard',
				'query_var'           => false,
			);

			// Registering your Custom Post Type
			register_post_type( YWGC_CUSTOM_POST_TYPE_NAME, $args );
		}

		/**
		 * Checks for YITH_YWGC_Gift_Card instance
		 *
		 * @param object $obj the object to check
		 *
		 * @return bool obj is an instance of YITH_YWGC_Gift_Card
		 */
		public function instanceof_giftcard( $obj ) {
			return $obj instanceof YITH_YWGC_Gift_Card;
		}

		/**
		 * Retrieve a gift card product instance from the gift card code
		 *
		 * @param $code string the card code to search for
		 *
		 * @return YITH_YWGC_Gift_Card
		 */
		public function get_gift_card_by_code( $code ) {
			/*if ( ! is_string ( $code ) ) {
				return null;
			}
*/
			$args = array( 'gift_card_number' => $code );

			return new YITH_YWGC_Gift_Card( $args );
		}

		/**
		 * Generate a new gift card code
		 *
		 *
		 * @return bool
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function generate_gift_card_code() {

			//  Create a new gift card number

			//http://stackoverflow.com/questions/3521621/php-code-for-generating-decent-looking-coupon-codes-mix-of-alphabets-and-number
			$code = strtoupper( substr( base_convert( sha1( uniqid( mt_rand() ) ), 16, 36 ), 0, 16 ) );

			$code = sprintf( "%s-%s-%s-%s",
				substr( $code, 0, 4 ),
				substr( $code, 4, 4 ),
				substr( $code, 8, 4 ),
				substr( $code, 12, 4 )
			);

			return $code;
		}
	}
}

