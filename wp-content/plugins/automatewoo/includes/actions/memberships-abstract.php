<?php
/**
 * @class 	AW_Action_Memberships_Abstract
 * @since	2.8
 */

abstract class AW_Action_Memberships_Abstract extends AW_Action {


	function init() {
		$this->group = __( 'Memberships', 'automatewoo' );
		parent::init();
	}


	/**
	 * @return array
	 */
	function get_membership_plans_select_options() {
		$options = [];

		foreach( wc_memberships_get_membership_plans() as $plan ) {
			$options[ $plan->get_id() ] = $plan->get_name();
		}

		return $options;
	}

}
