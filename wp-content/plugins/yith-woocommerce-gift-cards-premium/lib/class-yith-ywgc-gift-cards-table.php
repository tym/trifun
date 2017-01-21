<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


if ( ! class_exists( 'YITH_YWGC_Gift_Cards_Table' ) ) {

	/**
	 *
	 * @class   YITH_YWGC_Gift_Cards_Table
	 * @package Yithemes
	 * @since   1.0.0
	 * @author  Your Inspiration Themes
	 */
	class YITH_YWGC_Gift_Cards_Table {
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

			// Add to admin_init function
			add_filter( 'manage_edit-gift_card_columns', array( $this, 'add_custom_columns_title' ) );

			// Add to admin_init function
			add_action( 'manage_gift_card_posts_custom_column', array(
				$this,
				'add_custom_columns_content',
			), 10, 2 );
		}

		/**
		 * Add custom columns to custom post type table
		 *
		 * @param array $defaults current columns
		 *
		 * @return array new columns
		 */
		function add_custom_columns_title( $defaults ) {
			$columns = array_slice( $defaults, 0, 2 );

			$columns[ YWGC_TABLE_COLUMN_ORDER ]             = __( "Order", 'yith-woocommerce-gift-cards' );
			$columns[ YWGC_TABLE_COLUMN_AMOUNT ]            = __( "Amount", 'yith-woocommerce-gift-cards' );
			$columns[ YWGC_TABLE_COLUMN_BALANCE ]           = __( "Balance", 'yith-woocommerce-gift-cards' );
			$columns[ YWGC_TABLE_COLUMN_DEST_ORDERS ]       = __( "Orders", 'yith-woocommerce-gift-cards' );
			$columns[ YWGC_TABLE_COLUMN_DEST_ORDERS_TOTAL ] = __( "Order total", 'yith-woocommerce-gift-cards' );
			$columns[ YWGC_TABLE_COLUMN_INFORMATION ]       = __( "Information", 'yith-woocommerce-gift-cards' );
			$columns[ YWGC_TABLE_COLUMN_ACTIONS ]           = '';

			return array_merge( $columns, array_slice( $defaults, 1 ) );
		}

		/**
		 * @param WC_Order|int $order
		 *
		 * @return int
		 */
		private function get_order_number_and_details( $order ) {

			if ( is_numeric( $order ) ) {
				$order = wc_get_order( $order );
			}

			if ( ! $order instanceof WC_Order ) {
				return '';
			}
			$order_id = $order->id;

			if ( $order->user_id ) {
				$user_info = get_userdata( $order->user_id );
			}

			if ( ! empty( $user_info ) ) {
				$username = '<a href="user-edit.php?user_id=' . absint( $user_info->ID ) . '">';

				if ( $user_info->first_name || $user_info->last_name ) {
					$username .= esc_html( ucfirst( $user_info->first_name ) . ' ' . ucfirst( $user_info->last_name ) );
				} else {
					$username .= esc_html( ucfirst( $user_info->display_name ) );
				}

				$username .= '</a>';

			} else {

				if ( $order->billing_first_name || $order->billing_last_name ) {
					$username = trim( $order->billing_first_name . ' ' . $order->billing_last_name );
				} else {
					$username = __( 'Guest', 'yith-woocommerce-gift-cards' );
				}
			}

			return sprintf( _x( '%s by %s', 'Order number by X', 'yith-woocommerce-gift-cards' ),
				'<a href="' . admin_url( 'post.php?post=' . absint( $order_id ) . '&action=edit' ) . '" class="row-title"><strong>#' .
				esc_attr( $order->get_order_number() ) . '</strong></a>',
				$username );
		}


		/**
		 * show content for custom columns
		 *
		 * @param $column_name string column shown
		 * @param $post_ID     int     post to use
		 */
		function add_custom_columns_content( $column_name, $post_ID ) {

			$gift_card = new YWGC_Gift_Card_Premium( array( 'ID' => $post_ID ) );

			if ( ! $gift_card->exists() ) {
				return;
			}

			switch ( $column_name ) {
				case YWGC_TABLE_COLUMN_ORDER :

					if ( $gift_card->order_id ) {
						echo $this->get_order_number_and_details( $gift_card->order_id );
					} else {
						_e( 'Created manually', 'yith-woocommerce-gift-cards' );
					}

					break;

				case YWGC_TABLE_COLUMN_AMOUNT :

					$_amount     = empty( $gift_card->amount ) ? 0.00 : $gift_card->amount;
					$_amount_tax = empty( $gift_card->amount_tax ) ? 0.00 : $gift_card->amount_tax;

					echo wc_price( $_amount + $_amount_tax );

					break;

				case YWGC_TABLE_COLUMN_BALANCE:

					$_amount     = empty( $gift_card->balance ) ? 0.00 : $gift_card->balance;
					$_amount_tax = empty( $gift_card->balance_tax ) ? 0.00 : $gift_card->balance_tax;

					echo wc_price( $_amount + $_amount_tax );

					break;

				case YWGC_TABLE_COLUMN_DEST_ORDERS:
					$orders = $gift_card->get_registered_orders();
					if ( $orders ) {
						foreach ( $orders as $order_id ) {
							echo $this->get_order_number_and_details( $order_id );
							echo "<br>";
						}
					} else {
						_e( "The code has not been used yet", 'yith-woocommerce-gift-cards' );
					}

					break;

				case YWGC_TABLE_COLUMN_INFORMATION:
					if ( YITH_YWGC()->prior_than_150() ) {
						$this->show_details_on_gift_cards_table_prior_150( $post_ID, $gift_card );
					} else {
						$this->show_details_on_gift_cards_table( $post_ID, $gift_card );
					}

					break;

				case YWGC_TABLE_COLUMN_DEST_ORDERS_TOTAL:

					$orders = $gift_card->get_registered_orders();
					$total  = 0.00;

					if ( $orders ) {
						foreach ( $orders as $order_id ) {

							$the_order = wc_get_order( $order_id );
							if ( $the_order ) {
								//  From version 1.2.10, show the order totals instead of subtotals
								//  $order_total = floatval(preg_replace('#[^\d.]#', '', $the_order->get_subtotal_to_display()));
								$total += $the_order->order_total;
							}
						}
					}
					echo wc_price( $total );

					$_amount = get_post_meta( $post_ID, YWGC_META_GIFT_CARD_AMOUNT, true );
					$_amount = empty( $_amount ) ? 0.00 : $_amount;

					if ( $_amount && ( $total > $_amount ) ) {
						$percent = (float) ( $total - $_amount ) / $_amount * 100;
						echo '<br><span class="ywgc-percent">' . sprintf( __( '(+ %.2f%%)', 'yith-woocommerce-gift-cards' ), $percent ) . '</span>';
					}

					break;

				case YWGC_TABLE_COLUMN_ACTIONS:

					YITH_YWGC()->admin->show_change_status_button( $post_ID, $gift_card );
					YITH_YWGC()->admin->show_send_email_button( $post_ID, $gift_card );

					break;
			}
		}

		/**
		 * @param $post_ID
		 * @param $gift_card
		 */
		public function show_details_on_gift_cards_table_prior_150( $post_ID, $gift_card ) {
			$content   = get_post_meta( $post_ID, YWGC_META_GIFT_CARD_USER_DATA, true );
			$recipient = isset( $content["recipient"] ) ? $content["recipient"] : '';

			if ( $gift_card->is_dismissed() ) {
				?>
				<span
					class="ywgc-dismissed-text"><?php _e( "This card is dismissed.", 'yith-woocommerce-gift-cards' ); ?></span>
				<?php
			}

			if ( empty( $recipient ) ) {
				?>
				<div>
					<span><?php echo __( "Physical product", 'yith-woocommerce-gift-cards' ); ?></span>
				</div>
				<?php
			} else {

				$delivery_date = get_post_meta( $post_ID, YWGC_META_GIFT_CARD_DELIVERY_DATE, true );
				$email_date    = get_post_meta( $post_ID, YWGC_META_GIFT_CARD_SENT, true );

				if ( $email_date ) {
					$status_class = "sent";
					$message      = sprintf( __( "Sent on %s", 'yith-woocommerce-gift-cards' ), $email_date );
				} else if ( $delivery_date >= current_time( 'Y-m-d' ) ) {
					$status_class = "scheduled";
					$message      = __( "Scheduled", 'yith-woocommerce-gift-cards' );
				} else {
					$status_class = "failed";
					$message      = __( "Failed", 'yith-woocommerce-gift-cards' );
				}
				?>

				<div>
					<span><?php echo sprintf( __( "Recipient: %s", 'yith-woocommerce-gift-cards' ), $recipient ); ?></span>
				</div>
				<div>
					<span><?php echo sprintf( __( "Delivery date: %s", 'yith-woocommerce-gift-cards' ), $delivery_date ); ?></span>
					<br>
					<span
						class="ywgc-delivery-status <?php echo $status_class; ?>"><?php echo $message; ?></span>

				</div>
				<?php
			}
		}

		/**
		 * @param int                    $post_ID
		 * @param YWGC_Gift_Card_Premium $gift_card
		 */
		public function show_details_on_gift_cards_table( $post_ID, $gift_card ) {

			if ( $gift_card->is_dismissed() ) {
				?>
				<span
					class="ywgc-dismissed-text"><?php _e( "This card is dismissed.", 'yith-woocommerce-gift-cards' ); ?></span>
				<?php
			}

			if ( ! $gift_card->is_digital ) {
				?>
				<div>
					<span><?php echo __( "Physical product", 'yith-woocommerce-gift-cards' ); ?></span>
				</div>
				<?php
			} else {

				if ( $gift_card->delivery_send_date ) {
					$status_class = "sent";
					$message      = sprintf( __( "Sent on %s", 'yith-woocommerce-gift-cards' ), $gift_card->delivery_send_date );
				} else if ( $gift_card->delivery_date >= current_time( 'Y-m-d' ) ) {
					$status_class = "scheduled";
					$message      = __( "Scheduled", 'yith-woocommerce-gift-cards' );
				} else {
					$status_class = "failed";
					$message      = __( "Failed", 'yith-woocommerce-gift-cards' );
				}
				?>

				<div>
					<span><?php echo sprintf( __( "Recipient: %s", 'yith-woocommerce-gift-cards' ), $gift_card->recipient ); ?></span>
				</div>
				<div>
					<span><?php echo sprintf( __( "Delivery date: %s", 'yith-woocommerce-gift-cards' ), $gift_card->delivery_date ); ?></span>
					<br>
					<span
						class="ywgc-delivery-status <?php echo $status_class; ?>"><?php echo $message; ?></span>

				</div>
				<?php
			}
		}
	}
}

YITH_YWGC_Gift_Cards_Table::get_instance();