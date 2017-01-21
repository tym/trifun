<?php
class WCSG_Recipient_Management {

	/**
	 * Setup hooks & filters, when the class is initialised.
	 */
	public static function init() {
		add_filter( 'wcs_get_users_subscriptions', __CLASS__ . '::get_users_subscriptions', 1, 2 );

		add_action( 'woocommerce_order_details_after_customer_details', __CLASS__ . '::gifting_information_after_customer_details', 1 );

		add_filter( 'wcs_view_subscription_actions', __CLASS__ . '::add_recipient_actions', 11, 2 );

		//we want to handle the changing of subscription status before Subscriptions core
		add_action( 'init', __CLASS__ . '::change_user_recipient_subscription', 99 );

		add_filter( 'wcs_can_user_put_subscription_on_hold' , __CLASS__ . '::recipient_can_suspend', 1, 2 );

		add_filter( 'woocommerce_subscription_related_orders', __CLASS__ . '::maybe_remove_parent_order', 11, 4 );

		add_filter( 'user_has_cap', __CLASS__ . '::grant_recipient_capabilities', 20, 3 );

		add_action( 'delete_user_form', __CLASS__ . '::maybe_display_delete_recipient_warning', 10 );

		add_action( 'delete_user', __CLASS__ . '::maybe_remove_recipient', 10, 1 );

		add_action( 'woocommerce_add_order_item_meta', __CLASS__ . '::maybe_add_recipient_order_item_meta', 10, 2 );

		add_filter( 'woocommerce_attribute_label', __CLASS__ . '::format_recipient_meta_label', 10, 2 );

		add_filter( 'woocommerce_order_item_display_meta_value', __CLASS__ . '::format_recipient_meta_value', 10 );

		add_filter( 'woocommerce_hidden_order_itemmeta', __CLASS__ . '::hide_recipient_order_item_meta', 10, 1 );

		add_action( 'woocommerce_before_order_itemmeta', __CLASS__ . '::display_recipient_meta_admin', 10, 1 );

		add_action( 'woocommerce_subscription_status_updated', __CLASS__ . '::maybe_update_recipient_role', 10, 2 );
	}

	/**
	 * Grant capabilities for subscriptions and related orders to recipients
	 *
	 * @param array $allcaps An array of user capabilities
	 * @param array $caps The capability being questioned
	 * @param array $args Additional arguments related to the capability
	 * @return array
	 */
	public static function grant_recipient_capabilities( $allcaps, $caps, $args ) {
		if ( isset( $caps[0] ) ) {
			switch ( $caps[0] ) {
				case 'view_order' :
					$user_id = $args[1];
					$order   = wc_get_order( $args[2] );

					if ( $order ) {
						if ( 'shop_subscription' == get_post_type( $args[2] ) && $user_id == $order->recipient_user ) {
							$allcaps['view_order'] = true;
						} else if ( wcs_order_contains_renewal( $order ) ) {
							$subscriptions = wcs_get_subscriptions_for_renewal_order( $order );
							foreach ( $subscriptions as $subscription ) {
								if ( $user_id == $subscription->recipient_user ) {
									$allcaps['view_order'] = true;
									break;
								}
							}
						}
					}
				break;
				case 'pay_for_order' :
					$user_id = $args[1];
					$order   = wc_get_order( $args[2] );

					if ( $order && wcs_order_contains_subscription( $order, 'any' ) ) {
						$subscriptions = wcs_get_subscriptions_for_renewal_order( $order );

						foreach ( $subscriptions as $subscription ) {
							if ( $user_id == $subscription->recipient_user ) {
								$allcaps['pay_for_order'] = true;
								break;
							}
						}
					}
				break;
			}
		}
		return $allcaps;
	}

