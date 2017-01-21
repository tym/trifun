<?php
/**
 * @class 		AW_Rule_Abstract_Object
 * @package		AutomateWoo/Rules
 */

abstract class AW_Rule_Abstract_Object extends AW_Rule_Abstract
{
	/** @var string  */
	public $type = 'object';

	/** @var bool  */
	public $is_multi = false;

	/** @var string */
	public $ajax_action;

	/** @var string  */
	public $class = 'automatewoo-json-search';

	/** @var string */
	public $placeholder;


	abstract function get_object_display_value( $value );


	function __construct()
	{
		$this->placeholder = __( 'Search...', 'automatewoo' );

		parent::__construct();
	}

}