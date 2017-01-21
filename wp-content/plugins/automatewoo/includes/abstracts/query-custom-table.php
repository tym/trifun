<?php
/**
 * @class       AW_Custom_Table_Query
 * @package     AutomateWoo/Abstracts
 * @since       2.0.0
 */

abstract class AW_Query_Custom_Table {

	/** @var string (required) */
	public $table_name;

	/** @var array (required) */
	public $table_columns = [];

	/** @var string (required) */
	protected $model;

	/** @var string */
	public $meta_table_name;

	/** @var string */
	public $meta_id_column;

	/** @var array */
	public $where = [];

	/** @var array */
	public $where_meta = [];

	/** @var int */
	public $found_rows = 0;

	/** @var int */
	protected $limit;

	/** @var int */
	protected $offset;

	/** @var string */
	protected $orderby;

	/** @var string */
	protected $order;


	/**
	 * Possible compare values: =, <, > IN, NOT IN
	 *
	 * @param $column string
	 * @param $value mixed
	 * @param $compare string
	 * @return $this
	 */
	function where( $column, $value, $compare = '=' ) {

		// if $column is not a column, do a meta query instead
		if ( ! in_array( $column, $this->table_columns ) ) {
			return $this->where_meta( $column, $value, $compare );
		}

		// Accept DateTime, convert to string
		if ( $value instanceof DateTime ) {
			$value = $value->format( 'Y-m-d H:i:s' );
		}

		$this->where[] = [
			'column'  => $column,
			'value'   => $value,
			'compare' => $compare
		];

		return $this;
	}


	/**
	 * Does not support EXISTS or NOT EXISTS
	 *
	 * @param $key
	 * @param $value
	 * @param string $compare
	 * @return $this
	 */
	function where_meta( $key, $value, $compare = '=' ) {

		// Accept DateTime, convert to string
		if ( $value instanceof DateTime ) {
			$value = $value->format( 'Y-m-d H:i:s' );
		}

		$this->where_meta[] = [
			'key'  => $key,
			'value'   => $value,
			'compare' => $compare
		];

		return $this;
	}


	/**
	 * @param string $column
	 * @param string $order
	 * @return $this
	 */
	function set_ordering( $column, $order = 'DESC' ) {
		$this->orderby = sanitize_text_field($column);
		$this->order = sanitize_text_field($order);
		return $this;
	}


	/**
	 * @param $i
	 * @return $this
	 */
	function set_limit( $i ) {
		$this->limit = absint($i);
		return $this;
	}


	/**
	 * @param $i
	 * @return $this
	 */
	function set_offset( $i ) {
		$this->offset = absint($i);
		return $this;
	}


	/**
	 * @return string
	 */
	function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . $this->table_name;
	}


	/**
	 * @return string
	 */
	function get_meta_table_name() {
		if ( ! $this->meta_table_name ) return false;
		global $wpdb;
		return $wpdb->prefix . $this->meta_table_name;
	}


	/**
	 * @param $column string
	 * @param $value mixed
	 * @param $compare string
	 *
	 * @return string
	 */
	private function _process_where( $column, $value, $compare ) {
		global $wpdb;

		if ( is_array( $value ) ) {
			if ( $compare === '=' ) $compare = 'IN'; // change if default

			$value = "('" . implode( "','", esc_sql( $value ) ) . "')";

			return "$column $compare $value";
		}
		else {
			return $wpdb->prepare(
				"$column $compare %s",
				$value
			);
		}
	}


	/**
	 * returns false is no results
	 * @return array|false
	 */
	function get_results() {

		global $wpdb;

		$query = [];
		$query_joins = [];
		$query_wheres = [];

		foreach( $this->where as $where ) {
			$query_wheres[] = $this->_process_where( $where['column'], $where['value'], $where['compare'] );
		}


		if ( ! empty( $this->where_meta ) && $this->get_meta_table_name() ) {
			$i = 1;
			foreach( $this->where_meta as $where ) {
				$query_joins[] = "INNER JOIN {$this->get_meta_table_name()} AS mt$i ON ({$this->get_table_name()}.id = mt$i.{$this->meta_id_column})";

				$meta_key = esc_sql( $where['key'] );

				$query_wheres[] = "(mt$i.meta_key = '$meta_key' AND "
				                  . $this->_process_where( "mt$i.meta_value", $where['value'], $where['compare'] )
				                  . ")";
				$i++;
			}
		}


		$query[] = "SELECT SQL_CALC_FOUND_ROWS * FROM {$this->get_table_name()}";
		$query[] = implode( "\n", $query_joins );

		if ( ! empty( $query_wheres ) ) {
			$query[] = "WHERE";
			$query[] = implode("\nAND ", $query_wheres );
		}

		$query[] = "GROUP BY {$this->get_table_name()}.id";

		if ( $this->orderby ) {
			$query[] = "ORDER BY {$this->orderby} {$this->order}";
		}

		if ( $this->limit )
			$query[] = "LIMIT $this->limit ";

		if ( $this->offset )
			$query[] = "OFFSET $this->offset ";

		$query = implode( "\n", $query );

		$results = $wpdb->get_results( $query, ARRAY_A );

		if ( empty( $results ) ) {
			return false;
		}

		$this->found_rows = $wpdb->get_var('SELECT FOUND_ROWS()');

		if ( $this->model ) {
			$modelled_results = [];

			foreach ( $results as $result ) {
				$modelled_result = new $this->model();
				$modelled_result->fill($result);
				$modelled_results[] = $modelled_result;
			}

			return $modelled_results;
		}

		return $results;
	}

}