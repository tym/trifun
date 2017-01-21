<?php
/**
 * @class 		AW_Data_Type
 * @package		AutomateWoo/Abstracts
 * @since		2.4.6
 */

abstract class AW_Data_Type {

	/** @var string */
	public $id;


	/**
	 * @param $item
	 * @return bool
	 */
	abstract function validate( $item );


	/**
	 * Only validated $items should be passed to this method
	 *
	 * @param $item
	 * @return mixed
	 */
	abstract function compress( $item );


	/**
	 * @param $compressed_item
	 * @param $compressed_data_layer
	 * @return mixed
	 */
	abstract function decompress( $compressed_item, $compressed_data_layer );

}
