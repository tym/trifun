<?php
/**
 * @class       AW_Query_Abandoned_Carts
 * @since       2.0.0
 * @package     AutomateWoo/Queries
 */

class AW_Query_Guests extends AW_Query_Custom_Table {

	protected $model = 'AW_Model_Guest';

	public $table_columns = [ 'id', 'email', 'language', 'tracking_key', 'created', 'last_active' ];


	function __construct() {
		$this->table_name = AW()->table_name_guests;
	}


	/**
	 * @return AW_Model_Guest[]|false
	 */
	function get_results() {
		return parent::get_results();
	}

}
