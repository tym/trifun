<?php
/**
 * @class 		AW_Variable_Category_Permalink
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Category_Permalink extends AW_Variable
{
	protected $name = 'category.permalink';

	function init()
	{
		$this->description = __( "Displays a permalink to the category page.", 'automatewoo');
	}


	/**
	 * @param $category WP_Term
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $category, $parameters )
	{
		$link = get_term_link( $category );
		if ( ! $link instanceof WP_Error )
			return $link;
	}
}

return new AW_Variable_Category_Permalink();