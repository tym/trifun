<?php
/**
 * @class       AW_Abandoned_Cart_Manager
 * @package     AutomateWoo
 */

class AW_Abandoned_Cart_Manager {

	/** @var bool - used when restoring carts so that we don't fire unnecessary db queries */
	public $_prevent_store_cart = false;


	/**
	 * Constructor
	 */
	function __construct() {

		add_action( 'automatewoo_two_days_worker', [ $this, 'clean_stored_carts' ] );

		if ( AW()->options()->abandoned_cart_enabled ) {

			add_action( 'wp_loaded', [ $this, 'catch_restore_cart_link' ] );

			add_action( 'woocommerce_cart_updated', [ $this, 'maybe_store_cart' ], 100 );
			add_action( 'woocommerce_cart_emptied', [ $this, 'cart_emptied' ] );
			add_action( 'woocommerce_checkout_order_processed', [ $this, 'empty_after_order_created' ] );

			add_action( 'wp_footer', [ $this, 'js' ] );

			add_action( 'automatewoo/ajax/capture_email', [ $this, 'ajax_capture_email' ] );

		}
	}


	/**
	 * Logic to determine whether we should save the cart on certain hooks
	 */
	function maybe_store_cart() {

		// don't clear the cart after logout
		if ( did_action( 'wp_logout' ) )
			return;

		if ( $this->_prevent_store_cart )
			return;

		if ( $user_id = AW()->session_tracker->get_detected_user_id() ) {
			$this->store_user_cart( $user_id );
		}
		elseif ( $guest = AW()->session_tracker->get_current_guest() ) {
			// Store a guest cart if the guest has been stored in the database
			$this->store_guest_cart( $guest );

			$guest->update_last_active();
		}
	}



	/**
	 * Attempts to update or insert carts for guests
	 *
	 * @param AW_Model_Guest $guest
	 *
	 * @return bool
	 */
	function store_guest_cart( $guest ) {

		if ( ! $guest )
			return false;

		$cart = $guest->get_cart();

		if ( $cart ) {
			if ( 0 === sizeof( WC()->cart->get_cart() ) )
				$cart->delete();
			else
				$cart->sync();
		}
		else {
			// cart is empty
			if ( 0 === sizeof( WC()->cart->get_cart() ) )
				return false;

			// create new cart
			$ac = new AW_Model_Abandoned_Cart();
			$ac->guest_id = $guest->id;
			$ac->set_token();
			$ac->sync();
		}

		return true;
	}


	/**
	 * Attempts to store cart for a registered user whether they are logged in or not
	 *
	 * @param bool $user_id
	 * @return bool
	 */
	function store_user_cart( $user_id = false ) {

		if ( ! $user_id ) {
			// get user
			if ( ! $user_id = AW()->session_tracker->get_detected_user_id() )
				return false;
		}

		// If user is logged out their WC cart gets emptied
		// at this point we are tracking them via cookie
		// so it doesn't make sense to clear their abandoned cart
		if ( ! is_user_logged_in() && 0 === sizeof( WC()->cart->get_cart() ) ) {
			return false;
		}

		// does this user already have a stored cart?
		$existing_cart = new AW_Model_Abandoned_Cart();
		$existing_cart->get_by( 'user_id', $user_id );


		// if cart already exists
		if ( $existing_cart->exists ) {

			// delete cart if empty otherwise update it
			if ( 0 === sizeof( WC()->cart->get_cart() ) )
				$existing_cart->delete();
			else
				$existing_cart->sync();

			return true;
		}
		else {
			// if the cart doesn't already exist
			// and there are no items in cart no there is no need to insert
			if ( 0 === sizeof( WC()->cart->get_cart() ) )
				return false;

			// create a new stored cart for the user
			$cart = new AW_Model_Abandoned_Cart();
			$cart->user_id = $user_id;
			$cart->set_token();
			$cart->sync();

			return true;
		}

	}


	/**
	 * This event will fire when an order is placed and the cart is emptied NOT when a user empties their cart.
	 */
	function cart_emptied() {

		// don't clear the cart after logout
		if ( did_action( 'wp_logout' ) )
			return;

		$guest = AW()->session_tracker->get_current_guest();
		$user_id = AW()->session_tracker->get_detected_user_id();

		if ( $user_id ) {
			$cart = new AW_Model_Abandoned_Cart();
			$cart->get_by( 'user_id', $user_id );
			$cart->delete();
		}

		if ( $guest ) {
			// Ensure carts are cleared for users and guests registered at checkout
			$guest->delete_cart();
		}
	}


