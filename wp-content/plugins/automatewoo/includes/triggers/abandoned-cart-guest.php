<?php
/**
 * @class       AW_Trigger_Guest_Abandoned_Cart
 * @package     AutomateWoo/Triggers
 */

class AW_Trigger_Abandoned_Cart_Guest extends AW_Trigger_Abstract_Abandoned_Cart {

	public $name = 'guest_abandoned_cart';

	public $supplied_data_items = [ 'cart', 'guest', 'shop' ];

	/**
	 * Construct
	 */
	function init() {
		$this->title = __( 'Abandoned Cart (Guests)', 'automatewoo' );

		parent::init();
	}



	/**
	 * Route hooks through here
	 */
	function catch_hooks() {

		$carts_query = ( new AW_Query_Abandoned_Carts() )->where( 'user_id', 0 );
		$carts = $carts_query->get_results();

		if ( ! $carts )
			return;

		foreach ( $carts as $cart ) {

			$guest = $cart->get_guest();

			// cart must have items and a guest
			if ( ! $cart->items || ! $guest ) continue;

			$this->maybe_run([
				'guest' => $guest,
				'cart' => $cart
			]);
		}

	}


	/**
	 * @param $workflow AW_Model_Workflow
	 * @return bool
	 */
	function validate_workflow( $workflow ) {

		$cart = $workflow->get_data_item('cart');
		$guest = $workflow->get_data_item('guest');

		if ( ! $cart || ! $guest )
			return false;

		$delay = floatval( $workflow->get_trigger_option('delay') );

		if ( ! $delay ) return false; // delay must be set


		// convert delay to minutes
		$delay_mins = round( $delay * 60 );

		$delay_date = new DateTime(); // UTC
		$delay_date->modify("-$delay_mins minutes");


		// Was cart last updated longer than the delay date
		if ( $cart->last_modified > $delay_date->format('Y-m-d H:i:s') )
			return false;


		if ( ! $this->validate_field_user_pause_period( $workflow ) )
			return false;


		// Now check our logs
		// Only run each once foreach workflow for each stored cart
		$log_query = ( new AW_Query_Logs() )
			->where( 'workflow_id', $workflow->get_translation_ids() )
			->where( 'cart_id', $cart->id )
			->set_limit(1);

		if ( $log_query->get_results() )
			return false;

		return true;
	}


}
