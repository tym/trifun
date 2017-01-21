<?php
/**
 * @class AW_Unsubscribe_Manager
 */

class AW_Unsubscribe_Manager {


	/**
	 * Consolidate email based unsubscribes that match a user_id
	 * @param $user_id int
	 */
	function consolidate_user( $user_id ) {

		$user = get_user_by( 'id', $user_id );

		if ( ! $user )
			return;

		$query = new AW_Query_Unsubscribes();
		$query->where( 'email', strtolower( $user->user_email ) );
		$unsubscribes = $query->get_results();

		if ( ! $unsubscribes )
			return;

		foreach( $unsubscribes as $unsubscribe ) {
			/** @var $unsubscribe AW_Model_Unsubscribe */
			$unsubscribe->set_email( '' );
			$unsubscribe->set_user_id( $user_id );
			$unsubscribe->save();
		}
	}

}
