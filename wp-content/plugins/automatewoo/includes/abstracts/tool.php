<?php
/**
 * @class 		AW_Tool
 * @package		AutomateWoo/Abstracts
 * @since		2.4.5
 */
abstract class AW_Tool {

	/** @var string - this must directly correspond to the filename */
	public $id;

	/** @var string */
	public $title;

	/** @var string */
	public $description;

	/** @var string */
	public $additional_description;

	/**
	 * @param $args
	 * @return bool|WP_Error
	 */
	abstract function validate_process( $args );


	/**
	 * @param $args
	 * @return bool|WP_Error
	 */
	abstract function process( $args );


	/**
	 * @return array
	 */
	abstract function get_form_fields();


	/**
	 * @param $args
	 */
	abstract function display_confirmation_screen( $args );


	/**
	 * @param array $args
	 * @return array
	 */
	abstract function sanitize_args( $args );


	/**
	 * Return $args if batch needs more processing. Return false if processing complete.
	 *
	 * @return array|false
	 */
	function background_process_batch( $args, $batch_size ) {
		return false;
	}

}
