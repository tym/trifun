<?php
/**
 * @class       AW_Field_User_Tags
 * @package     AutomateWoo/Fields
 * @since       2.0.0
 */

class AW_Field_User_Tags extends AW_Field_Select {

	protected $default_title = 'User Tags';

	protected $default_name = 'user_tags';

	protected $type = 'select';

	public $multiple = true;


	/**
	 * @return void
	 */
	function __construct() {
		$options = array();

		$tags = get_terms([
			'taxonomy' => 'user_tag',
			'hide_empty' => false
		]);

		foreach ( $tags as $tag ) {
			$options[$tag->slug] = $tag->name;
		}

		$this->set_options( $options );
	}


}