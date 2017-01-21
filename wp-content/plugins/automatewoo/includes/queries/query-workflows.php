<?php
/**
 * @class       AW_Query_Workflows
 * @package     AutomateWoo/Queries
 */

class AW_Query_Workflows {

	/** @var object */
	public $trigger;

	/** @var int */
	public $limit = -1;

	/** @var array */
	public $args;


	function __construct() {
		$this->args = [
			'post_type' => 'aw_workflow',
			'post_status' => 'publish',
			'order' => 'ASC',
			'orderby' => 'menu_order',
			'posts_per_page' => $this->limit,
			'meta_query' => []
		];
	}


	/**
	 * @param $trigger
	 */
	function set_trigger($trigger) {
		$this->trigger = $trigger;
	}


	/**
	 * @param $i
	 */
	function set_limit( $i ) {
		$this->limit = $i;
	}


	/**
	 * @return AW_Model_Workflow[]|false
	 */
	function get_results() {

		if ( $this->trigger ) {
			$this->args['meta_query'][] = [
				'key' => 'trigger_name',
				'value' => $this->trigger->name,
			];
		}

		$posts = get_posts( $this->args );

		if ( ! $posts )
			return false;

		$workflows = [];

		foreach ( $posts as $post ) {
			$workflow = new AW_Model_Workflow($post);
			$workflows[] = $workflow;
		}

		return $workflows;
	}

}