<?php
/**
 * @class 		AW_Admin_Reports_Tab_Abstract
 * @package		AutomateWoo/Admin
 *
 * @todo eventually handle actions like the 'run now' buttons here
 */

abstract class AW_Admin_Reports_Tab_Abstract
{
	/** @var string */
	public $id;

	/** @var string */
	public $name;


	/**
	 * @return object
	 */
	abstract function get_report_class();


	/**
	 * @return string
	 */
	function get_url()
	{
		return admin_url( 'admin.php?page=automatewoo-reports&tab=' . $this->id );
	}


	/**
	 * @return string|false
	 */
	function output_before_report()
	{
		return false;
	}


	/**
	 * @param $action
	 */
	function handle_actions( $action ) {}



	/**
	 *
	 */
	function output()
	{
		if ( ! $class = $this->get_report_class() )
			return false;

		$class->nonce_action = $this->id . '-action';

		$class->output_report();
	}


}