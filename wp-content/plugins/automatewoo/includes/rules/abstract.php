<?php
/**
 * @class 		AW_Rule_Abstract
 * @package		AutomateWoo/Rules
 */

abstract class AW_Rule_Abstract
{
	/** @var string */
	public $name;

	/** @var string */
	public $title;

	/** @var string */
	public $group;

	/** @var string string|number|object|select  */
	public $type;

	/** @var array */
	public $data_item;

	/** @var array  */
	public $compare_types = [];

	/** @var AW_Model_Workflow */
	private $workflow;


	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->group = __( 'Other', 'automatewoo' );

		$this->init();
	}


	/**
	 * Set up the condition
	 */
	abstract function init();


	/**
	 * Validates the rule based on options set by a workflow
	 * The $data_item passed will already be validated
	 * @param $data_item
	 * @param $compare
	 * @param $expected_value
	 * @return bool
	 */
	abstract function validate( $data_item, $compare, $expected_value );


	/**
	 * @param $workflow
	 */
	function set_workflow( $workflow )
	{
		$this->workflow = $workflow;
	}


	/**
	 * @return AW_Model_Workflow
	 */
	function get_workflow()
	{
		return $this->workflow;
	}


}