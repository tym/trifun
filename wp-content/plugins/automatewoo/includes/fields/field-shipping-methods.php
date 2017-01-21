<?php
/**
 * @class       AW_Field_Shipping_Methods
 * @package     AutomateWoo/Fields
 */

class AW_Field_Shipping_Methods extends AW_Field_Select {

	protected $default_title = 'Shipping Methods';

	protected $default_name = 'shipping_methods';

	protected $type = 'select';

	public $multiple = true;


	/**
	 */
	function __construct() {

		$this->set_description( __( 'Only trigger when the order is placed with certain shipping methods.', 'automatewoo'  ) );
		$options = [];

		foreach ( WC()->shipping()->get_shipping_methods() as $method_id => $method ) {
			// method added in WC 2.6
			$options[$method_id] = method_exists( $method, 'get_method_title' ) ? $method->get_method_title() : $method->get_title();
		}

		$this->set_options( $options );
	}

}