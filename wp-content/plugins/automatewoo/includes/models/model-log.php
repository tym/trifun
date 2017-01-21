<?php
/**
 * @class       AW_Model_Log
 * @package     AutomateWoo/Models
 * @since       2.1.0
 *
 * @property int $workflow_id
 * @property string $date UTC
 * @property bool $tracking_enabled
 * @property bool $conversion_tracking_enabled
 */

class AW_Model_Log extends AW_Model {

	/** @var string */
	public $model_id = 'log';

	/** @var AW_Model_Workflow */
	private $workflow;

	/** @var string */
	public $meta_id_column = 'log_id';

	/** @var array */
	private $data_layer;


	/**
	 * @param bool|int $id
	 */
	function __construct( $id = false ) {

		$this->table_name = AW()->table_name_logs;
		$this->meta_table_name = AW()->table_name_log_meta;

		if ( $id ) $this->get_by( 'id', $id );
	}


	/**
	 * @param $workflow
	 */
	function set_workflow( $workflow ) {

		$this->workflow_id = $workflow->id;
		$this->workflow = $workflow;
	}


	/**
	 * @param $url
	 */
	function record_click( $url ) {

		if ( ! $tracking = $this->get_meta('tracking_data') )
			$tracking = [];

		$tracking[] = [
			'type' => 'click',
			'url' => $url,
			'date' => current_time( 'mysql', true )
		];

		$this->update_meta( 'tracking_data', $tracking );

		// clicking requires an open so record one just in case images were blocked
		if ( ! $this->has_open_recorded() ) {
			$this->record_open();
		}
	}


	/**
	 * Only records an open once i.e. unique opens
	 */
	function record_open() {

		if ( ! $tracking = $this->get_meta('tracking_data') )
			$tracking = array();

		foreach( $tracking as $item )
			if ( $item['type'] == 'open')
				return;

		$tracking[] = [
			'type' => 'open',
			'date' => current_time( 'mysql', true )
		];

		$this->update_meta( 'tracking_data', $tracking );
	}


	/**
	 * @return bool
	 */
	function has_open_recorded() {

		$tracking = $this->get_meta('tracking_data');

		if ( is_array( $tracking ) ) foreach( $tracking as $item ) {
			if ( $item['type'] == 'open')
				return true;
		}

		return false;
	}


	/**
	 * @return bool
	 */
	function has_click_recorded() {

		$tracking = $this->get_meta('tracking_data');

		if ( is_array( $tracking ) ) foreach( $tracking as $item ) {
			if ( $item['type'] == 'click')
				return true;
		}

		return false;
	}


	/**
	 * @return string|false
	 */
	function get_date_opened() {

		$tracking = $this->get_meta('tracking_data');

		if ( is_array( $tracking ) ) foreach( $tracking as $item ) {
			if ( $item['type'] == 'open')
				return $item['date'];
		}

		return false;
	}


	/**
	 * @return string|false
	 */
	function get_date_clicked() {

		$tracking = $this->get_meta('tracking_data');

		if ( is_array( $tracking ) ) foreach( $tracking as $item ) {
			if ( $item['type'] == 'click')
				return $item['date'];
		}

		return false;
	}



	/**
	 * @param $note string
	 */
	function add_note( $note ) {

		$notes = $this->get_meta( 'notes' );

		if ( ! is_array( $notes ) )
			$notes = [];

		$notes[] = $note;
		$this->update_meta( 'notes', $notes );
	}



	/**
	 * @return AW_Model_Workflow
	 */
	function get_workflow() {

		if ( $this->workflow )
			return $this->workflow;

		return $this->workflow = new AW_Model_Workflow( $this->workflow_id );
	}


	/**
	 * @return array
	 */
	function get_data_layer() {

		if ( ! isset( $this->data_layer ) ) {
			if ( $compressed = $this->get_compressed_data_layer() ) {
				$this->data_layer = $this->decompress_data_layer( $compressed );
			}
			else {
				$this->data_layer = [];
			}
		}

		return $this->data_layer;
	}


	/**
	 * Fetches the data layer from log meta, but does not decompress
	 * Uses the the supplied_data_items field on the workflows trigger
	 *
	 * @return array|false
	 */
	private function get_compressed_data_layer() {

		if ( ! $workflow = $this->get_workflow() )
			return false; // workflow must be set

		if ( ! $this->exists )
			return false; // log must be saved

		if ( ! $trigger = $workflow->get_trigger() )
			return false; // need a trigger

		$data_layer = [];

		foreach ( $trigger->supplied_data_items as $data_type_id ) {

			$data_item_value = $this->get_compressed_data_item( $data_type_id, $trigger->supplied_data_items );

			if ( $data_item_value !== false ) {
				$data_layer[ $data_type_id ] = $data_item_value;
			}
		}

		return $data_layer;
	}