	/**
	 * Adds available user actions to the subscription recipient
	 *
	 * @param array|actions An array of actions the user can peform
	 * @param object|subscription
	 * @return array|actions An updated array of actions the user can perform on a gifted subscription
	 */
	public static function add_recipient_actions( $actions, $subscription ) {

		if ( WCS_Gifting::is_gifted_subscription( $subscription ) && wp_get_current_user()->ID == $subscription->recipient_user ) {

			$recipient_actions = array();
			$current_status    = $subscription->get_status();
			$recipient_user    = get_user_by( 'id', $subscription->recipient_user );

			$admin_with_suspension_disallowed = ( current_user_can( 'manage_woocommerce' ) && '0' === get_option( WC_Subscriptions_Admin::$option_prefix . '_max_customer_suspensions', '0' ) ) ? true : false;

			if ( $subscription->can_be_updated_to( 'on-hold' ) && wcs_can_user_put_subscription_on_hold( $subscription, $recipient_user ) && ! $admin_with_suspension_disallowed ) {
				$recipient_actions['suspend'] = array(
					'url'  => self::get_recipient_change_status_link( $subscription->id, 'on-hold', $recipient_user->ID, $current_status ),
					'name' => __( 'Suspend', 'woocommerce-subscriptions-gifting' ),
				);
			} else if ( $subscription->can_be_updated_to( 'active' ) && ! $subscription->needs_payment() ) {
				$recipient_actions['reactivate'] = array(
					'url'  => self::get_recipient_change_status_link( $subscription->id, 'active', $recipient_user->ID, $current_status ),
					'name' => __( 'Reactivate', 'woocommerce-subscriptions-gifting' ),
				);
			}

			if ( $subscription->can_be_updated_to( 'cancelled' ) ) {
				$recipient_actions['cancel'] = array(
					'url'  => self::get_recipient_change_status_link( $subscription->id, 'cancelled', $recipient_user->ID, $current_status ),
					'name' => __( 'Cancel', 'woocommerce-subscriptions-gifting' ),
				);
			}

			$actions = array_merge( $recipient_actions, $actions );

			//remove the ability for recipients to change the payment method.
			unset( $actions['change_payment_method'] );
		}
		return $actions;
	}

	/**
	 * Generates a link for the user to change the status of a subscription
	 *
	 * @param int|subscription_id
	 * @param string|status The status the recipient has requested to change the subscription to
	 * @param int|recipient_id
	 */
	private static function get_recipient_change_status_link( $subscription_id, $status, $recipient_id, $current_status ) {

		$action_link = add_query_arg( array( 'subscription_id' => $subscription_id, 'change_subscription_to' => $status, 'wcsg_requesting_recipient_id' => $recipient_id ) );
		$action_link = wp_nonce_url( $action_link, $subscription_id . $current_status );

		return $action_link;
	}

	/**
	 * Checks if a status change request is by the recipient, and if it is,
	 * validate the request and proceed to change to the subscription.
	 */
	public static function change_user_recipient_subscription() {
		//check if the request is being made from the recipient (wcsg_requesting_recipient_id is set)
		if ( isset( $_GET['wcsg_requesting_recipient_id'] ) && isset( $_GET['change_subscription_to'] ) && isset( $_GET['subscription_id'] ) && isset( $_GET['_wpnonce'] ) ) {

			remove_action( 'init', 'WCS_User_Change_Status_Handler::maybe_change_users_subscription', 100 );

			$subscription = wcs_get_subscription( $_GET['subscription_id'] );
			$user_id      = $subscription->get_user_id();
			$new_status   = $_GET['change_subscription_to'];

			if ( WCS_User_Change_Status_Handler::validate_request( $user_id, $subscription, $new_status, $_GET['_wpnonce'] ) ) {
				WCS_User_Change_Status_Handler::change_users_subscription( $subscription, $new_status );
				wp_safe_redirect( $subscription->get_view_order_url() );
				exit;
			}
		}
	}

	/**
	 * Allows the recipient to suspend a subscription, provided the suspension count hasnt been reached
	 *
	 * @param bool|user_can_suspend Whether the user can suspend a subscription
	 */
	public static function recipient_can_suspend( $user_can_suspend, $subscription ) {

		if ( WCS_Gifting::is_gifted_subscription( $subscription ) && wp_get_current_user()->ID == $subscription->recipient_user ) {

			// Make sure subscription suspension count hasn't been reached
			$suspension_count    = $subscription->suspension_count;
			$allowed_suspensions = get_option( WC_Subscriptions_Admin::$option_prefix . '_max_customer_suspensions', 0 );

			if ( 'unlimited' === $allowed_suspensions || $allowed_suspensions > $suspension_count ) { // 0 not > anything so prevents a customer ever being able to suspend
				$user_can_suspend = true;
			}
		}

		return $user_can_suspend;

	}

