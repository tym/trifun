<?php
/**
 * @class 		AW_Variable_Guest_Generate_Coupon
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Guest_Generate_Coupon extends AW_Variable_Abstract_Generate_Coupon {

	protected $name = 'guest.generate_coupon';

	/**
	 * @param $guest AW_Model_Guest
	 * @param $parameters
	 * @param $workflow
	 * @return string
	 */
	function get_value( $guest, $parameters, $workflow ) {
		return $this->generate_coupon( $guest->email, $parameters, $workflow );
	}
}

return new AW_Variable_Guest_Generate_Coupon();