	/**
	 * @param $data_type_id
	 * @param array $supplied_data_items
	 * @return mixed
	 */
	private function get_compressed_data_item( $data_type_id, $supplied_data_items ) {

		if ( $data_type_id === 'shop' )
			return false; // shop is not stored

		// user requires special logic when related to an order
		if ( $data_type_id === 'user' && in_array( 'order', $supplied_data_items ) ) {
			return 0; // get user data from the order when decompressing
		}

		$storage_key = $this->get_data_item_storage_key( $data_type_id );

		if ( ! $storage_key )
			return false;

		return $this->get_meta( $storage_key );
	}


	/**
	 * @param array $compressed_data_layer
	 * @return array
	 */
	private function decompress_data_layer( $compressed_data_layer ) {

		$data_layer = [];

		if ( is_array( $compressed_data_layer ) ) foreach ( $compressed_data_layer as $data_type_id => $compressed_item ) {
			if ( $data_type = AW()->get_data_type( $data_type_id ) ) {
				$data_layer[$data_type_id] = $data_type->decompress( $compressed_item, $compressed_data_layer );
			}
		}

		return $data_layer;
	}


	/**
	 * Store the data layer from the workflow in log meta
	 */
	function store_data_layer() {

		if ( ! $workflow = $this->get_workflow() )
			return; // workflow must be set

		if ( ! $this->exists )
			return; // log must be saved

		foreach ( $workflow->get_data_layer() as $data_type_id => $data_item ) {
			$this->store_data_item( $data_type_id, $data_item );
		}
	}


	/**
	 * @param $data_type_id
	 * @param $data_item
	 */
	private function store_data_item( $data_type_id, $data_item ) {

		$data_type = AW()->get_data_type( $data_type_id );

		if ( ! $data_type || ! $data_type->validate( $data_item ) )
			return;

		// special logic for users who are actually guests
		if ( $data_type_id === 'user' && $data_item->ID === 0 ) {
			$storage_key = 'guest_email';
			$storage_value = $data_item->user_email;
		}
		else {
			$storage_key = $this->get_data_item_storage_key( $data_type_id );
			$storage_value = $this->get_data_item_storage_value( $data_type_id, $data_item );
		}

		if ( $storage_key ) {
			$this->update_meta( $storage_key, $storage_value );
		}
	}



	/**
	 * @param $data_type_id string
	 * @return bool|string
	 */
	private function get_data_item_storage_key( $data_type_id ) {

		$storage_keys = apply_filters( 'automatewoo/log/data_layer_storage_keys', [
			'cart' => 'cart_id',
			'category' => 'category_id',
			'comment' => 'comment_id',
			'guest' => 'guest_email',
			'order' => 'order_id',
			'order_item' => 'order_item_id',
			'order_note' => 'order_note_id',
			'product' => 'product_id',
			'subscription' => 'subscription_id',
			'tag' => 'tag_id',
			'user' => 'user_id',
			'wishlist' => 'wishlist_id',
			'workflow' => 'workflow_id',
		]);

		if ( isset( $storage_keys[ $data_type_id ] ) ) {
			return $storage_keys[ $data_type_id ];
		}
		else {
			return '_data_layer_' . $data_type_id;
		}
	}


	/**
	 * @param $data_type_id
	 * @param $data_item : must be validated
	 * @return mixed
	 */
	private function get_data_item_storage_value( $data_type_id, $data_item ) {

		$value = false;

		if ( $data_type = AW()->get_data_type( $data_type_id ) ) {
			$value = $data_type->compress( $data_item );
		}

		return $value;
	}


	/**
	 *
	 */
	function delete() {

		// delete conversion records for the log
		$converted_orders = get_posts([
			'post_type' => 'shop_order',
			'post_status' => 'any',
			'posts_per_page' => -1,
			'fields' => 'ids',
			'meta_query' => [
				[
					'key' => '_aw_conversion_log',
					'value' => $this->id
				]
			]
		]);

		if ( $converted_orders ) foreach ( $converted_orders as $order_id ) {
			delete_post_meta( $order_id, '_aw_conversion' );
			delete_post_meta( $order_id, '_aw_conversion_log' );
		}

		$this->clear_cached_data();
		parent::delete();
	}


	/**
	 *
	 */
	function save() {
		$this->clear_cached_data();
		parent::save();
	}


	/**
	 *
	 */
	function clear_cached_data() {

		if ( ! $this->workflow_id )
			return;

		AW()->cache()->delete( 'times_run/workflow=' . $this->workflow_id );
	}

}

