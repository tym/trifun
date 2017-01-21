<?php
/**
 * @class 		AW_Dashboard_Widget_Abstract
 * @package		AutomateWoo / Admin / Dashboard Widgets
 * @since		2.8
 */

class AW_Dashboard_Widget_Abstract {

	/** @var string */
	public $id;

	/** @var string optional */
	public $title = '';

	/** @var DateTime */
	public $date_from;

	/** @var DateTime */
	public $date_to;

	/** @var bool */
	public $display = true;


	/**
	 * @return string
	 */
	function get_id() {
		return $this->id;
	}


	/**
	 * Set GMT date range
	 *
	 * @param $from DateTime
	 * @param $to DateTime
	 */
	function set_date_range( $from, $to ) {
		$this->date_from = $from;
		$this->date_to = $to;
	}


	/**
	 * Display the widget
	 */
	function output() {
		$this->output_before();
		$this->output_content();
		$this->output_after();
	}


	function output_content() {

	}


	function output_before() {
		$classes = 'automatewoo-dashboard-widget automatewoo-dashboard-widget--' . $this->id;
		echo '<div class="' . esc_attr( $classes ) . '">';
		echo '<div class="automatewoo-dashboard-widget__content">';
	}


	function output_after() {
		echo '</div></div>';
	}


}