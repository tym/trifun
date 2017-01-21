<?php
/**
 * @class 		AW_Variable_Category_ID
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Category_ID extends AW_Variable
{
	protected $name = 'category.id';

	function init()
	{
		$this->description = __( "Displays the ID of the category.", 'automatewoo');
	}

	/**
	 * @param $category WP_Term
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $category, $parameters )
	{
		return $category->term_id;
	}
}

return new AW_Variable_Category_ID();