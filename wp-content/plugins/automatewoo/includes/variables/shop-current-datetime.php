<?php
/**
 * @class 		AW_Variable_Shop_Current_Datetime
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Shop_Current_Datetime extends AW_Variable_Abstract_Datetime
{
	protected $name = 'shop.current_datetime';

	function init()
	{
		parent::init();

		$this->description = __( "Current datetime as per your website's specified timezone.", 'automatewoo') . ' ' . $this->_desc_format_tip;
	}


	/**
	 * @param $parameters
	 * @return string
	 */
	function get_value( $parameters )
	{
		return $this->format_datetime( current_time('mysql'), $parameters );
	}
}

return new AW_Variable_Shop_Current_Datetime();