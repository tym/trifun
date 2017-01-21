<?php
/**
 * @class 		AW_Integration
 * @package		AutomateWoo/Abstracts
 * @since		2.3
 */

abstract class AW_Integration {

	/** @var string */
	public $integration_id;

	/** @var WC_Logger */
	private $log;

	/** @var bool */
	public $log_errors = true;


	/**
	 * @param $message
	 */
	protected function log( $message ) {

		if ( ! $this->log_errors )
			return;

		if ( ! $this->log ) {
			$this->log = new WC_Logger();
		}

		$this->log->add( 'automatewoo-integration-' . $this->integration_id, $message );
	}
}
