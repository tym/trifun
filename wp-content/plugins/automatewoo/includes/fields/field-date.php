<?php
/**
 * @class 		AW_Field_Date
 * @since		2.4.5
 */

class AW_Field_Date extends AW_Field_Text_Input {

	function __construct() {
		$this->default_title = __( 'Date', 'automatewoo' );
		$this->default_name = 'date';
		$this->type = 'text';

		$this->set_classes('automatewoo-date-picker date-picker');
	}
}
