<?php
/**
 * @class 		AW_Variable_Shop_Tagline
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Shop_Tagline extends AW_Variable
{
	protected $name = 'shop.tagline';

	function init()
	{
		$this->description = __( "Displays your shop's tag line.", 'automatewoo');
	}

	/**
	 * @param $parameters
	 * @return string
	 */
	function get_value( $parameters )
	{
		return get_bloginfo('description');
	}
}

return new AW_Variable_Shop_Tagline();