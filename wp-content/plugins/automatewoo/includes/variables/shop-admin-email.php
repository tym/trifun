<?php
/**
 * @class 		AW_Variable_Shop_Admin_Email
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Shop_Admin_Email extends AW_Variable
{
	protected $name = 'shop.admin_email';

	function init()
	{
		$this->description = __( "Display the site admin email. Note: You can use this variable in the To field when sending emails.", 'automatewoo');
	}


	/**
	 * @param $parameters
	 * @return string
	 */
	function get_value( $parameters )
	{
		return get_bloginfo('admin_email');
	}
}

return new AW_Variable_Shop_Admin_Email();