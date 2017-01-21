<?php
/**
 * @class       AW_Query_Queue
 * @since       2.1.0
 * @package     AutomateWoo/Queries
 */

class AW_Query_Queue extends AW_Query_Custom_Table {

	protected $model = 'AW_Model_Queued_Event';

	public $table_columns = [ 'id', 'workflow_id', 'date', 'created', 'data_items', 'failed' ];


	function __construct() {
		$this->table_name = AW()->table_name_queue;
	}

	/**
	 * @return AW_Model_Queued_Event[]|false
	 */
	function get_results() {
		return parent::get_results();
	}
}