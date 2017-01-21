<?php
/**
 * @class 		AW_Variable_Shop_Url
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Shop_Url extends AW_Variable
{
	protected $name = 'shop.url';

	function init()
	{
		$this->description = __( "Displays the URL to the home page of your shop.", 'automatewoo');
	}

	/**
	 * @param $parameters
	 * @return string
	 */
	function get_value( $parameters )
	{
		return home_url();
	}
}

return new AW_Variable_Shop_Url();

