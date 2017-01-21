<?php
/**
 * @class 		AW_Variable_Shop_Title
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Shop_Title extends AW_Variable
{
	protected $name = 'shop.title';

	/**
	 * Init
	 */
	function init()
	{
		$this->description = __( "Displays your shop's title.", 'automatewoo');
	}

	/**
	 * @param $parameters
	 * @return string
	 */
	function get_value( $parameters )
	{
		return get_bloginfo('name');
	}
}

return new AW_Variable_Shop_Title();
