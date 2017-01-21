<?php
/**
 * @class       AW_Trigger_Wishlist_Reminder
 * @package     AutomateWoo/Triggers
 */

class AW_Trigger_Wishlist_Reminder extends AW_Trigger
{
	public $name = 'wishlist_reminder';

	public $group = 'Wishlist';

	public $supplied_data_items = array( 'user', 'wishlist', 'shop' );

	public $allow_queueing = false;


	function init()
	{
		$integration = AW()->wishlist()->get_integration();

		// don't enable triggers
		if ( ! $integration )
			return;

		$this->title = sprintf( __('Wishlist Reminder (%s)', 'automatewoo'), AW()->wishlist()->get_integration_title() );
		$this->description = __( "Setting the 'Reminder Interval' field to 30 will mean this trigger will fire every 30 days for any users that have items in their wishlist. This trigger is checked daily. Please note this doesn't work for guests because their wishlist data only exists in their session data.", 'automatewoo');

		parent::init();
	}



	/**
	 * Add options to the trigger
	 */
	function load_fields()
	{
		$period = new AW_Field_Number_Input();
		$period->set_name('interval');
		$period->set_title( __( 'Reminder Interval (days)', 'automatewoo' ) );
		$period->set_description( __( 'E.g. Reminder any users with items in a Wishlist every 30 days.', 'automatewoo'  ) );

		$once_only = new AW_Field_Checkbox();
		$once_only->set_name('once_only');
		$once_only->set_title( __( 'Once Per User', 'automatewoo' ));
		$once_only->set_description( __( 'If checked the trigger will fire only once for each user for each wishlist they create. Most users only use the one wishlist so use with caution. Setting a high Reminder Interval may be a better plan.', 'automatewoo'  ) );

		$this->add_field( $period );
		$this->add_field( $once_only );
	}



	/**
	 * When should this trigger run?
	 */
	function register_hooks()
	{
		add_action( 'automatewoo_daily_worker', array( $this, 'catch_hooks' ) );
	}



	/**
	 * Route hooks through here
	 */
	function catch_hooks()
	{
		// As this query is going to be memory intensive lets make sure we have a workflow using this trigger
		if ( ! $this->has_workflows() )
			return;

		$integration = AW()->wishlist()->get_integration();

		if ( $integration == 'woothemes' )
		{
			// Get all wishlists
			$wishlists = get_posts(array(
				'post_type' => 'wishlist',
			));
		}
		elseif( $integration == 'yith')
		{
			$wishlists = YITH_WCWL()->get_wishlists();
		}
		else
		{
			return;
		}


		if ( is_array( $wishlists ) )
		{
			foreach( $wishlists as $wishlist )
			{
				$normalized_wishlist = AW()->wishlist()->get_normalized_wishlist( $wishlist );

				$user = get_user_by( 'id', $normalized_wishlist->owner_id );

				if ( $user )
				{
					$this->maybe_run(array(
						'user' => $user,
						'wishlist' => $normalized_wishlist
					));
				}
			}
		}

	}


	/**
	 * @param $workflow AW_Model_Workflow
	 *
	 * @return bool
	 */
	function validate_workflow( $workflow )
	{
		$user = $workflow->get_data_item('user');
		$wishlist = $workflow->get_data_item('wishlist');

		if ( ! $user || ! $wishlist )
			return false;


		// Only do this once for each user for each workflow and each wishlist
		if ( $workflow->get_trigger_option('once_only') )
		{
			$log_query = ( new AW_Query_Logs() )
				->where( 'workflow_id', $workflow->id )
				->where( 'wishlist_id', $wishlist->id )
				->where( 'user_id', $user->ID )
				->set_limit(1);

			if ( $log_query->get_results() )
				return false;
		}


		$interval = absint( $workflow->get_trigger_option('interval') );

		if ( ! $interval )
			return false;

		$last_interval_date = new DateTime();
		$last_interval_date->modify( "-$interval days" );


		// Now check our logs for the last run
		$log_query = ( new AW_Query_Logs() )
			->where( 'workflow_id', $workflow->id )
			->where( 'date', $last_interval_date, '>' )
			->where( 'wishlist_id', $wishlist->id )
			->where( 'user_id', $user->ID )
			->set_limit(1);

		if ( $log_query->get_results() )
			return false;

		return true;
	}

}

