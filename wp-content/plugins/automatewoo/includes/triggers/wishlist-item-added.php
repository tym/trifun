<?php
/**
 * @class      AW_Trigger_Wishlist_Item_Added
 * @package    AutomateWoo/Triggers
 * @since		2.3
 */

class AW_Trigger_Wishlist_Item_Added extends AW_Trigger
{
	public $name = 'wishlist_item_added';

	public $group = 'Wishlist';

	public $supplied_data_items = array( 'user', 'wishlist', 'product', 'shop' );


	function init()
	{
		$integration = AW()->wishlist()->get_integration();

		// only support yith
		if ( $integration != 'yith' )
			return;

		$this->title = sprintf( __('User Adds Product (%s)', 'automatewoo'), AW()->wishlist()->get_integration_title() );

		parent::init();
	}


	/**
	 * Add options to the trigger
	 */
	function load_fields()
	{
		$this->add_field_user_pause_period();
	}


	/**
	 * When should this trigger run?
	 */
	function register_hooks()
	{
		add_action( 'yith_wcwl_added_to_wishlist', array( $this, 'catch_hooks' ), 20, 3 );
	}


	/**
	 * Route hooks through here
	 * @param int $product_id
	 * @param int $wishlist_id
	 * @param int $user_id
	 */
	function catch_hooks( $product_id, $wishlist_id, $user_id )
	{
		if ( ! $this->has_workflows() )
			return;

		$integration = AW()->wishlist()->get_integration();

		if ( $integration == 'yith' )
		{
			$wishlist = AW()->wishlist()->get_wishlist( $wishlist_id );
			$user = get_user_by( 'id', $user_id );

			$this->maybe_run(array(
				'user' => $user,
				'wishlist' => $wishlist,
				'product' => wc_get_product( $product_id )
			));
		}
		else
		{
			return;
		}
	}


	/**
	 * @param $workflow AW_Model_Workflow
	 *
	 * @return bool
	 */
	function validate_workflow( $workflow )
	{
		$user = $workflow->get_data_item('user');
		$wishlist = $workflow->get_data_item('wishlist');
		$product = $workflow->get_data_item('product');

		if ( ! $user || ! $wishlist || ! $product )
			return false;

		if ( ! $this->validate_field_user_pause_period( $workflow ) )
			return false;

		return true;
	}


	/**
	 * @param AW_Model_Workflow $workflow
	 * @return bool
	 */
	function validate_before_queued_event( $workflow )
	{
		$user = $workflow->get_data_item('user');
		$wishlist = $workflow->get_data_item('wishlist');
		$product = $workflow->get_data_item('product');

		if ( ! $user || ! $wishlist || ! $product )
			return false;

		$integration = AW()->wishlist()->get_integration();

		// YITH only
		if ( $integration == 'yith' )
		{
			// Check the product is still in the wishlist
			global $wpdb;

			$sql = "SELECT COUNT(*) as `cnt` FROM `{$wpdb->yith_wcwl_items}` WHERE `prod_id` = %d AND `user_id` = %d AND `wishlist_id` = %d";
			$results = $wpdb->get_var( $wpdb->prepare( $sql, [ $product->id, $user->ID, $wishlist->id ] ) );
			$exists = (bool) ( $results > 0 );

			if ( ! $exists )
				return false;
		}
		else
		{
			return false;
		}

		return parent::validate_before_queued_event($workflow);
	}

}

