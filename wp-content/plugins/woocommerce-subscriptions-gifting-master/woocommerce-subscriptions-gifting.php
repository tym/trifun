<?php
/**
 * Plugin Name: WooCommerce Subscriptions Gifting
 * Plugin URI: https://github.com/prospress/woocommerce-subscriptions-gifting
 * Description: Allow customers to buy a subscription product for someone else, then share subscription management between the purchaser and recipient.
 * Author: Prospress Inc.
 * Author URI: http://prospress.com/
 * Version: 1.0
 * License: GPLv2
 *
 * GitHub Plugin URI: Prospress/woocommerce-subscriptions-gifting
 * GitHub Branch: master
 *
 * Copyright 2016 Prospress, Inc.  (email : freedoms@prospress.com)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package		WooCommerce Subscriptions Gifting
 * @author		James Allan
 * @since		1.0
 */

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if ( ! function_exists( 'woothemes_queue_update' ) || ! function_exists( 'is_woocommerce_active' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

/**
 * Check if WooCommerce and Subscriptions are active.
 */
if ( ! is_woocommerce_active() || version_compare( get_option( 'woocommerce_db_version' ), WCS_Gifting::$wc_minimum_supported_version, '<' ) ) {
	add_action( 'admin_notices', 'WCS_Gifting::plugin_dependency_notices' );
	return;
}

if ( ! is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) || version_compare( get_option( 'woocommerce_subscriptions_active_version' ), WCS_Gifting::$wcs_minimum_supported_version, '<' ) ) {
	add_action( 'admin_notices', 'WCS_Gifting::plugin_dependency_notices' );
	return;
}

require_once( 'includes/class-wcsg-product.php' );

require_once( 'includes/class-wcsg-cart.php' );

require_once( 'includes/class-wcsg-checkout.php' );

require_once( 'includes/class-wcsg-recipient-management.php' );

require_once( 'includes/class-wcsg-recipient-details.php' );

require_once( 'includes/class-wcsg-email.php' );

require_once( 'includes/class-wcsg-download-handler.php' );

require_once( 'includes/class-wcsg-admin.php' );

require_once( 'includes/class-wcsg-recipient-addresses.php' );

class WCS_Gifting {

	public static $plugin_file = __FILE__;

	public static $wc_minimum_supported_version  = '2.3';
	public static $wcs_minimum_supported_version = '2.0';
	public static $wcm_minimum_supported_version = '1.4';

	/**
	 * Setup hooks & filters, when the class is initialised.
	 */
	public static function init() {

		register_activation_hook( __FILE__, __CLASS__ . '::wcsg_install' );

		add_action( 'wp_enqueue_scripts', __CLASS__ . '::gifting_scripts' );

		// Needs to run after Subscriptions has loaded its dependant classes
		add_action( 'plugins_loaded', __CLASS__ . '::load_dependant_classes' , 11 );

		add_action( 'init', __CLASS__ . '::maybe_flush_rewrite_rules' );

		add_action( 'wc_get_template', __CLASS__ . '::get_recent_orders_template', 1 , 3 );

		add_filter( 'wcs_renewal_order_meta_query', __CLASS__ . '::remove_renewal_order_meta_query', 11 );

		add_action( 'admin_enqueue_scripts',  __CLASS__ . '::admin_scripts' );
	}

	/**
	 * Register/queue admin scripts.
	 */
	public static function admin_scripts() {
		global $post;

		$screen = get_current_screen();

		if ( 'shop_subscription' == $screen->id && WCS_Gifting::is_gifted_subscription( $post->ID ) ) {

			wp_register_script( 'wcs_gifting_admin', plugins_url( '/js/wcsg-admin.js', __FILE__ ), array( 'jquery', 'wc-admin-order-meta-boxes' ) );

			wp_localize_script( 'wcs_gifting_admin', 'wcs_gifting', array(
				'revoke_download_permission_nonce' => wp_create_nonce( 'revoke_download_permission' ),
				'ajax_url'                         => admin_url( 'admin-ajax.php' ),
				)
			);

			wp_enqueue_script( 'wcs_gifting_admin' );
		}
	}

	/**
	 * Don't carry recipient meta data to renewal orders
	 */
	public static function remove_renewal_order_meta_query( $order_meta_query ) {
		$order_meta_query .= " AND `meta_key` NOT IN ('_recipient_user')";
		return $order_meta_query;
	}

	/**
	 * loads classes after plugins for classes dependant on other plugin files
	 */
	public static function load_dependant_classes() {
		require_once( 'includes/class-wcsg-query.php' );

		if ( class_exists( 'WC_Memberships_Membership_Plan' ) ) {

			if ( version_compare( get_option( 'wc_memberships_version' ), WCS_Gifting::$wcm_minimum_supported_version, '>=' ) ) {
				require_once( 'includes/class-wcsg-memberships-integration.php' );
			} else {
				add_action( 'admin_notices', 'WCS_Gifting::plugin_dependency_notices' );
			}
		}
	}

	/**
	 * Register/queue frontend scripts.
	 */
	public static function gifting_scripts() {
		wp_register_script( 'woocommerce_subscriptions_gifting', plugins_url( '/js/wcs-gifting.js', __FILE__ ), array( 'jquery' ) );
		wp_enqueue_script( 'woocommerce_subscriptions_gifting' );
	}

	/**
	 * Determines if an email address belongs to the current user,
	 * @param string Email address.
	 * @return bool Returns whether the email address belongs to the current user.
	 */
	public static function email_belongs_to_current_user( $email ) {
		$current_user_email = wp_get_current_user()->user_email;
		return $current_user_email == $email;
	}

	/**
	 * Validates an array of recipient emails scheduling error notices if an error is found.
	 * @param array An array of recipient email addresses.
	 * @return bool returns whether any errors have occurred.
	 */
	public static function validate_recipient_emails( $recipients ) {
		$invalid_email_found = false;
		$self_gifting_found  = false;
		$current_user_email  = wp_get_current_user()->user_email;
		if ( is_array( $recipients ) ) {
			foreach ( $recipients as $key => $recipient ) {
				$cleaned_recipient = sanitize_email( $recipient );
				if ( $recipient == $cleaned_recipient && is_email( $cleaned_recipient ) ) {
					if ( ! $self_gifting_found && self::email_belongs_to_current_user( $cleaned_recipient ) ) {
						wc_add_notice( __( 'Please enter someone else\'s email address.', 'woocommerce-subscriptions-gifting' ), 'error' );
						$self_gifting_found = true;
					}
				} else if ( ! empty( $recipient ) && ! $invalid_email_found ) {
					wc_add_notice( __( ' Invalid email address.', 'woocommerce-subscriptions-gifting' ), 'error' );
					$invalid_email_found = true;
				}
			}
		}
		return ! ( $invalid_email_found || $self_gifting_found );
	}

	/**
	 * Attaches recipient information to a subscription cart item key when the recipient information is updated. If necessary
	 * combines cart items if the same cart key exists in the cart.
	 * @param object|item The item in the cart to be updated
	 * @param string|key
	 * @param new_recipient_data The new recipient information for the item
	 */
	public static function update_cart_item_key( $item, $key, $new_recipient_data ) {
		if ( empty( $item['wcsg_gift_recipients_email'] ) || $item['wcsg_gift_recipients_email'] != $new_recipient_data ) {

			$cart_item_data = self::add_cart_item_data( $item, $key, $new_recipient_data );
			$new_key        = WC()->cart->generate_cart_id( $item['product_id'], $item['variation_id'], $item['variation'], $cart_item_data );
			$cart_item      = WC()->cart->get_cart_item( $new_key );

			if ( $new_key != $key ) {

				if ( ! empty( $cart_item ) ) {
					$combined_quantity = $item['quantity'] + $cart_item['quantity'];
					WC()->cart->cart_contents[ $new_key ]['quantity'] = $combined_quantity;
					unset( WC()->cart->cart_contents[ $key ] );
				} else { // there is no item in the cart with the same new key

					$item_cart_position = array_search( $key, array_keys( WC()->cart->cart_contents ) );
					WC()->cart->cart_contents = array_merge( array_slice( WC()->cart->cart_contents, 0, $item_cart_position, true ),
						array( $new_key => WC()->cart->cart_contents[ $key ] ),
						array_slice( WC()->cart->cart_contents, $item_cart_position, count( WC()->cart->cart_contents ), true )
					);

					if ( empty( $new_recipient_data ) ) {
						unset( WC()->cart->cart_contents[ $new_key ]['wcsg_gift_recipients_email'] );
					} else {
						WC()->cart->cart_contents[ $new_key ]['wcsg_gift_recipients_email'] = $new_recipient_data;
					}

					unset( WC()->cart->cart_contents[ $key ] );
				}
			}
		}
	}

	/**
	 * Populates the cart item data that will be used by WooCommerce to generate a unique ID for the cart item. That is to
	 * avoid merging different products when they aren't the same. Previously the resubscribe status was ignored.
	 *
	 * @param array  $item               A cart item with all its data
	 * @param string $key                A cart item key
	 * @param array  $new_recipient_data email address of the new recipient
	 */
	private static function add_cart_item_data( $item, $key, $new_recipient_data ) {
		// start with a clean slate
		$cart_item_data = array();

		// Add the recipient email
		if ( ! empty( $new_recipient_data ) ) {
			$cart_item_data = array( 'wcsg_gift_recipients_email' => $new_recipient_data );
		}

		// Add resubscribe data
		if ( array_key_exists( 'subscription_resubscribe', $item ) ) {
			$cart_item_data = array_merge( $cart_item_data, array( 'subscription_resubscribe' => $item['subscription_resubscribe'] ) );
		}

		$cart_item_data = apply_filters( 'wcsg_cart_item_data', $cart_item_data, $item, $key, $new_recipient_data );

		return $cart_item_data;
	}

	/**
	 * Install wcsg
	 */
	public static function wcsg_install() {
		if ( 'false' === get_option( 'wcsg_flush_rewrite_rules_flag', 'false' ) ) {
			add_option( 'wcsg_flush_rewrite_rules_flag', 'true' );
		}
	}

	/**
	 * Flush rewrite rules if they haven't been flushed since plugin activation
	 */
	public static function maybe_flush_rewrite_rules() {
		if ( 'true' === get_option( 'wcsg_flush_rewrite_rules_flag', 'false' ) ) {
			flush_rewrite_rules();
			delete_option( 'wcsg_flush_rewrite_rules_flag' );
		}

	}
	/**
	 * Generates an array of arguments used to create the recipient email html fields
	 * @return array | email_field_args A set of html attributes
	 */
	public static function get_recipient_email_field_args( $email ) {
		$email_field_args = array(
			'placeholder'      => 'recipient@example.com',
			'class'            => array( 'woocommerce_subscriptions_gifting_recipient_email' ),
			'style_attributes' => array(),
		);

		if ( ! empty( $email ) && ( WCS_Gifting::email_belongs_to_current_user( $email ) || ! is_email( $email ) ) ) {
			array_push( $email_field_args['class'], 'woocommerce-invalid' );
		}

		if ( empty( $email ) ) {
			array_push( $email_field_args['style_attributes'], 'display: none' );
		}
		return apply_filters( 'wcsg_recipient_email_field_args', $email_field_args, $email );
	}

	/**
	 * Overrides the default recent order template for gifted subscriptions
	 */
	public static function get_recent_orders_template( $located, $template_name, $args ) {
		if ( 'myaccount/related-orders.php' == $template_name ) {
			$subscription = $args['subscription'];
			if ( WCS_Gifting::is_gifted_subscription( $subscription ) ) {
				$located = wc_locate_template( 'related-orders.php', '', plugin_dir_path( WCS_Gifting::$plugin_file ) . 'templates/' );
			}
		}
		return $located;
	}

	/**
	 * Returns a combination of the customer's first name, last name and email depending on what the customer has set.
	 *
	 * @param int $user_id The ID of the customer user
	 * @param bool $strip_tags Whether to strip HTML tags in user name (defaulted to false)
	 */
	public static function get_user_display_name( $user_id, $strip_tags = false ) {

		$user = get_user_by( 'id', $user_id );
		$name = '';

		if ( ! empty( $user->first_name ) ) {
			$name = $user->first_name . ( ( ! empty( $user->last_name ) ) ? ' ' . $user->last_name : '' ) . ' (' . make_clickable( $user->user_email ) . ')';
		} else {
			$name = make_clickable( $user->user_email );
		}

		if ( $strip_tags ) {
			$name = wp_strip_all_tags( $name );
		}

		return $name;
	}

	/**
	 * Displays plugin dependency notices if required plugins are inactive or the installed version is less than a
	 * supported version.
	 */
	public static function plugin_dependency_notices() {

		if ( ! is_woocommerce_active() ) {
			self::output_plugin_dependency_notice( 'WooCommerce' );
		} else if ( version_compare( get_option( 'woocommerce_db_version' ), WCS_Gifting::$wc_minimum_supported_version, '<' ) ) {
			self::output_plugin_dependency_notice( 'WooCommerce', WCS_Gifting::$wc_minimum_supported_version );
		}

		if ( ! is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
			self::output_plugin_dependency_notice( 'WooCommerce Subscriptions' );
		} else if ( version_compare( get_option( 'woocommerce_subscriptions_active_version' ), WCS_Gifting::$wcs_minimum_supported_version, '<' ) ) {
			self::output_plugin_dependency_notice( 'WooCommerce Subscriptions', WCS_Gifting::$wcs_minimum_supported_version );
		}

		if ( is_plugin_active( 'woocommerce-memberships/woocommerce-memberships.php' ) && version_compare( get_option( 'wc_memberships_version' ), WCS_Gifting::$wcm_minimum_supported_version, '<' ) ) {
			self::output_plugin_dependency_notice( 'WooCommerce Memberships', WCS_Gifting::$wcm_minimum_supported_version );
		}

	}

	/**
	 * Prints a plugin dependency admin notice. If a required version is supplied an invalid version notice is printed,
	 * otherwise an inactive plugin notice is printed.
	 *
	 * @param string $plugin_name The plugin name.
	 * @param string $required_version The minimum supported version of the plugin.
	 */
	public static function output_plugin_dependency_notice( $plugin_name, $required_version = false ) {

		if ( current_user_can( 'activate_plugins' ) ) {
			if ( $required_version ) { ?>
				<div id="message" class="error">
					<p><?php
					if ( 'WooCommerce Memberships' == $plugin_name ) {
						// translators: 1$-2$: opening and closing <strong> tags, 3$ plugin name, 4$ required plugin version, 5$-6$: opening and closing link tags, leads to plugins.php in admin, 7$: line break, 8$-9$ Opening and closing small tags
						printf( esc_html__( '%1$sWooCommerce Subscriptions Gifting Membership integration is inactive.%2$s In order to integrate with WooCommerce Memberships, WooCommerce Subscriptions Gifting requires %3$s %4$s or newer. %5$sPlease update &raquo;%6$s %7$s%8$sNote: All other WooCommerce Subscriptions Gifting features will remain available, however purchasing membership plans for recipients will fail to grant the membership to the gift recipient.%9$s', 'woocommerce-subscriptions-gifting' ), '<strong>', '</strong>', esc_html( $plugin_name ), esc_html( $required_version ), '<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">', '</a>', '</br>', '<small>', '</small>' );
					} else {
						// translators: 1$-2$: opening and closing <strong> tags, 3$ plugin name, 4$ required plugin version, 5$-6$: opening and closing link tags, leads to plugins.php in admin
						printf( esc_html__( '%1$sWooCommerce Subscriptions Gifting is inactive.%2$s This version of WooCommerce Subscriptions Gifting requires %3$s %4$s or newer. %5$sPlease update &raquo;%6$s', 'woocommerce-subscriptions-gifting' ), '<strong>', '</strong>', esc_html( $plugin_name ), esc_html( $required_version ), '<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">', '</a>' );
					} ?>
					</p>
				</div>
			<?php } else {
				switch ( $plugin_name ) {
					case 'WooCommerce Subscriptions':
						$plugin_url = 'http://www.woothemes.com/products/woocommerce-subscriptions/';
						break;
					case 'WooCommerce':
						$plugin_url = 'http://wordpress.org/extend/plugins/woocommerce/';
						break;
					default:
						$plugin_url = '';
				} ?>
				<div id="message" class="error">
					<p><?php
						// translators: 1$-2$: opening and closing <strong> tags, 3$ plugin name, 4$:opening link tag, leads to plugin product page, 5$-6$: opening and closing link tags, leads to plugins.php in admin
						printf( esc_html__( '%1$sWooCommerce Subscriptions Gifting is inactive.%2$s WooCommerce Subscriptions Gifting requires the %4$s%3$s%6$s plugin to be active to work correctly. Please %5$sinstall & activate %3$s &raquo;%6$s',  'woocommerce-subscriptions-gifting' ), '<strong>', '</strong>', esc_html( $plugin_name ) , '<a href="' . esc_url( $plugin_url ) . '">', '<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">', '</a>' ); ?>
					</p>
				</div>
			<?php }
		}
	}

	/**
	 * Checks whether a subscription is a gifted subscription.
	 *
	 * @param int|WC_Subscription $subscription either a subscription object or subscription's ID.
	 * @return bool
	 */
	public static function is_gifted_subscription( $subscription ) {

		if ( ! $subscription instanceof WC_Subscription ) {
			$subscription = wcs_get_subscription( $subscription );
		}

		return wcs_is_subscription( $subscription ) && ! empty( $subscription->recipient_user ) && is_numeric( $subscription->recipient_user );
	}

	/**
	 * Returns a list of all order item ids and thier containing order ids that have been purchased for a recipient.
	 *
	 * @param int $recipient_user_id
	 * @return array
	 */
	public static function get_recipient_order_items( $recipient_user_id ) {
		global $wpdb;

			return $wpdb->get_results(
				$wpdb->prepare( "
					SELECT o.order_id, i.order_item_id
					FROM {$wpdb->prefix}woocommerce_order_itemmeta AS i
					INNER JOIN {$wpdb->prefix}woocommerce_order_items as o
					ON i.order_item_id=o.order_item_id
					WHERE meta_key = 'wcsg_recipient'
					AND meta_value = %s",
				'wcsg_recipient_id_' . $recipient_user_id ),
				ARRAY_A
			);
	}

	/**
	 * Returns the user's shipping address.
	 *
	 * @param int $user_id
	 * @return array
	 */
	public static function get_users_shipping_address( $user_id ) {
		return array(
			'first_name' => get_user_meta( $user_id, 'shipping_first_name', true ),
			'last_name'  => get_user_meta( $user_id, 'shipping_last_name', true ),
			'company'    => get_user_meta( $user_id, 'shipping_company', true ),
			'address_1'  => get_user_meta( $user_id, 'shipping_address_1', true ),
			'address_2'  => get_user_meta( $user_id, 'shipping_address_2', true ),
			'city'       => get_user_meta( $user_id, 'shipping_city', true ),
			'state'      => get_user_meta( $user_id, 'shipping_state', true ),
			'postcode'   => get_user_meta( $user_id, 'shipping_postcode', true ),
			'country'    => get_user_meta( $user_id, 'shipping_country', true ),
		);
	}

	/**
	 * Determines if an order contains a gifted subscription.
	 *
	 * @param mixed $order the order id or order object to check
	 * @return bool
	 */
	public static function order_contains_gifted_subscription( $order ) {

		if ( ! is_object( $order ) ) {
			$order = wc_get_order( $order );
		}

		if ( ! is_a( $order, 'WC_Order' ) ) {
			return false;
		}

		$contains_gifted_subscription = false;

		foreach ( wcs_get_subscriptions_for_order( $order ) as $subscription_id => $subscription ) {

			if ( self::is_gifted_subscription( $subscription ) ) {
				$contains_gifted_subscription = true;
				break;
			}
		}

		return $contains_gifted_subscription;
	}

	/**
	 * Retrieves the user id of the recipient stored in order item meta.
	 *
	 * @param mixed $order_item the order item to check
	 * @return mixed bool|int The recipient user id or false if the order item is not gifted
	 */
	public static function get_order_item_recipient_user_id( $order_item ) {
		return ( isset( $order_item['item_meta']['wcsg_recipient'] ) ) ? substr( $order_item['item_meta']['wcsg_recipient'][0], strlen( 'wcsg_recipient_id_' ) ) : false;
	}

}
WCS_Gifting::init();
