<?php
/**
 * @class       AW_Model_Order_Note
 * @package     AutomateWoo/Models
 * @since       2.2
 */

class AW_Model_Order_Note
{
	/** @var int */
	public $id;

	/** @var string */
	public $note;

	/** @var int */
	public $order_id;


	/**
	 * @param $id
	 * @param $note
	 * @param $order_id
	 */
	function __construct( $id, $note, $order_id )
	{
		$this->id = $id;
		$this->note = $note;
		$this->order_id = $order_id;
	}
}