	/**
	 * Adds all the subscriptions that have been gifted to a user to their subscriptions
	 *
	 * @param array|subscriptions An array of subscriptions assigned to the user
	 * @return array|subscriptions An updated array of subscriptions with any subscriptions gifted to the user added.
	 */
	public static function get_users_subscriptions( $subscriptions, $user_id ) {

		//get the subscription posts that have been gifted to this user
		$recipient_subscriptions = self::get_recipient_subscriptions( $user_id );

		foreach ( $recipient_subscriptions as $subscription_id ) {
			$subscriptions[ $subscription_id ] = wcs_get_subscription( $subscription_id );
		}

		if ( 0 < count( $recipient_subscriptions ) ) {
			krsort( $subscriptions );
		}

		return $subscriptions;
	}

	/**
	 * Adds recipient/purchaser information to the view subscription page
	 */
	public static function gifting_information_after_customer_details( $subscription ) {
		//check if the subscription is gifted
		if ( WCS_Gifting::is_gifted_subscription( $subscription ) ) {
			$customer_user  = get_user_by( 'id', $subscription->customer_user );
			$recipient_user = get_user_by( 'id', $subscription->recipient_user );
			$current_user   = wp_get_current_user();

			if ( $current_user->ID == $customer_user->ID ) {
				wc_get_template( 'html-view-subscription-gifting-information.php', array( 'user_title' => 'Recipient', 'name' => WCS_Gifting::get_user_display_name( $subscription->recipient_user ) ), '', plugin_dir_path( WCS_Gifting::$plugin_file ) . 'templates/' );
			} else {
				wc_get_template( 'html-view-subscription-gifting-information.php', array( 'user_title' => 'Purchaser', 'name' => WCS_Gifting::get_user_display_name( $subscription->customer_user ) ), '', plugin_dir_path( WCS_Gifting::$plugin_file ) . 'templates/' );
			}
		}
	}

	/**
	 * Gets an array of subscription ids which have been gifted to a user
	 *
	 * @param user_id The user id of the recipient
	 * @param $order_id The Order ID which contains the subscription
	 * @return array An array of subscriptions gifted to the user
	 */
	public static function get_recipient_subscriptions( $user_id, $order_id = 0 ) {
		$args = array(
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'post_type'      => 'shop_subscription',
			'orderby'        => 'date',
			'order'          => 'desc',
			'meta_key'       => '_recipient_user',
			'meta_value'     => $user_id,
			'meta_compare'   => '=',
			'fields'         => 'ids',
		);

		if ( 0 != $order_id ) {
			$args['post_parent'] = $order_id;
		}
		return get_posts( $args );
	}

	/**
	 * Filter the WC_Subscription::get_related_orders() method removing parent orders for recipients.
	 *
	 * @param array $related_orders an array of order ids related to the $subscription
	 * @param WC_Subscription Object $subscription
	 * @return array $related_orders an array of order ids related to the $subscription
	 */
	public static function maybe_remove_parent_order( $related_orders, $subscription ) {
		if ( WCS_Gifting::is_gifted_subscription( $subscription ) && wp_get_current_user()->ID == $subscription->recipient_user ) {
			$related_order_ids = array_keys( $related_orders );
			if ( in_array( $subscription->order->id, $related_order_ids ) ) {
				unset( $related_orders[ $subscription->order->id ] );
			}
		}
		return $related_orders;
	}

	/**
	 * Maybe add recipient information to order item meta for displaying in order item tables.
	 *
	 * @param int $item_id
	 * @param array $cart_item
	 */
	public static function maybe_add_recipient_order_item_meta( $item_id, $cart_item ) {
		$recipient_email = '';

		if ( isset( $cart_item['subscription_renewal'] ) && WCS_Gifting::is_gifted_subscription( $cart_item['subscription_renewal']['subscription_id'] ) ) {
			$recipient_id    = get_post_meta( $cart_item['subscription_renewal']['subscription_id'], '_recipient_user', true );
			$recipient       = get_user_by( 'id', $recipient_id );
			$recipient_email = $recipient->user_email;
		} else if ( isset( $cart_item['wcsg_gift_recipients_email'] ) ) {
			$recipient_email = $cart_item['wcsg_gift_recipients_email'];
		}

		if ( ! empty( $recipient_email ) ) {

			$recipient_user_id = email_exists( $recipient_email );

			if ( empty( $recipient_user_id ) ) {
				// create a username for the new customer
				$username  = explode( '@', $recipient_email );
				$username  = sanitize_user( $username[0], true );
				$counter   = 1;
				$original_username = $username;
				while ( username_exists( $username ) ) {
					$username = $original_username . $counter;
					$counter++;
				}
				$password = wp_generate_password();
				$recipient_user_id = wc_create_new_customer( $recipient_email, $username, $password );
				update_user_meta( $recipient_user_id, 'wcsg_update_account', 'true' );
			}

			wc_update_order_item_meta( $item_id, 'wcsg_recipient', 'wcsg_recipient_id_' . $recipient_user_id );

			// Clear the order item meta cache so all meta is included in emails sent on checkout
			$cache_key = WC_Cache_Helper::get_cache_prefix( 'orders' ) . 'item_meta_array_' . $item_id;
			wp_cache_delete( $cache_key, 'orders' );
		}
	}

