<?php
/**
 * Not to be confused with AW_Model_Order_Guest
 *
 * This object should be used as a data-type 'guest' and can only be queued with certain other data items
 *
 * @class       AW_Model_Guest
 * @package     AutomateWoo/Models
 * @since       2.1.0
 *
 * @property string $id
 * @property string $email
 * @property string $tracking_key
 * @property string $created
 * @property string $last_active
 * @property string $language
 */

class AW_Model_Guest extends AW_Model {

	/** @var string */
	public $model_id = 'guest';

	/** @var AW_Model_Abandoned_Cart|null|false */
	private $cart;

	/**
	 * @param $id
	 */
	function __construct( $id = false ) {

		$this->table_name = AW()->table_name_guests;

		if ( $id ) $this->get_by( 'id', $id );
	}


	/**
	 * @param $email
	 */
	function set_email( $email ) {
		$this->email = strtolower( $email );
	}


	/**
	 * @param $tracking_key
	 */
	function set_tracking_key( $tracking_key ) {
		$this->tracking_key = $tracking_key;
	}


	/**
	 * @param $created
	 */
	function set_created( $created ) {
		$this->created = $created;
	}


	/**
	 * @param $last_active
	 */
	function set_last_active( $last_active ) {
		$this->last_active = $last_active;
	}


	/**
	 * @param $language
	 */
	function set_language( $language ) {
		$this->language = $language;
	}


	/**
	 * Update last active to now
	 */
	function update_last_active() {
		$this->last_active = current_time( 'mysql', true );
		$this->save();
	}


	/**
	 * @return AW_Model_Abandoned_Cart|false
	 */
	function get_cart() {

		if ( $this->cart === null ) {
			$this->cart = new AW_Model_Abandoned_Cart();
			$this->cart->get_by( 'guest_id', $this->id );

			if ( ! $this->cart->exists )
				$this->cart = false;
		}

		return $this->cart;
	}


	/**
	 *
	 */
	function delete_cart() {
		if ( $this->exists && $cart = $this->get_cart() ) {
			$cart->delete();
		}
	}


	function delete() {
		$this->delete_cart();
		parent::delete();
	}

}
