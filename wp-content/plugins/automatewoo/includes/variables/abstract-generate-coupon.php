<?php
/**
 * @class 		AW_Variable_Abstract_Generate_Coupon
 * @package		AutomateWoo/Variables
 */

abstract class AW_Variable_Abstract_Generate_Coupon extends AW_Variable {

	/**
	 * Init
	 */
	function init() {

		$this->description = sprintf(
			__( "Creates a unique coupon that is restricted to the %s's email. To use, you need to first create a template coupon which is "
				."exactly like creating a normal coupon. It might be helpful to prefix it with the word 'template'.", 'automatewoo'),
			$this->get_data_type() );

		$this->add_parameter_text_field( 'template', __( "Name of the coupon that will be cloned.", 'automatewoo'), true );
		$this->add_parameter_text_field( 'expires', __( "Number of days the coupon will be valid for. If left blank then the expiry set for the template coupon will be used.", 'automatewoo' ) );
		$this->add_parameter_text_field( 'prefix', __( "The prefix for the coupon code, defaults to 'aw-'.", 'automatewoo'), false, 'aw-' );
	}


	/**
	 * @param $email string
	 * @param $parameters array
	 * @param $workflow AW_Model_Workflow
	 * @return bool|string
	 */
	function generate_coupon( $email, $parameters, $workflow ) {

		// requires a template
		if ( empty( $parameters['template'] ) )
			return false;

		$coupon = new AW_Coupon();
		$coupon->set_template_coupon_code( $parameters['template'] );


		// override with parameter
		if ( isset( $parameters['prefix'] ) )
			$coupon->set_prefix( $parameters['prefix'] );

		if ( $workflow->test_mode ) {
			$coupon->set_suffix('[test]');
			$coupon->set_description( __( 'AutomateWoo Test Coupon', 'automatewoo' ) );
		}

		$coupon->generate_code();

		// don't generate a new coupon every time we preview
		if ( $workflow->preview_mode ) {
			return $coupon->code;
		}

		$coupon->set_email_restriction( $email );

		if ( ! empty( $parameters['expires'] ) ) {
			$coupon->set_expires( $parameters['expires'] );
		}

		if ( $coupon_id = $coupon->generate_coupon() ) {

			add_post_meta( $coupon_id, '_is_aw_coupon', true );

			if ( $workflow->test_mode ) {
				add_post_meta( $coupon_id, '_is_aw_test_coupon', true );
			}

			return $coupon->code;
		}
	}
}
