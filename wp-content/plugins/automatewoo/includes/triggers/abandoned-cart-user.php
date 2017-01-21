<?php
/**
 * @class       AW_Trigger_Abandoned_Cart_User
 * @package     AutomateWoo/Triggers
 */

class AW_Trigger_Abandoned_Cart_User extends AW_Trigger_Abstract_Abandoned_Cart
{
	public $name = 'abandoned_cart';

	public $supplied_data_items = [ 'cart', 'user', 'shop' ];

	/**
	 * Construct
	 */
	function init()
	{
		$this->title = __('Abandoned Cart (Users)', 'automatewoo');

		// Registers the trigger
		parent::init();
	}


	/**
	 * Route hooks through here
	 */
	function catch_hooks()
	{
		$carts_query = new AW_Query_Abandoned_Carts();
		$carts_query->where( 'user_id', 0, '!=' );
		$carts = $carts_query->get_results();

		if ( ! is_array($carts) )
			return;

		foreach ( $carts as $cart )
		{
			// cart must have items
			if ( ! $cart->items ) continue;

			$this->maybe_run([
				'user' => get_user_by( 'id', $cart->user_id ),
				'cart' => $cart
			]);
		}
	}


	/**
	 * @param $workflow AW_Model_Workflow
	 * @return bool
	 */
	function validate_workflow( $workflow )
	{
		$user = $workflow->get_data_item('user');
		$cart = $workflow->get_data_item('cart');

		if ( ! $user && ! $cart )
			return false;

		$delay = floatval( $workflow->get_trigger_option('delay') );

		if ( ! $delay )
		{
			$delay =  0.25; // min delay value
		}

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
		$log_query = new AW_Query_Logs();
		$log_query->where( 'workflow_id', $workflow->get_translation_ids() );
		$log_query->where( 'cart_id', $cart->id );
		$log_query->set_limit(1);

		if ( $log_query->get_results() )
			return false;

		return true;
	}


}
