<?php
/**
 * This trigger relies on the user meta field _aw_last_order_placed that is managed in AW_Order_Helper.
 *
 * @class       AW_Trigger_User_Absent
 * @package     AutomateWoo/Triggers
 */

class AW_Trigger_User_Absent extends AW_Trigger {

	public $name = 'user_absent';

	public $allow_queueing = false;

	public $supplied_data_items = [ 'user', 'shop' ];


	function init() {

		$this->title = __( 'User Has Not Purchased For Period', 'automatewoo' );
		$this->group = __( 'Group', 'automatewoo' );

		parent::init();
	}


	/**
	 * Add options to the trigger
	 */
	function load_fields() {

		$period = ( new AW_Field_Number_Input() )
			->set_name( 'days_since_last_purchase' )
			->set_title( __( 'Period (days)', 'automatewoo' ) )
			->set_required();

		$repeat = ( new AW_Field_Checkbox() )
			->set_name( 'enable_repeats' )
			->set_title( __( 'Enable Repeats', 'automatewoo' ) )
			->set_description( __( 'If checked this trigger will repeatedly fire after the set period passes and the user has not made a purchase. E.g. if set to 30 days the trigger will fire 30 days after the customers last purchase and every 30 days from then on until the customer makes another purchase, then the cycle starts again. If unchecked the trigger will not repeat until the customer makes a new purchase.', 'automatewoo' ) );

		$this->add_field( $period );
		$this->add_field( $repeat );
	}


	/**
	 * Run check daily
	 */
	function register_hooks() {

		add_action( 'automatewoo_daily_worker', [ $this, 'catch_hooks' ] );
		add_action( 'automatewoo/batch/user_absent', [ $this, 'process_batch' ], 10, 2 );
	}



	/**
	 * This trigger does not use $this->maybe_run() so we don't have to loop through every single user when processing
	 */
	function catch_hooks() {

		if ( ! $this->has_workflows() )
			return;

		if ( ! $workflows = $this->get_workflows() )
			return;

		foreach ( $workflows as $workflow ) {

			if ( ! $period = absint( $workflow->get_trigger_option( 'days_since_last_purchase' ) ) )
				return;

			$date = new DateTime();
			$date->modify( "-$period days" );

			// fetch users by date using our last order meta field
			$users = get_users([
				'fields' => 'ids',
				'meta_query' => [
					[
						'key' => '_aw_last_order_placed',
						'compare' => '!=',
						'value' => false
					],
					[
						'key' => '_aw_last_order_placed',
						'compare' => '<',
						'value' => $date->format( 'Y-m-d H:i:s' )
					]
				]
			]);

			if ( $users ) {
				$process = new AW_Background_Process( 'automatewoo/batch/user_absent', $users, [ 'workflow_id' => $workflow->id ] );
				$process->set_delay( ( aw_get_user_count_rough() > 4000 ) ? 3 : 5 ); // decrease delay if more users
				$process->dispatch();
			}
		}
	}


	/**
	 * @param $users
	 * @param $args
	 */
	function process_batch( $users, $args ) {

		if ( ! isset( $args['workflow_id'] ) )
			return;

		$workflow = new AW_Model_Workflow( absint( $args['workflow_id'] ) );

		// workflow status may have changed
		if ( ! $workflow->is_active() )
			return;

		foreach ( $users as $user_id ) {
			$workflow->maybe_run([
				'user' => get_user_by( 'id', $user_id )
			]);
		}
	}


	/**
	 * @param $workflow AW_Model_Workflow
	 * @return bool
	 */
	function validate_workflow( $workflow ) {

		$user = $workflow->get_data_item( 'user' );
		$period = absint( $workflow->get_trigger_option( 'days_since_last_purchase' ) );
		$enable_repeats = $workflow->get_trigger_option( 'enable_repeats' );

		if ( ! $user || ! $period )
			return false;

		$date = new DateTime( current_time( 'mysql', true ) );
		$date->modify("-$period days");

		// fail if the user has not been a member for at least the set period
		if ( strtotime( $user->user_registered ) > $date->getTimestamp() )
			return false;

		$last_order_date = get_user_meta( $user->ID, '_aw_last_order_placed', true );

		// check that the user has not made a purchase since the start of the delay period
		if ( ! $last_order_date || strtotime( $last_order_date ) > $date->getTimestamp() )
			return false;

		if ( $enable_repeats ) {

			// check the workflow has not run in the last set period
			$log_query = ( new AW_Query_Logs() )
				->where( 'workflow_id', $workflow->id )
				->where( 'date', $date, '>' )
				->where( 'user_id', $user->ID )
				->set_limit(1);

			if ( $log_query->get_results() )
				return false;
		}
		else {

			// check the workflow has not run since the user's last purchase
			$log_query = ( new AW_Query_Logs() )
				->where( 'workflow_id', $workflow->id )
				->where( 'date', $last_order_date, '>' )
				->where( 'user_id', $user->ID )
				->set_limit(1);

			if ( $log_query->get_results() )
				return false;
		}

		return true;
	}

}
