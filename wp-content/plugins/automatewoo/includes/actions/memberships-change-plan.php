<?php
/**
 * @class 	AW_Action_Memberships_Change_Plan
 * @since	2.8
 */

class AW_Action_Memberships_Change_Plan extends AW_Action_Memberships_Abstract {

	public $name = 'memberships_change_plan';

	public $required_data_items = [ 'user' ];


	function init() {
		$this->title = __( 'Change Membership Plan', 'automatewoo' );
		$this->description = __( 'This action can be used to change the plan of a single active membership belonging to a user. It will not create new memberships.', 'automatewoo' );
		parent::init();
	}


	function load_fields() {

		$from_plan = new AW_Field_Select();
		$from_plan->set_name( 'from_plan' );
		$from_plan->set_title( __( 'From Plan (optional)', 'automatewoo' ) );
		$from_plan->set_options( $this->get_membership_plans_select_options() );

		$to_plan = new AW_Field_Select( false );
		$to_plan->set_name( 'to_plan' );
		$to_plan->set_title( __( 'To Plan', 'automatewoo' ) );
		$to_plan->set_options( $this->get_membership_plans_select_options() );
		$to_plan->set_required();

		$this->add_field( $from_plan );
		$this->add_field( $to_plan );

	}


	function run() {

		/** @var $user AW_Model_Order_Guest|WP_User */
		$user = $this->workflow->get_data_item( 'user' );
		$from_plan_id = absint( $this->get_option( 'from_plan' ) );
		$to_plan_id = absint( $this->get_option( 'to_plan' ) );

		if ( ! $user instanceof WP_User || ! $to_plan_id )
			return;

		$current_memberships = wc_memberships_get_user_active_memberships( $user->ID );

		if ( empty ( $current_memberships ) )
			return; // user has no memberships we can change

		$membership_to_change = false;

		if ( $from_plan_id ) {
			foreach ( $current_memberships as $membership ) {

				if ( $membership->get_plan_id() == $from_plan_id ) {
					$membership_to_change = $membership;
					break;
				}
			}
		}
		else {
			// change the first membership found
			$membership_to_change = current( $current_memberships );
		}

		if ( $membership_to_change ) {

			$to_plan = wc_memberships_get_membership_plan( $to_plan_id );

			if ( ! $to_plan ) {
				return;
			}

			$membership_to_change->add_note(
				sprintf(
					__( 'Membership plan changed from %s to %s by AutomateWoo workflow:#%s', 'automatewoo' ),
					$membership_to_change->get_plan()->get_name(),
					$to_plan->get_name(),
					$this->workflow->id
				)
			);

			wp_update_post([
				'ID' => $membership_to_change->get_id(),
				'post_parent' => $to_plan_id
			]);
		}
	}
}
