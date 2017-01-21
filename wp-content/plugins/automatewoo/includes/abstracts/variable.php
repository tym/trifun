<?php
/**
 * @class       AW_Variable
 * @package     AutomateWoo/Abstracts
 * @since       2.4
 */

abstract class AW_Variable {

	/** @var string - use dot format e.g. order.id */
	protected $name;

	/** @var string */
	protected $description;

	/** @var array */
	protected $parameters = [];

	/** @var string */
	protected $data_type;

	/** @var string */
	protected $data_field;

	/** @var bool  */
	public $use_fallback = true;


	/**
	 * Constructor
	 */
	function __construct() {

		if ( $this->name ) {
			list( $this->data_type, $this->data_field ) = explode( '.', $this->name );
		}

		$this->init();
	}

	/**
	 *
	 */
	abstract function init();


	/**
	 * @return string
	 */
	function get_description() {
		return $this->description;
	}


	/**
	 * @return array
	 */
	function get_parameters() {
		return $this->parameters;
	}


	/**
	 * @return bool
	 */
	function has_parameters() {
		return ! empty( $this->parameters );
	}


	/**
	 * @return string
	 */
	function get_name() {
		return $this->name;
	}


	/**
	 * @return string
	 */
	function get_data_type() {
		return $this->data_type;
	}


	/**
	 * @return string
	 */
	function get_data_field() {
		return $this->data_field;
	}


	/**
	 * @param $name
	 * @param $description
	 * @param bool $required
	 * @param string $placeholder
	 * @param array $extra
	 */
	protected function add_parameter_text_field( $name, $description, $required = false, $placeholder = '', $extra = [] ) {
		$this->parameters[$name] = array_merge([
			'type' => 'text',
			'description' => $description,
			'required' => $required,
			'placeholder' => $placeholder
		], $extra );
	}


	/**
	 * @param $name
	 * @param $description
	 * @param array $options
	 * @param bool $required
	 * @param array $extra
	 */
	protected function add_parameter_select_field( $name, $description, $options = [], $required = false, $extra = [] ) {
		$this->parameters[$name] = array_merge([
			'type' => 'select',
			'description' => $description,
			'options' => $options,
			'required' => $required
		], $extra );
	}

}