	/**
	 * Format the order item meta label to be displayed.
	 *
	 * @param string $label The item meta label displayed
	 * @param string $name The name of the order item meta (key)
	 */
	public static function format_recipient_meta_label( $label, $name ) {
		if ( 'wcsg_recipient' == $name || 'wcsg_deleted_recipient_data' == $name ) {
			$label = 'Recipient';
		}
		return $label;
	}

	/**
	 * Format recipient order item meta value by extracting the recipient user id.
	 *
	 * @param mixed $value Order item meta value
	 */
	public static function format_recipient_meta_value( $value ) {
		if ( false !== strpos( $value, 'wcsg_recipient_id' ) ) {

			$recipient_id = substr( $value, strlen( 'wcsg_recipient_id_' ) );
			$strip_tags   = is_checkout() && ! is_wc_endpoint_url( 'order-received' );

			return WCS_Gifting::get_user_display_name( $recipient_id, $strip_tags );

		} else if ( false !== strpos( $value, 'wcsg_deleted_recipient_data' ) ) {

			$recipient_data = json_decode( substr( $value, strlen( 'wcsg_deleted_recipient_data_' ) ), true );
			return $recipient_data['display_name'];
		}
		return $value;
	}

	/**
	 * Prevents default display of recipient meta in admin panel.
	 *
	 * @param array $ignored_meta_keys An array of order item meta keys which are skipped when displaying meta.
	 */
	public static function hide_recipient_order_item_meta( $ignored_meta_keys ) {
		array_push( $ignored_meta_keys, 'wcsg_recipient', 'wcsg_deleted_recipient_data' );
		return $ignored_meta_keys;
	}

