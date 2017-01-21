<?php
/**
 * @class       AW_Field_Countries
 * @package     AutomateWoo/Fields
 */

class AW_Field_Countries extends AW_Field_Select {

	protected $default_title = 'Countries';

	protected $default_name = 'countries';

	protected $type = 'select';

	public $multiple = true;


	/**
	 */
	function __construct() {
		$this->set_options( WC()->countries->get_allowed_countries() );
	}

}