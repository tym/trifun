<?php
/**
 * @class       AW_Field_Payment_Gateway
 * @package     AutomateWoo/Fields
 */

class AW_Field_Payment_Gateway extends AW_Field_Select {

	protected $default_title = 'Payment Gateway';

	protected $default_name = 'payment_gateway';

	protected $type = 'select';

	public $multiple = true;


	/**
	 */
	function __construct() {

		$this->set_description( __( 'Only trigger when the order is placed with certain payment methods.', 'automatewoo'  ) );

		$gateways = [];

		foreach ( WC()->payment_gateways()->payment_gateways() as $gateway ) {
			if ( $gateway->enabled === 'yes') {
				$gateways[$gateway->id] = $gateway->get_title();
			}
		}

		$this->set_options( $gateways );
	}

}