	/**
	 * Displays recipient order item meta for admin panel.
	 *
	 * @param int $item_id The id of the order item.
	 */
	public static function display_recipient_meta_admin( $item_id ) {

		$recipient_meta             = wc_get_order_item_meta( $item_id, 'wcsg_recipient' );
		$deleted_recipient_meta     = wc_get_order_item_meta( $item_id, 'wcsg_deleted_recipient_data' );
		$recipient_shipping_address = '';
		$recipient_display_name     = '';

		if ( ! empty( $recipient_meta ) ) {

			$recipient_id               = substr( $recipient_meta, strlen( 'wcsg_recipient_id_' ) );
			$recipient_shipping_address = WC()->countries->get_formatted_address( WCS_Gifting::get_users_shipping_address( $recipient_id ) );
			$recipient_display_name     = WCS_Gifting::get_user_display_name( $recipient_id );

		} else if ( ! empty( $deleted_recipient_meta ) ) {

			$recipient_data         = json_decode( substr( $deleted_recipient_meta, strlen( 'wcsg_deleted_recipient_data_' ) ), true );
			$recipient_display_name = $recipient_data['display_name'];

			unset( $recipient_data['display_name'] );

			$recipient_shipping_address = WC()->countries->get_formatted_address( $recipient_data );
		}

		if ( ! empty( $recipient_meta ) || ! empty( $deleted_recipient_meta ) ) {

			if ( empty( $recipient_shipping_address ) ) {
				$recipient_shipping_address = 'N/A';
			}

			echo '<br />';
			echo '<b>Recipient:</b> ' . wp_kses( $recipient_display_name , wp_kses_allowed_html( 'user_description' ) );
			echo '<img class="help_tip" data-tip="Shipping: ' . esc_attr( $recipient_shipping_address ) . '" src="' . esc_url( WC()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
		}
	}

	/**
	 * Removes recipient subscription meta from gifted subscriptions if the recipient is deleted.
	 *
	 * @param int $user_id The id of the user being deleted.
	 */
	public static function maybe_remove_recipient( $user_id ) {

		$gifted_subscriptions = WCSG_Recipient_Management::get_recipient_subscriptions( $user_id );
		$gifted_items         = WCS_Gifting::get_recipient_order_items( $user_id );

		if ( ! empty( $gifted_subscriptions ) ) {
			foreach ( $gifted_subscriptions as $subscription_id ) {
				update_post_meta( $subscription_id, '_recipient_user', 'deleted_recipient' );
			}

			$recipient      = get_user_by( 'id', $user_id );
			$recipient_data = json_encode(
				array_merge(
					array( 'display_name' => addslashes( WCS_Gifting::get_user_display_name( $user_id ) ) ),
					WCS_Gifting::get_users_shipping_address( $user_id )
				)
			);

			foreach ( $gifted_items as $gifted_item ) {
				if ( ! wcs_is_subscription( $gifted_item['order_id'] ) ) {
					wc_update_order_item_meta( $gifted_item['order_item_id'], 'wcsg_deleted_recipient_data', 'wcsg_deleted_recipient_data_' . $recipient_data );
				}
				wc_delete_order_item_meta( $gifted_item['order_item_id'], 'wcsg_recipient', 'wcsg_recipient_id_' . $user_id );
			}
		}
	}

	/**
	 * Displays a warning message if a recipient is in the process of being deleted.
	 */
	public static function maybe_display_delete_recipient_warning() {

		$recipient_users = array();

		if ( empty( $_REQUEST['users'] ) ) {
			$user_ids = array( $_REQUEST['user'] );
		} else {
			$user_ids = $_REQUEST['users'];
		}

		if ( ! empty( $user_ids ) ) {

			foreach ( $user_ids as $user_id ) {

				$gifted_subscriptions = WCSG_Recipient_Management::get_recipient_subscriptions( $user_id );

				if ( 0 != count( $gifted_subscriptions ) ) {
					$recipient_users[ $user_id ] = $gifted_subscriptions;
				}
			}

			$recipients_count = count( $recipient_users );

			if ( 0 != $recipients_count ) {

				echo '<p><strong>' . esc_html__( 'WARNING:', 'woocommerce-subscriptions-gifting' ) . ' </strong>';
				echo esc_html( _n( 'The following recipient will be removed from their subscriptions:', 'The following recipients will be removed from their subscriptions:',$recipients_count, 'woocommerce-subscriptions-gifting' ) );

				echo '<p><dl>';

				foreach ( $recipient_users as $recipient_id => $subscriptions ) {

					$recipient = get_userdata( $recipient_id );

					echo '<dt>ID #' . esc_attr( $recipient_id ) . ': ' . esc_attr( $recipient->user_login ) . '</dt>';

					foreach ( $subscriptions as $subscription_id ) {

						$subscription = wcs_get_subscription( $subscription_id );
						echo '<dd>' . esc_html__( 'Subscription' , 'woocommerce-subscriptions-gifting' ) . ' <a href="' . esc_url( wcs_get_edit_post_link( $subscription->id ) ) . '">#' . esc_html( $subscription->get_order_number() ) . '</a></dd>';

					}
				}

				echo '</dl>';

			}
		}
	}

	/**
	 * On subscription status changes, maybe update the role of the subscription recipient (if set) depending on the new subscription status.
	 * Sets the recipient user to the inactive subscriber role on on-hold, cancelled, expired statuses and an active subscriber role on active statuses.
	 *
	 * @param WC_Subscription $subscription
	 * @param string $new_status The subscription's new status
	 */
	public static function maybe_update_recipient_role( $subscription, $new_status ) {

		$inactive_statuses = array(
			'on-hold',
			'cancelled',
			'expired',
		);

		if ( WCS_Gifting::is_gifted_subscription( $subscription ) ) {

			if ( in_array( $new_status, $inactive_statuses ) ) {
				wcs_maybe_make_user_inactive( $subscription->recipient_user );
			} else if ( 'active' == $new_status ) {
				wcs_make_user_active( $subscription->recipient_user );
			}
		}
	}
}
WCSG_Recipient_Management::init();