	/**
	 * Ensure the stored abandoned cart is removed when an order is created
	 *
	 * Clears even if payment has not gone through
	 *
	 * @param $order_id
	 */
	function empty_after_order_created( $order_id ) {

		$order = wc_get_order( $order_id );

		$user_id = $order->get_user_id();

		if ( $user_id > 0 ) {
			$cart = new AW_Model_Abandoned_Cart();
			$cart->get_by( 'user_id', $user_id );
			$cart->delete();
		}

		// order placed by a guest or by a guest that signed up at checkout

		$guest = new AW_Model_Guest();
		$guest->get_by( 'email', strtolower( $order->billing_email ) );
		$guest->delete_cart();

		// Delete cart by guest cookie key
		if ( $guest = AW()->session_tracker->get_current_guest() ) {
			$guest->delete_cart();
		}
	}



	/**
	 * Add ajax email capture to checkout
	 */
	function js() {

		switch( AW()->options()->guest_email_capture_scope ) {
			case 'none':
				return;
				break;
			case 'checkout':
				if ( ! is_checkout() ) return;
				break;
		}

		$selectors = apply_filters( 'automatewoo/guest_capture_fields', [
			'#billing_email',
			'.automatewoo-capture-guest-email'
		]);

		?>
			<script type="text/javascript">
				(function($){

					var email = '';

					$(document).on('blur', '<?php echo implode( ', ', $selectors ) ?>', function(){

						// hasn't changed
						if ( email == $(this).val() ) {
							return;
						}

						email = $(this).val();

						$.post( '<?php echo esc_js( AW_Ajax::get_endpoint( 'capture_email' ) ) ?>', {
								email: email
								<?php if ( AW()->integrations()->is_wpml() ): ?>
									,language: '<?php echo esc_js( wpml_get_current_language() ) ?>'
								<?php endif; ?>
						});

					});

				})(jQuery);
			</script>
		<?php
	}


	/**
	 * Restores a cart when URL is clicked
	 */
	function catch_restore_cart_link() {

		$cart_token = aw_clean( aw_request( 'aw-restore-cart' ) );

		if ( ! $cart_token )
			return;

		$cart = new AW_Model_Abandoned_Cart();
		$cart->get_by( 'token', $cart_token );

		if ( ! $cart->exists || ! is_array( $cart->items ) )
			return;


		// block cart storage hooks
		$this->_prevent_store_cart = true;
		$notices_backup = wc_get_notices();


		// merge restored items with existing
		$existing_items = WC()->cart->get_cart_for_session();

		foreach ( $cart->get_items() as $item_key => $item ) {
			// item already exists in cart
			if ( isset( $existing_items[$item_key] ) )
				continue;

			WC()->cart->add_to_cart( $item['product_id'], $item['quantity'], $item['variation_id'], $item['variation']  );
		}

		// restore coupons
		foreach ( $cart->get_coupons() as $coupon_code => $coupon_data ) {
			if ( ! WC()->cart->has_discount( $coupon_code ) )
			{
				WC()->cart->add_discount( $coupon_code );
			}
		}


		// clear show notices for added coupons or products
		wc_set_notices( $notices_backup );

		// unblock cart storing and store the restored cart
		$this->_prevent_store_cart = false;
		$this->maybe_store_cart();


		wp_redirect(add_query_arg([
			'aw-cart-restored' => 1
		], wc_get_page_permalink('cart') ) );

		exit;
	}



	/**
	 *
	 */
	function ajax_capture_email() {
		// don't capture the email if the user has been detected
		if ( AW()->session_tracker->get_detected_user_id() )
			die;

		$email = sanitize_email( aw_request('email') );
		$language = aw_clean( aw_request('language') );

		// capture the guest email
		AW()->session_tracker->store_guest( $email, $language );
	}


	/**
	 * Delete stored carts older than 45 days
	 */
	function clean_stored_carts() {
		global $wpdb;

		$delay_date = new DateTime(); // UTC
		$delay_date->modify("-45 days");

		$wpdb->query( $wpdb->prepare("
			DELETE FROM ". $wpdb->prefix . AW()->table_name_abandoned_cart . "
			WHERE last_modified < %s",
			$delay_date->format('Y-m-d H:i:s')
		));
	}

}
