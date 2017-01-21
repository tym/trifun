<?php
/**
 * @class 		AW_Variable_Order_Item_Attribute
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Order_Item_Attribute extends AW_Variable
{
	protected $name = 'order_item.attribute';

	function init()
	{
		$this->description = __( "Can be used to display the attribute term name when a customer orders a variable product.", 'automatewoo');

		$this->add_parameter_text_field( 'slug', __( "The slug of the product attribute.", 'automatewoo'), true );
	}


	/**
	 * @param $order_item
	 * @param $parameters
	 * @return string
	 */
	function get_value( $order_item, $parameters )
	{
		// requires a slug
		if ( empty( $parameters['slug'] ) )
			return false;

		$attribute = 'pa_' . $parameters['slug'];

		if ( empty( $order_item[$attribute] ) )
			return false;

		$term = $order_item[$attribute];

		$term_obj = get_term_by('slug', $term, $attribute );

		if ( ! $term_obj || is_wp_error($term_obj) )
			return false;

		return $term_obj->name;
	}
}

return new AW_Variable_Order_Item_Attribute();
