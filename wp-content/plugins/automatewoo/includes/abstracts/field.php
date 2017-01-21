<?php
/**
 * $name and $title can be set for a specific trigger or they will fall back to their defaults
 *
 * @class       AW_Field
 * @package     AutomateWoo/Abstracts
 */

abstract class AW_Field {

	/** @var string */
	protected $default_title;

	/** @var string */
	protected $default_name;

	/** @var string */
	protected $title;

	/** @var string */
	protected $name;

	/** @var string */
	protected $type;

	/** @var string */
	protected $description;

	/** @var string trigger or action */
	protected $name_base;

	/** @var bool */
	protected $required = false;

	/** @var string */
	protected $classes = '';

	/** @var array */
	protected $extra_attrs = [];

	/** @var string */
	protected $placeholder = '';


	/**
	 * @return mixed
	 */
	abstract function render( $value );


	/**
	 * @param $name
	 * @return $this
	 */
	function set_name( $name ) {
		$this->name = $name;
		return $this;
	}

	/**
	 * @param $title
	 * @return $this
	 */
	function set_title( $title ) {
		$this->title = $title;
		return $this;
	}


	/**
	 * @return string
	 */
	function get_title() {
		if ( $this->title )
			return $this->title;

		return __( $this->default_title, 'automatewoo');
	}


	/**
	 * @return string
	 */
	function get_name() {
		if ( $this->name )
			return $this->name;

		return $this->default_name;
	}


	/**
	 * @return string
	 */
	function get_type() {
		return $this->type;
	}


	/**
	 * @param $description
	 * @return $this
	 */
	function set_description( $description ) {
		$this->description = $description;
		return $this;
	}


	/**
	 * @return string
	 */
	function get_description() {
		return $this->description;
	}


	/**
	 * @param $placeholder string
	 *
	 * @return $this
	 */
	function set_placeholder( $placeholder ) {
		$this->placeholder = $placeholder;
		return $this;
	}


	/**
	 * @return string
	 */
	function get_placeholder() {
		return $this->placeholder;
	}


	/**
	 * @param $classes string
	 * @return $this
	 */
	function set_classes( $classes ) {
		$this->classes = $classes;
		return $this;
	}


	/**
	 * @return string
	 */
	function get_classes() {
		return $this->classes;
	}


	/**
	 * @param $name
	 * @param $value
	 * @return $this
	 */
	function add_extra_attr( $name, $value = null ) {
		$this->extra_attrs[$name] = $value;
		return $this;
	}


	/**
	 * @param $name
	 * @return bool
	 */
	function has_data_attr( $name ) {
		return isset( $this->extra_attrs[ 'data-' . $name ] );
	}


	/**
	 * @param $name
	 * @param $value
	 * @return $this
	 */
	function add_data_attr( $name, $value = null ) {
		$this->add_extra_attr( 'data-' . $name, $value );
		return $this;
	}


	/**
	 * @return string in HTML attribute format
	 */
	function get_extra_attrs() {
		$string = '';

		foreach ( $this->extra_attrs as $name => $value ) {
			if ( is_null( $value ) ) {
				$string .= $name . ' ';
			}
			else {
				$string .= $name . '="' . $value . '" ';
			}
		}

		return $string;
	}


	/**
	 * @param bool $required
	 * @return $this
	 */
	function set_required( $required = true ) {
		$this->required = $required;
		return $this;
	}


	/**
	 * @return bool
	 */
	function get_required() {
		return $this->required;
	}


	/**
	 * @return $this
	 */
	function set_disabled() {
		$this->add_extra_attr( 'disabled', 'true' );
		return $this;
	}


	/**
	 * @param $name_base
	 * @return $this
	 */
	function set_name_base( $name_base ) {
		$this->name_base = $name_base;
		return $this;
	}


	/**
	 * @return bool
	 */
	function get_name_base() {
		return $this->name_base;
	}

	/**
	 * @return string
	 */
	function get_full_name() {
		return ( $this->get_name_base() ? $this->get_name_base() . '[' . $this->get_name() . ']' : $this->get_name() );
	}


	/**
	 * @param string $options
	 * @return $this
	 */
	function set_variable_validation( $options = '' ) {
		$this->set_validation( 'variables ' . $options );
		return $this;
	}


	/**
	 * If $options is left blank then the field not support variables
	 *
	 * @param string $options
	 * @return $this
	 */
	function set_validation( $options = '' ) {
		$this->add_data_attr( 'automatewoo-validate', $options );
		return $this;
	}


	/**
	 * @param $value
	 * @return mixed|string|void
	 */
	function esc_value( $value ) {
		return esc_attr( $value );
	}

}
