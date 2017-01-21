<?php
/**
 * @class 		AW_Variable_Category_Title
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Category_Title extends AW_Variable
{
	protected $name = 'category.title';

	function init()
	{
		$this->description = __( "Displays the title of the category.", 'automatewoo');
	}


	/**
	 * @param $category WP_Term
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $category, $parameters )
	{
		return $category->name;
	}
}

return new AW_Variable_Category_Title();