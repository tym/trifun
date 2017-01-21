<?php
/**
 * @class       AW_Query_Log
 * @package     AutomateWoo/Queries
 */

class AW_Query_Logs extends AW_Query_Custom_Table {

	public $model = 'AW_Model_Log';

	public $table_columns = [ 'id', 'workflow_id', 'date', 'tracking_enabled', 'conversion_tracking_enabled' ];


	function __construct() {
		$this->table_name = AW()->table_name_logs;
		$this->meta_table_name = AW()->table_name_log_meta;
		$this->meta_id_column = 'log_id';
	}


	/**
	 * @return AW_Model_Log[]|false
	 */
	function get_results() {
		return parent::get_results();
	}

}
