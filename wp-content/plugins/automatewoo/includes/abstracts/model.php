<?php
/**
 * @class       AW_Model
 * @package     AutomateWoo/Abstracts
 *
 * @property $id
 */

abstract class AW_Model {

	/** @var string */
	public $model_id;

	/** @var string */
	public $table_name;

	/** @var string */
	public $meta_table_name;

	/** @var string */
	public $meta_id_column;

	/** @var bool */
	public $exists = false;

	/** @var array */
	public $data = [];

	/** @var array */
	public $changed_fields = [];

	/** @var array */
	public $meta_cache = [];


	/**
	 * @return int
	 */
	function get_id() {
		return $this->id;
	}


	/**
	 * Fill model with data
	 *
	 * @param $row
	 */
	function fill( $row ) {

		// remove meta columns
		unset( $row[ 'meta_key' ] );
		unset( $row[ 'meta_value' ] );
		unset( $row[ 'meta_id' ] );
		unset( $row[ $this->meta_id_column ] );

		$this->data = $row;
		$this->exists = true;
	}


	/**
	 * @param $value string|int
	 * @param $field string
	 *
	 * @return array|null|object|void
	 */
	function get_by( $field, $value ) {

		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare( "
				SELECT * FROM {$this->get_table_name()}
		 		WHERE $field = %s
			", $value
			), ARRAY_A
		);

		if ( ! $row ) return false;

		$this->exists = true;
		$this->data = $row;
	}


	/**
	 * Magic method for accessing db fields
	 *
	 * @param string $key
	 * @return mixed
	 */
	function __get( $key ) {

		if ( ! isset( $this->data[$key] ) )
			return false;

		$value = $this->data[$key];
		$value = maybe_unserialize( $value );

		return $value;
	}


	/**
	 * Magic method for setting db fields
	 *
	 * @param $key
	 * @param $value
	 */
	function __set( $key, $value ) {
		$this->data[$key] = $value;
		$this->changed_fields[] = $key;
	}


	/**
	 * Add wpdb prefix
	 *
	 * @return string
	 */
	function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . $this->table_name;
	}


	/**
	 * Add wpdb prefix
	 *
	 * @return string
	 */
	function get_meta_table_name() {
		if ( ! $this->meta_table_name ) return false;
		global $wpdb;
		return $wpdb->prefix . $this->meta_table_name;
	}


	/**
	 * Inserts or updates the model
	 * Only updates modified fields
	 *
	 * @return void
	 */
	function save() {

		global $wpdb;

		if ( $this->exists ) {
			// update changed fields
			$changed_data = array_intersect_key( $this->data, array_flip($this->changed_fields) );

			// serialize
			$changed_data = array_map( 'maybe_serialize', $changed_data );

			if ( empty( $changed_data ) )
				return;

			$wpdb->update(
				$this->get_table_name(),
				$changed_data,
				[ 'id' => $this->id ],
				null,
				[ '%d' ]
			);

			// reset changed data
			$this->changed_fields = [];
		}
		else {
			$this->data = array_map( 'maybe_serialize', $this->data );

			// insert row
			$wpdb->insert(
				$this->get_table_name(),
				$this->data
			);

			$this->exists = true;
			$this->id = $wpdb->insert_id;
		}
	}


	/**
	 * @return void
	 */
	function delete() {

		global $wpdb;

		if ( ! $this->exists ) return;

		if ( $this->get_meta_table_name() ) {
			$wpdb->query($wpdb->prepare( "
                DELETE FROM {$this->get_meta_table_name()}
		 		WHERE $this->meta_id_column = %d
			", $this->id
			));
		}


		$wpdb->query( $wpdb->prepare( "
                DELETE FROM {$this->get_table_name()}
		 		WHERE id = %d
			", $this->id
		));
	}


	/**
	 * @param $key
	 *
	 * @return mixed
	 */
	function get_meta( $key ) {

		if ( ! $this->get_meta_table_name() )
			return false;

		// check meta cache
		if ( isset( $this->meta_cache[$key] ) )
			return $this->meta_cache[$key];

		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare( "
                SELECT meta_value FROM {$this->get_meta_table_name()}
		 		WHERE {$this->meta_id_column} = %d AND meta_key = %s
			", $this->id, $key
			), ARRAY_A
		);

		$value = $row ? maybe_unserialize( $row[ 'meta_value' ] ) : false;

		$this->meta_cache[$key] = $value;
		return $value;
	}


	/**
	 * Updates meta if it already exists other wise inserts new meta row.
	 *
	 * Note: Objects they must be saved before adding meta
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return bool
	 */
	function update_meta( $key, $value ) {

		if ( ! $this->_can_add_meta() )
			return false;

		global $wpdb;

		$data = $this->_generate_meta_array( $key, $value );
		$data_format = array( '%d', '%s', '%s');

		$existing = $wpdb->get_row( $wpdb->prepare(
			"SELECT meta_id FROM {$this->get_meta_table_name()} WHERE $this->meta_id_column = %d AND meta_key = %s",
			$this->id, $key
		));

		if ( $existing ) {
			$wpdb->update(
				$this->get_meta_table_name(),
				$data,
				array( 'meta_id' => $existing->meta_id ),
				$data_format
			);
		}
		else {
			$wpdb->insert(
				$this->get_meta_table_name(),
				$data,
				$data_format
			);
		}

		// update cache
		$this->meta_cache[$key] = $value;
	}



	/**
	 * Adds meta without checking if it already exists - use with caution
	 *
	 * Note: Objects they must be saved before adding meta
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return bool
	 */
	function add_meta( $key, $value ) {

		if ( ! $this->_can_add_meta() ) return false;
		if ( ! $key ) return false;

		global $wpdb;

		$data = $this->_generate_meta_array( $key, $value );
		$data_format = [ '%d', '%s', '%s' ];

		$wpdb->insert(
			$this->get_meta_table_name(),
			$data,
			$data_format
		);

		// update cache
		$this->meta_cache[$key] = $value;
	}


	/**
	 * @param $key
	 * @param $value
	 *
	 * @return array
	 */
	private function _generate_meta_array( $key, $value ) {
		return [
			$this->meta_id_column => $this->id,
			'meta_key' => $key,
			'meta_value' => maybe_serialize( $value )
		];
	}


	/**
	 * Check if the modal supports meta and is ready to save meta.
	 *
	 * @return bool
	 */
	private function _can_add_meta() {

		if ( ! $this->exists ) {
			_doing_it_wrong( __FUNCTION__, __('Object must be saved beore adding meta.', 'automatewoo'), '2.1.0' );
			return false;
		}

		if ( ! $this->get_meta_table_name() || ! $this->meta_id_column )
			return false;

		return true;
	}
}
