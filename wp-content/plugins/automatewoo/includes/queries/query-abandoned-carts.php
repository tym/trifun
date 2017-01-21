<?php
/**
 * @class       AW_Query_Abandoned_Carts
 * @since       2.0.0
 * @package     AutomateWoo/Queries
 */

class AW_Query_Abandoned_Carts extends AW_Query_Custom_Table {

	protected $model = 'AW_Model_Abandoned_Cart';

	public $table_columns = [ 'id', 'user_id', 'last_modified', 'created', 'items', 'total', 'token' ];


	function __construct() {
		$this->table_name = AW()->table_name_abandoned_cart;
	}


	/**
	 * @return AW_Model_Abandoned_Cart[]|false
	 */
	function get_results() {
		return parent::get_results();
	}

}