<?php
/**
 *
 * @class       AW_Field_Attribute
 * @package     AutomateWoo/Fields
 */

class AW_Field_Attribute extends AW_Field_Select {

	protected $default_title = 'Attribute';

	protected $default_name = 'attribute';

	protected $type = 'select';


	/**
	 *
	 */
	function __construct() {

		$this->set_placeholder('- Select -');

		$attributes = wc_get_attribute_taxonomies();

		$options = [];

		foreach( $attributes as $attribute ) {
			$options[$attribute->attribute_name] = $attribute->attribute_label;
		}

		$this->set_options( $options );
	}

}