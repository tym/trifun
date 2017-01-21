<?php
/**
 * @class 		AW_Rule_Abstract_Bool
 * @package		AutomateWoo/Rules
 */

abstract class AW_Rule_Abstract_Bool extends AW_Rule_Abstract
{
	public $type = 'bool';

	public $select_choices;

	function __construct()
	{
		$this->select_choices = [
			'yes' => __( 'Yes', 'automatewoo' ),
			'no' => __( 'No','automatewoo' )
		];

		parent::__construct();
	}

}