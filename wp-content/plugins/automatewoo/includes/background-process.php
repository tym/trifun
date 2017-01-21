<?php
/**
 * @class 		AW_Background_Process
 * @package 	AutomateWoo
 * @since		2.6.1
 */
class AW_Background_Process {

	/** @var array */
	public $items;

	/** @var string  */
	public $hook;

	/** @var string  */
	public $args;

	/** @var integer : max number of items to process each time */
	public $batch_size;

	/** @var integer : in seconds  */
	public $delay;


	/**
	 * @param string $hook : hook should accept 2 params
	 * @param array $items
	 * @param array $args
	 */
	function __construct( $hook, $items, $args = [] ) {
		$this->batch_size = apply_filters( 'automatewoo/background_process/batch_size', 20, $this );
		$this->delay = apply_filters( 'automatewoo/background_process/delay', 5, $this );
		$this->hook = $hook;
		$this->items = $items;
		$this->args = $args;
	}


	/**
	 * @param $size
	 */
	function set_batch_size( $size ) {
		$this->batch_size = $size;
	}


	/**
	 * @param $delay
	 */
	function set_delay( $delay ) {
		$this->delay = $delay;
	}


	/**
	 *
	 */
	function dispatch() {

		$items = $this->items;
		$i = 1;
		$delay = $this->delay * 60; // seconds

		while ( ! empty( $items ) ) {
			$batch = array_splice( $items, 0, $this->batch_size );

			wp_schedule_single_event( time() + ( $delay * $i ), 'automatewoo/background_process', [
				'hook' => $this->hook,
				'items' => $batch,
				'args' => $this->args
			]);

			$i++;
		}
	}
}