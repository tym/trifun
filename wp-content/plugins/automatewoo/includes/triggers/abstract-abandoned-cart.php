<?php
/**
 * @class       AW_Trigger_Guest_Abandoned_Cart
 * @package     AutomateWoo/Triggers
 */

abstract class AW_Trigger_Abstract_Abandoned_Cart extends AW_Trigger
{
	public $group = 'Cart';

	public $allow_queueing = false;

	/**
	 * Add options to the trigger
	 */
	function load_fields()
	{
		$delay = ( new AW_Field_Number_Input() )
			->set_name('delay')
			->set_title( __( 'Hours After Abandoned', 'automatewoo' ) )
			->set_min('0.25')
			->set_max('720')
			->set_description(__( "It is possible to use decimal values e.g. '1.5 = 1 hour and 30 minutes'. Max is 720 hours (30 days).", 'automatewoo' ) )
			->add_extra_attr('step', '0.25')
			->set_required();

		$this->add_field( $delay );
		$this->add_field_user_pause_period();
	}


	function register_hooks()
	{
		add_action( 'automatewoo_thirty_minute_worker', [ $this, 'catch_hooks' ] );
	}
}
