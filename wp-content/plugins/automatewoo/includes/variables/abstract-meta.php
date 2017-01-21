<?php
/**
 * @class 		AW_Variable_Abstract_Meta
 * @package		AutomateWoo/Variables
 */

abstract class AW_Variable_Abstract_Meta extends AW_Variable
{
	function init()
	{
		$this->add_parameter_text_field( 'key', __( "The meta_key of the field you would like to display.", 'automatewoo'), true );
	}
}
