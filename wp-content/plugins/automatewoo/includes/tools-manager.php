<?php
/**
 * @class 		AW_Tools_Manager
 * @since		2.4.5
 */
class AW_Tools_Manager {

	/** @var $tools array */
	public static $tools = [];


	/**
	 * Constructor
	 */
	function __construct() {
		add_action( 'automatewoo/tools/background_process', [ $this, 'handle_background_process' ], 10, 2 );
	}


	/**
	 * @return array
	 */
	static function get_tools() {

		if ( empty( self::$tools ) ) {

			$path = AW()->path( '/includes/tools/' );

			$tool_includes = apply_filters( 'automatewoo/tools', [
				$path . 'reset-workflow-records.php',
				$path . 'manual-orders-trigger.php',
				$path . 'manual-subscriptions-trigger.php',
				$path . 'unsubscribe-importer.php'
			]);

			foreach ( $tool_includes as $tool_include ) {
				$class = include_once $tool_include;
				self::$tools[$class->id] = $class;
			}
		}

		return self::$tools;
	}


	/**
	 * @param $id
	 * @return AW_Tool|false
	 */
	static function get_tool( $id ) {
		$tools = self::get_tools();

		if ( isset( $tools[$id] ) ) {
			return $tools[$id];
		}

		return false;
	}


	/**
	 * @param $tool_id
	 * @param $args
	 */
	function new_background_process( $tool_id, $args ) {
		wp_schedule_single_event( time(), 'automatewoo/tools/background_process', [ $tool_id, $args ] );
	}


	/**
	 * @param $tool_id
	 * @param $args
	 */
	function handle_background_process( $tool_id, $args ) {

		$tool = $this->get_tool( $tool_id );

		$args = $tool->background_process_batch( $args, $this->get_batch_size() );

		if ( $args ) {
			wp_schedule_single_event( time() + $this->get_batch_delay(), 'automatewoo/tools/background_process', [ $tool_id, $args ]);
		}
	}


	/**
	 * @return int
	 */
	function get_batch_size() {
		return apply_filters( 'automatewoo/tools/batch_size', 25 );
	}


	/**
	 * @return int
	 */
	function get_batch_delay() {
		return apply_filters( 'automatewoo/tools/batch_delay', 5 ) * 60; // 5 minute delay
	}

}
