<?php
/**
 * @class       AW_Field_Number_Input
 * @package     AutomateWoo/Fields
 */

class AW_Field_Number_Input extends AW_Field_Text_Input {

	protected $default_title = 'Number Input';

	protected $default_name = 'number_input';

	protected $type = 'number';


	/**
	 * @param $min string
	 * @return $this
	 */
	function set_min( $min ) {
		$this->add_extra_attr( 'min', $min );
		return $this;
	}


	/**
	 * @param $max string
	 * @return $this
	 */
	function set_max( $max ) {
		$this->add_extra_attr( 'max', $max );
		return $this;
	}

}
