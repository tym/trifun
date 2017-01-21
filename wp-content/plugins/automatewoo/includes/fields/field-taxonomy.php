<?php
/**
 * @class       AW_Field_Taxonomy
 * @package     AutomateWoo/Fields
 */

class AW_Field_Taxonomy extends AW_Field_Select {

	protected $default_title = 'Taxonomy';

	protected $default_name = 'taxonomy';

	protected $type = 'select';


	function __construct() {
		$this->set_placeholder('- Select -');
	}


	/**
	 * @return array
	 */
	function get_options() {

		$taxonomies = get_taxonomies( [], false );

		$exclude = [
			'nav_menu',
			'post_format',
			'link_category',
			'category',
			'post_tag',
			'product_type',
			'product_shipping_class'
		];

		$options = [];

		foreach( $taxonomies as $tax_slug => $taxonomy ) {

			if ( in_array($tax_slug, $exclude) )
				continue;

			$options[$tax_slug] = $taxonomy->labels->name;
		}

		$this->set_options( $options );

		return $this->options;
	}

}
