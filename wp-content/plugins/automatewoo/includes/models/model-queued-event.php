<?php
/**
 * Used to create new queued events and use existing
 *
 * @class       AW_Model_Queued_Event
 * @package     AutomateWoo/Models
 * @since       2.1.0
 *
 * @property int $workflow_id
 * @property string $date UTC
 * @property string $created UTC
 * @property array $data_items
 * @property bool $failed
 */

class AW_Model_Queued_Event extends AW_Model {

	/** @var string */
	public $model_id = 'queued-event';

	/** @var bool|array */
	private $uncompressed_data_layer;

	/** @var AW_Model_Workflow */
	private $workflow;


	/**
	 * @param bool|int $id
	 */
	function __construct( $id = false ) {

		$this->table_name = AW()->table_name_queue;

		if ( $id ) $this->get_by( 'id', $id );
	}


	/**
	 * @param $id
	 */
	function set_workflow_id( $id ) {
		$this->workflow_id = $id;
	}


	/**
	 * @param $data_layer
	 */
	function set_data_layer( $data_layer ) {

		$this->uncompressed_data_layer = $data_layer;

		$compressed_data_layer = [];

		foreach ( $this->uncompressed_data_layer as $data_type_id => $item ) {
			if ( $data_type = AW()->get_data_type( $data_type_id ) ) {
				if ( $data_type->validate( $item ) ) {
					$compressed_data_layer[$data_type_id] = $data_type->compress( $item );
				}
			}
		}

		// Save
		$this->data_items = $compressed_data_layer;
	}


	/**
	 * @return array
	 */
	function get_data_layer() {

		// already uncompressed
		if ( is_array( $this->uncompressed_data_layer ) )
			return $this->uncompressed_data_layer;

		$this->uncompressed_data_layer = array();

		if ( ! $this->data_items ) return array();

		$compressed_data_layer = $this->data_items;

		foreach ( $compressed_data_layer as $data_type_id => $compressed_item ) {
			if ( $data_type = AW()->get_data_type( $data_type_id ) ) {
				$this->uncompressed_data_layer[$data_type_id] = $data_type->decompress( $compressed_item, $compressed_data_layer );
			}
		}

		return $this->uncompressed_data_layer;
	}



	/**
	 * Data items should be retrieved through the workflow
	 *
	 * @return AW_Model_Workflow|false
	 */
	function get_workflow() {

		if ( ! isset( $this->workflow ) ) {
			$this->workflow = AW()->get_workflow( $this->workflow_id );

			if ( $this->workflow ) {
				$this->workflow->set_data_items( $this->get_data_layer() );
			}
		}

		return $this->workflow;
	}


	/**
	 * @param $value int
	 * @param $unit string (h,d,w)
	 *
	 * @return DateTime
	 */
	function calculate_delay( $value, $unit ) {

		$date = new DateTime();

		switch( $unit ) {
			case 'm':
				$date->modify("+$value minutes");
				break;

			case 'h':
				$date->modify("+$value hours");
				break;

			case 'd':
				$date->modify("+$value days");
				break;

			case 'w':
				$date->modify("+$value weeks");
				break;
		}

		return $date;
	}


	/**
	 * @param $date DateTime
	 */
	function set_date( $date ) {

		if ( ! $date instanceof DateTime )
			return;

		$this->date = $date->format('Y-m-d H:i:s');
	}



	/**
	 * @return bool
	 */
	function check_data_layer() {

		$data_items = $this->get_data_layer();

		foreach ( $data_items as $data_item ) {
			if ( ! $data_item )
				return false;
		}

		return true;
	}


	/**
	 *
	 */
	function run() {

		if ( ! $this->exists )
			return false;

		$workflow = $this->get_workflow();

		if ( $workflow && $workflow->is_active() && $this->check_data_layer() ) {

			if ( $this->validate_workflow( $workflow ) ) {
				$workflow->run();
			}

			$this->delete();
			return true;
		}
		else {
			$this->failed = true;
			$this->save();
			return false;
		}
	}


	/**
	 * Validate the workflow before running it from the queue.
	 * This validation is different from the initial trigger validation.
	 *
	 * @since 2.7.5
	 * @param $workflow AW_Model_Workflow
	 * @return bool
	 */
	function validate_workflow( $workflow ) {

		if ( ! $trigger = $workflow->get_trigger() )
			return false;

		if ( ! $trigger->validate_before_queued_event( $workflow ) )
			return false;

		if ( ! $workflow->validate_rules() )
			return false;

		return true;
	}


	/**
	 *
	 */
	function clear_cached_data() {

		if ( ! $this->workflow_id )
			return;

		AW()->cache()->delete( 'current_queue_count/workflow=' . $this->workflow_id );
	}


	/**
	 *
	 */
	function save() {

		if ( ! $this->exists ) {
			$this->created = current_time( 'mysql', true );
		}

		$this->clear_cached_data();

		parent::save();
	}


	/**
	 *
	 */
	function delete() {
		$this->clear_cached_data();
		parent::delete();
	}

}

