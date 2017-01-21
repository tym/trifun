<?php
/**
 * @class       AW_Queue_Manager
 * @package     AutomateWoo
 * @since       2.1.0
 */

class AW_Queue_Manager {

	/** @var int */
	public $batch_size;

	/** @var int (in days) */
	public $delete_failed_after;

	/**
	 * Construct
	 */
	function __construct() {

		$this->batch_size = apply_filters( 'automatewoo_queue_batch_size', AW()->options()->queue_batch_size );
		$this->delete_failed_after = apply_filters( 'automatewoo_failed_events_delete_after', 30 );

		add_action( 'automatewoo_five_minute_worker', [ $this, 'check_for_queued_events' ] );
		add_action( 'automatewoo_two_days_worker', [ $this, 'check_for_failed_queued_events' ] );
	}


	/**
	 * Check for queued workflow runs
	 */
	function check_for_queued_events() {

		$query = ( new AW_Query_Queue() )
			->set_limit( $this->batch_size )
			->set_ordering( 'date', 'ASC' )
			->where( 'date', current_time( 'mysql', true ), '<' )
			->where( 'failed', false );

		$results = $query->get_results();

		if ( $results ) foreach ( $results as $result ) {
			$result->run();
		}
	}


	/**
	 * Delete old for queued events that failed
	 */
	function check_for_failed_queued_events() {

		$clear_date = new DateTime(); // UTC
		$clear_date->modify("-{$this->delete_failed_after} days");

		$query = ( new AW_Query_Queue() )
			->set_limit( $this->batch_size )
			->set_ordering('date', 'ASC')
			->where('date', $clear_date->format( 'Y-m-d H:i:s' ), '<' )
			->where('failed', true );

		$results = $query->get_results();

		if ( $results ) foreach ( $results as $result ) {
			$result->delete();
		}
	}


}